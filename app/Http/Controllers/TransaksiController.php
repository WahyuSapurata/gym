<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;
use App\Models\Absensi;
use App\Models\Member;
use App\Models\Paket;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\Geometry\Line;

class TransaksiController extends BaseController
{
    public function index()
    {
        $module = 'Invoice';
        // Ambil semua UUID member yang sudah punya transaksi
        $memberSudahTransaksi = Transaksi::pluck('uuid_member');

        // Ambil member yang belum terdaftar di transaksi, join ke users untuk ambil nama
        $member = Member::join('users', 'members.uuid_user', '=', 'users.uuid')
            ->whereNotIn('members.uuid', $memberSudahTransaksi)
            ->select('members.uuid', 'users.nama', 'users.username', 'members.tipe_member', 'members.status_member')
            ->get();

        $paket = Paket::where('status', 'Aktiv')->get();

        return view('admin.transaksi.index', compact('module', 'member', 'paket'));
    }

    public function get(Request $request)
    {
        $columns = [
            'transaksis.uuid',
            'transaksis.uuid_member',
            'transaksis.uuid_paket',
            'members.uuid as member_uuid',
            'members.expired_at',
            'pakets.uuid as paket_uuid',
            'users.nama as nama_member',
            'pakets.nama_paket',
            'pakets.durasi_hari',
            'transaksis.tipe_member',
            'transaksis.no_invoice',
            'transaksis.jenis_pembayaran',
            'transaksis.total_bayar',
            'transaksis.tanggal_mulai',
            'transaksis.tanggal_selesai',
            'transaksis.remaining_session',
            'transaksis.status',
            'transaksis.keterangan',
            'transaksis.bukti',
        ];

        $totalData = Transaksi::where('is_active', true)->count();

        $query = Transaksi::where('is_active', true)->select($columns)
            ->leftJoin('members', 'members.uuid', '=', 'transaksis.uuid_member')
            ->leftJoin('users', 'users.uuid', '=', 'members.uuid_user')
            ->leftJoin('pakets', 'pakets.uuid', '=', 'transaksis.uuid_paket');

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->orWhere('users.nama', 'like', "%{$search}%")
                    ->orWhere('pakets.nama_paket', 'like', "%{$search}%")
                    ->orWhere('transaksis.no_invoice', 'like', "%{$search}%")
                    ->orWhere('transaksis.jenis_pembayaran', 'like', "%{$search}%");
            });
        }

        // ========== FILTER EXPIRED =============
        if ($request->filter_expired !== null && $request->filter_expired !== "") {

            $today = Carbon::today()->format('Y-m-d');

            if ($request->filter_expired == "0") {
                // Benar-benar expired
                $query->whereRaw(
                    "DATEDIFF(STR_TO_DATE(members.expired_at, '%d-%m-%Y'), ?) < 0",
                    [$today]
                );
            } else {
                $days = intval($request->filter_expired);

                // Tepat X hari sebelum expired
                $query->whereRaw(
                    "DATEDIFF(STR_TO_DATE(members.expired_at, '%d-%m-%Y'), ?) = ?",
                    [$today, $days]
                );
            }
        }

        $totalFiltered = $query->count();

        // Sorting
        if ($request->order) {
            $orderCol = $columns[$request->order[0]['column']] ?? 'transaksis.created_at';
            $orderDir = $request->order[0]['dir'] ?? 'desc';
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest('transaksis.created_at');
        }

        // Pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get();

        // Format response DataTables
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function store(StoreTransaksiRequest $store)
    {
        return DB::transaction(function () use ($store) {
            $path = null;

            // Upload bukti pembayaran jika ada
            if ($store->hasFile('bukti')) {
                $fileName = time() . '_' . uniqid() . '.' . $store->bukti->extension();
                $path = $store->bukti->storeAs('bukti', $fileName, 'public');
            }

            // Ambil data member & paket
            $member = Member::where('uuid', $store->uuid_member)->firstOrFail();
            $paket = Paket::where('uuid', $store->uuid_paket)->firstOrFail();

            // Ambil tanggal mulai dari kolom tgl_registrasi di tabel member
            if (empty($member->tgl_registrasi)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tanggal transaksi di member belum diisi.'
                ], 422);
            }

            // Pastikan tgl_registrasi dibaca dengan benar
            $startDate = Carbon::parse($member->tgl_registrasi);

            // Hitung tanggal expired otomatis
            if (!is_null($paket->durasi_hari) && $paket->durasi_hari > 0) {
                $endDate = $startDate->copy()->addDays($paket->durasi_hari);
            } else {
                // Jika paket berbasis sesi → masa aktif default 60 hari
                $endDate = $startDate->copy()->addDays(60);
            }

            // Format hasil ke d-m-Y sebelum simpan
            $startDateFormatted = $startDate->format('d-m-Y');
            $endDateFormatted   = $endDate->format('d-m-Y');

            // Generate nomor invoice otomatis
            $no_invoice = 'INV-' . strtoupper(Str::random(6)) . '-' . date('dmY');

            // Buat transaksi baru
            $transaction = Transaksi::create([
                'uuid_member'       => $member->uuid,
                'uuid_paket'        => $paket->uuid,
                'tipe_member'       => $paket->tipe_member,
                'no_invoice'        => $no_invoice,
                'jenis_pembayaran'  => $store->jenis_pembayaran,
                'total_bayar'       => $paket->harga,
                'tanggal_mulai'     => $startDateFormatted,
                'tanggal_selesai'   => $endDateFormatted,
                'remaining_session' => $paket->total_sesi ?? 0,
                'status'            => 'paid',
                'keterangan'        => $store->keterangan ?? null,
                'bukti'             => $path,
            ]);

            // Update data member
            $member->expired_at = $endDateFormatted;
            $member->tipe_member = $paket->tipe_member;
            $member->status_member = 'active';
            $member->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi berhasil disimpan',
                'data'    => $transaction
            ]);
        });
    }

    public function edit($params)
    {
        $transaksi = Transaksi::with(['member', 'paket'])->where('uuid', $params)->first();

        if (!$transaksi) {
            return response()->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $transaksi
        ]);
    }

    public function update(StoreTransaksiRequest $update, $params)
    {
        return DB::transaction(function () use ($update, $params) {

            $transaksi = Transaksi::where('uuid', $params)->firstOrFail();
            $member = Member::where('uuid', $update->uuid_member)->firstOrFail();
            $paket = Paket::where('uuid', $update->uuid_paket)->firstOrFail();

            $path = $transaksi->bukti; // default: path lama
            if ($update->hasFile('bukti')) {
                // Hapus bukti lama jika ada
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }

                // Upload bukti baru
                $fileName = time() . '_' . uniqid() . '.' . $update->bukti->extension();
                $path = $update->bukti->storeAs('bukti', $fileName, 'public');
            }

            // Ambil tanggal mulai dari member->tgl_registrasi
            if (empty($member->tgl_registrasi)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tanggal transaksi di member belum diisi.'
                ], 422);
            }

            $startDate = Carbon::parse($member->tgl_registrasi);

            // Hitung tanggal expired otomatis
            if (!is_null($paket->durasi_hari) && $paket->durasi_hari > 0) {
                $endDate = $startDate->copy()->addDays($paket->durasi_hari);
            } else {
                // Jika paket berbasis sesi → masa aktif default 60 hari
                $endDate = $startDate->copy()->addDays(60);
            }

            // Format hasil ke d-m-Y sebelum simpan
            $startDateFormatted = $startDate->format('d-m-Y');
            $endDateFormatted   = $endDate->format('d-m-Y');

            // Simpan dengan format d-m-Y ke database
            $transaksi->update([
                'uuid_member'       => $member->uuid,
                'uuid_paket'        => $paket->uuid,
                'tipe_member'       => $paket->tipe_member,
                'jenis_pembayaran'  => $update->jenis_pembayaran,
                'total_bayar'       => $paket->harga,
                'tanggal_mulai'     => $startDateFormatted,
                'tanggal_selesai'   => $endDateFormatted,
                'remaining_session' => $paket->total_sesi ?? 0,
                'status'            => $update->status ?? $transaksi->status,
                'keterangan'        => $update->keterangan ?? $transaksi->keterangan,
                'bukti'             => $path,
            ]);

            // Update data member sesuai paket baru
            $member->expired_at = $endDateFormatted;
            $member->tipe_member = $paket->tipe_member;
            $member->status_member = 'active';

            if (!is_null($paket->total_sesi) && $paket->total_sesi > 0) {
                $member->sisa_sesi = $paket->total_sesi;
            } else {
                $member->sisa_sesi = null;
            }

            $member->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi berhasil diperbarui',
                'data'    => $transaksi
            ]);
        });
    }

    public function delete($params)
    {
        $transaksi = Transaksi::where('uuid', $params)->first();

        // Hapus bukti jika ada
        if ($transaksi->bukti && Storage::disk('public')->exists($transaksi->bukti)) {
            Storage::disk('public')->delete($transaksi->bukti);
        }

        // Hapus data transaksi dan user
        $transaksi->delete();

        return response()->json(['status' => 'success']);
    }

    public function konfirmasi($params)
    {
        DB::beginTransaction();

        try {
            $transaksi = Transaksi::where('uuid', $params)
                ->with(['member', 'paket'])
                ->firstOrFail();

            $member = $transaksi->member;
            $paket  = $transaksi->paket;

            // Update status transaksi
            $transaksi->update([
                'status'     => 'terkonfirmasi',
                'keterangan' => 'Pembayaran telah dikonfirmasi oleh admin.'
            ]);

            if ($member && $paket) {

                // Tentukan prefix tipe member
                $prefix = match (strtolower($paket->tipe_member)) {
                    'gym'        => 'G-',
                    'fungsional' => 'F-',
                    'studio'     => 'S-',
                    default      => 'M-',
                };

                // ===========================
                //  JIKA MEMBER_ID BELUM ADA
                // ===========================
                if (empty($member->member_id)) {

                    // Ambil ID terakhir dengan prefix sama
                    $lastId = Member::whereNotNull('member_id')
                        ->where('member_id', 'like', $prefix . '%')
                        ->orderBy('member_id', 'desc')
                        ->value('member_id');

                    $nextNumber = $lastId
                        ? ((int) preg_replace('/\D/', '', $lastId)) + 1
                        : 1;

                    $newMemberId = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                    $member->member_id = $newMemberId;
                }

                // Jika member_id SUDAH ADA → tetap aktifkan status
                $member->status_member = 'aktif';
                $member->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dikonfirmasi dan status member diperbarui.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancel($params)
    {
        $transaksi = Transaksi::where('uuid', $params)->firstOrFail();
        $transaksi->status = 'cancelled';
        $transaksi->keterangan = 'Transaksi dibatalkan oleh admin karna tidak sesuai ketentuan pembayaran.';
        $transaksi->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil dibatalkan.'
        ]);
    }

    public function edit_pembayaran(Request $request, $params)
    {
        $transaksi = Transaksi::where('uuid', $params)->firstOrFail();
        $transaksi->tanggal_pembayaran = $request->tanggal_pembayaran;
        $transaksi->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi pembayaran berhasil di perbarui.'
        ]);
    }

    public function invoiceView($uuid)
    {
        $transaksi = Transaksi::with(['member.user', 'paket'])->where('uuid', $uuid)->firstOrFail();

        $data = [
            'invoice_no'     => $transaksi->no_invoice,
            'tanggal'        => \Carbon\Carbon::parse($transaksi->created_at)->translatedFormat('d F Y'),
            'tanggal_pembayaran' => \Carbon\Carbon::parse($transaksi->tanggal_pembayaran)->translatedFormat('d F Y'),
            'nama_member'    => $transaksi->member->user->nama,
            'no_member'      => $transaksi->member->member_id,
            'telepon'        => $transaksi->member->nomor_telepon,
            'paket'          => $transaksi->paket->nama_paket,
            'periode_mulai'  => \Carbon\Carbon::parse($transaksi->tanggal_mulai)->translatedFormat('d F Y'),
            'periode_selesai' => \Carbon\Carbon::parse($transaksi->tanggal_selesai)->translatedFormat('d F Y'),
            'total'          => number_format($transaksi->total_bayar, 0, ',', '.'),
            'metode'         => strtoupper($transaksi->jenis_pembayaran),
            'tanggal_bayar'  => \Carbon\Carbon::parse($transaksi->created_at)->translatedFormat('d F Y'),
        ];

        return view('admin.transaksi.invoiceview', $data);
    }

    public function cetak_kartu($uuid)
    {
        $transaksi = Transaksi::with(['member.user', 'paket'])->where('uuid', $uuid)->firstOrFail();
        $member = $transaksi->member;

        if (!$member || !$member->member_id) {
            abort(404, 'Member atau Member ID tidak ditemukan.');
        }

        // Inisialisasi Image Manager
        $manager = new ImageManager(new Driver());

        // Template kartu
        $templatePath = public_path('template-card.png');
        if (!file_exists($templatePath)) {
            abort(404, 'Template kartu tidak ditemukan.');
        }

        $image = $manager->read($templatePath);

        // ✅ Buat QR code sementara
        $qrTempPath = storage_path('app/public/tmp_qr_' . $member->member_id . '.png');
        QrCode::format('png')
            ->size(180)
            ->margin(1)
            ->generate($member->member_id, $qrTempPath);

        $qrImage = $manager->read($qrTempPath);

        // Tempel QR ke template
        $image->place($qrImage, 'bottom-right', 502, 22);

        // ✅ Tambahkan foto member (jika ada)
        if (!empty($member->foto_member)) {
            $fotoPath = storage_path('app/public/' . $member->foto_member);

            if (file_exists($fotoPath)) {
                // Ukuran dan posisi kotak foto di template (kanan atas)
                $targetWidth = 180;
                $targetHeight = 263;
                $posX = 760;  // sesuaikan koordinat X sesuai posisi di template kamu
                $posY = 188;  // sesuaikan koordinat Y sesuai posisi di template kamu

                $foto = $manager->read($fotoPath);

                // Resize proporsional dulu (fit ke kotak)
                $foto->scaleDown($targetWidth, $targetHeight);

                // Crop tengah jika masih lebih besar
                $foto->cover($targetWidth, $targetHeight);

                // Tempel ke posisi
                $image->place($foto, 'top-left', $posX, $posY);
            }
        }

        // Font
        $fontBold = public_path('fonts/Poppins-Bold.ttf');
        $fontRegular = public_path('fonts/Poppins-Regular.ttf');

        // Posisi awal teks
        $startX = 70;
        $startY = 180;
        $lineHeight = 31;

        $labels = [
            'Nama' => $member->user->nama,
            'Member ID' => $member->member_id,
            'Tanggal Lahir' => $member->tanggal_lahir,
            'Alamat' => $member->alamat,
            'Nomor Telepon' => $member->nomor_telepon,
            'Jenis Kelamin' => $member->jenis_kelamin,
            'Masa Aktif' => $transaksi->tanggal_mulai . ' s/d ' . $transaksi->tanggal_selesai,
        ];

        // ✅ Tambahkan sesi HANYA jika ada
        if ($transaksi->remaining_session != 0) {
            $labels['Sisa Sesi'] = $transaksi->remaining_session;
        }

        $maxLabelWidth = 250; // agar titik dua sejajar

        foreach ($labels as $label => $value) {
            // Label
            $image->text($label, $startX, $startY, function ($font) use ($fontBold) {
                $font->filename($fontBold);
                $font->size(20);
                $font->color('#f0f0f0');
            });

            // Titik dua sejajar
            $image->text(':', $startX + $maxLabelWidth, $startY, function ($font) use ($fontBold) {
                $font->filename($fontBold);
                $font->size(20);
                $font->color('#f0f0f0');
            });

            // Nilai
            $image->text($value, $startX + $maxLabelWidth + 20, $startY, function ($font) use ($fontBold) {
                $font->filename($fontBold);
                $font->size(20);
                $font->color('#f0f0f0');
            });

            $startY += $lineHeight;
        }

        // Pastikan folder tujuan ada
        $outputDir = storage_path('app/public/kartu-member/');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Simpan hasil akhir
        $outputPath = $outputDir . $member->member_id . '.jpg';
        $image->save($outputPath, 90, 'jpg');

        // Hapus QR sementara
        @unlink($qrTempPath);

        // Tampilkan ke browser
        return response()->file($outputPath, [
            'Content-Type' => 'image/jpeg',
        ]);
    }

    public function getTanggalExpired($uuid)
    {
        $transaksi = Transaksi::where('uuid', $uuid)->firstOrFail();

        // Ambil tanggal dari transaksi
        $mulai = $transaksi->tanggal_mulai;
        $expired = $transaksi->tanggal_selesai;

        // Ubah ke format Y-m-d agar cocok ke input date HTML
        $expiredFormatted = Carbon::createFromFormat('d-m-Y', $expired)->format('d-m-Y');

        return response()->json([
            'status' => 'success',
            'data' => [
                'mulai' => $mulai,
                'expired_at' => $expiredFormatted
            ]
        ]);
    }

    public function editTanggalExpired(Request $request, $uuid)
    {
        $transaksi = Transaksi::where('uuid', $uuid)->firstOrFail();

        // Parse tanggal expired
        $tanggalMulai = Carbon::createFromFormat('d-m-Y', $request->tanggal_mulai);
        $tanggalExpired = Carbon::createFromFormat('d-m-Y', $request->tanggal_expired);

        // Update tanggal expired di transaksi
        $transaksi->tanggal_mulai = $tanggalMulai->format('d-m-Y');
        $transaksi->tanggal_selesai = $tanggalExpired->format('d-m-Y');
        $transaksi->save();

        // Update juga di member
        $member = $transaksi->member;
        if ($member) {
            $member->expired_at = $tanggalExpired->format('d-m-Y');
            $member->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Tanggal expired berhasil diperbarui.',
            'data' => $transaksi,
        ]);
    }

    public function getDataPerpanjang($params)
    {
        $transaksi = Transaksi::with(['member', 'paket'])->where('uuid', $params)->first();
        return response()->json([
            'status' => 'success',
            'data' => $transaksi
        ]);
    }

    public function perpanjangMember(Request $request, $params)
    {
        $oldTransaksi = Transaksi::where('uuid', $params)->firstOrFail();
        $member = $oldTransaksi->member;

        if ($request->uuid_paket) {
            $paket = Paket::where('uuid', $request->uuid_paket)->first();
        } else {
            $paket = $oldTransaksi->paket;
        }

        if (!$member || !$paket) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member atau Paket tidak ditemukan.'
            ], 404);
        }

        // Nonaktifkan transaksi lama
        $oldTransaksi->is_active = false;
        $oldTransaksi->save();

        // Hitung tanggal baru
        $tanggalMulai = $request->tanggal_mulai ? $request->tanggal_mulai : Carbon::now()->format('d-m-Y');
        $durasi = ($paket->durasi_hari ?? 0) > 0 ? $paket->durasi_hari : 60;
        $tanggalSelesai = $request->tanggal_expired ? $request->tanggal_expired : $tanggalMulai->copy()->addDays($durasi);

        // Buat transaksi baru (aktif)
        $newTransaksi = Transaksi::create([
            'uuid' => (string) Str::uuid(),
            'uuid_member' => $member->uuid,
            'uuid_paket' => $paket->uuid,
            'tipe_member' => $oldTransaksi->tipe_member,
            'no_invoice' => 'INV-' . strtoupper(Str::random(6)) . '-' . date('dmY'),
            'jenis_pembayaran' => $request->jenis_pembayaran ?? 'Tunai',
            'total_bayar' => $paket->harga ?? 0,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'remaining_session' => $paket->jumlah_sesi ?? 0,
            'status' => 'terkonfirmasi',
            'is_active' => true,
            'keterangan' => 'Perpanjangan dari transaksi ' . $oldTransaksi->no_invoice,
        ]);

        // Update masa aktif member
        $member->expired_at = $tanggalSelesai;
        $member->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Masa aktif member berhasil diperpanjang.',
            'data' => [
                'expired_at' => $member->expired_at
            ]
        ]);
    }

    public function getDataByMemberid($params)
    {
        $member = Member::where('member_id', $params)->first();
        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member tidak ditemukan.'
            ], 404);
        }
        $transaksi = Transaksi::where('uuid_member', $member->uuid)
            ->where('is_active', true)
            ->with(['paket'])
            ->first();
        if (!$transaksi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi aktif tidak ditemukan untuk member ini.'
            ], 404);
        }

        $sesi = Absensi::where('uuid_member', $transaksi->uuid_member)->count();

        // ===============================
        // VALIDASI TANGGAL ABSENSI
        // ===============================
        $today = Carbon::now()->format('Y-m-d');

        $tanggalMulai = Carbon::createFromFormat('d-m-Y', $transaksi->tanggal_mulai)
            ->format('Y-m-d');

        $tanggalSelesai = Carbon::createFromFormat('d-m-Y', $transaksi->tanggal_selesai)
            ->format('Y-m-d');

        if ($today < $tanggalMulai) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Absensi belum bisa dilakukan. Belum masuk tanggal mulai.'
            ], 403);
        }

        if ($today > $tanggalSelesai) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Masa aktif member sudah berakhir.'
            ], 403);
        }

        if ($transaksi->tipe_member === "STUDIO") {
            // Sesi habis
            if ($sesi >= $transaksi->remaining_session) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sesi absensi sudah habis.'
                ], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'uuid_member' => $member->uuid,
                'nama_member' => $member->user->nama,
                'tanggal_lahir' => $member->tanggal_lahir,
                'alamat' => $member->alamat,
                'member_id' => $member->member_id,
                'nomor_telepon' => $member->nomor_telepon,
                'jenis_kelamin' => $member->jenis_kelamin,
                'expired_at' => $member->expired_at,
                'foto_member' => $member->foto_member ? $member->foto_member : null,
                'tanggal_mulai' => $transaksi->tanggal_mulai,
                'tanggal_selesai' => $transaksi->tanggal_selesai,
                'sesi' => $transaksi->remaining_session ?? null,
                'sisa_sesi' => $transaksi->remaining_session ? $transaksi->remaining_session - $sesi : null,
            ]
        ]);
    }

    public function getTransaksiByMemberUuid($params)
    {
        $member = Member::where('uuid_user', $params)->first();
        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak.'
            ], 403);
        }
        $transaksi = Transaksi::where('uuid_member', $member->uuid)
            ->with(['paket'])
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => $transaksi
        ]);
    }

    public function getDataByMemberidAbsen($params)
    {
        $member = Member::where('member_id', $params)->first();
        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'Member tidak ditemukan.'
            ], 404);
        }
        $transaksi = Transaksi::where('uuid_member', $member->uuid)
            ->where('is_active', true)
            ->with(['paket'])
            ->first();
        if (!$transaksi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi aktif tidak ditemukan untuk member ini.'
            ], 404);
        }

        $sesi = Absensi::where('uuid_member', $transaksi->uuid_member)->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'uuid_member' => $member->uuid,
                'nama_member' => $member->user->nama,
                'tanggal_lahir' => $member->tanggal_lahir,
                'alamat' => $member->alamat,
                'member_id' => $member->member_id,
                'nomor_telepon' => $member->nomor_telepon,
                'jenis_kelamin' => $member->jenis_kelamin,
                'expired_at' => $member->expired_at,
                'foto_member' => $member->foto_member ? $member->foto_member : null,
                'tanggal_mulai' => $transaksi->tanggal_mulai,
                'tanggal_selesai' => $transaksi->tanggal_selesai,
                'sesi' => $transaksi->remaining_session ?? null,
                'sisa_sesi' => $transaksi->remaining_session ? $transaksi->remaining_session - $sesi : null,
            ]
        ]);
    }
}
