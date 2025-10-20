<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiRequest;
use App\Http\Requests\UpdateTransaksiRequest;
use App\Models\Member;
use App\Models\Paket;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        $paket = Paket::all();

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

        $totalData = Transaksi::count();

        $query = Transaksi::select($columns)
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
                ->with('member', 'paket') // pastikan relasi ikut di-load
                ->firstOrFail();

            // Update status transaksi
            $transaksi->status = 'terkonfirmasi';
            $transaksi->keterangan = 'Pembayaran telah dikonfirmasi oleh admin.';
            $transaksi->save();

            $member = $transaksi->member;
            $paket = $transaksi->paket;

            if ($member && $paket) {
                // Tentukan prefix dari tipe_member
                $prefix = match (strtolower($paket->tipe_member)) {
                    'gym' => 'G',
                    'fungsional' => 'F',
                    'studio' => 'S',
                    default => 'M', // fallback jika tipe tidak dikenal
                };

                // Jika member belum punya member_id, buatkan otomatis
                if (empty($member->member_id)) {
                    // Ambil member_id terakhir dengan prefix yang sama
                    $lastId = Member::whereNotNull('member_id')
                        ->where('member_id', 'like', $prefix . '%')
                        ->orderBy('member_id', 'desc')
                        ->value('member_id');

                    // Ambil angka terakhir (contoh dari "G0010" jadi 10)
                    $nextNumber = $lastId
                        ? ((int) preg_replace('/\D/', '', $lastId)) + 1
                        : 1;

                    // Format ID baru, contoh: G0001 / F0012 / S0023
                    $newMemberId = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                    // Simpan ke member
                    $member->member_id = $newMemberId;
                    $member->status_member = 'aktif';
                    $member->save();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dikonfirmasi dan member ID telah diperbarui.'
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

    public function invoiceView($uuid)
    {
        $transaksi = Transaksi::with(['member.user', 'paket'])->where('uuid', $uuid)->firstOrFail();

        $data = [
            'invoice_no'     => $transaksi->no_invoice,
            'tanggal'        => \Carbon\Carbon::parse($transaksi->tanggal_mulai)->translatedFormat('d F Y'),
            'nama_member'    => $transaksi->member->user->nama,
            'no_member'      => $transaksi->member->kode_member ?? 'M-' . $transaksi->member->id,
            'telepon'        => $transaksi->member->nomor_telepon,
            'paket'          => $transaksi->paket->nama_paket,
            'periode_mulai'  => \Carbon\Carbon::parse($transaksi->tanggal_mulai)->translatedFormat('d F Y'),
            'periode_selesai' => \Carbon\Carbon::parse($transaksi->tanggal_selesai)->translatedFormat('d F Y'),
            'total'          => number_format($transaksi->total_bayar, 0, ',', '.'),
            'metode'         => strtoupper($transaksi->jenis_pembayaran),
            'tanggal_bayar'  => \Carbon\Carbon::parse($transaksi->tanggal_mulai)->translatedFormat('d F Y'),
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
                $foto = $manager->read($fotoPath)
                    ->resize(220, 221); // Sesuaikan ukuran dengan desain kartu

                // Tempel di kiri atas
                $image->place($foto, 'top-right', 41, 180);
            }
        }

        // Font
        $fontBold = public_path('fonts/Poppins-Bold.ttf');
        $fontRegular = public_path('fonts/Poppins-Regular.ttf');

        // Posisi awal teks
        $startX = 70;
        $startY = 193;
        $lineHeight = 33;

        $labels = [
            'Nama' => $member->user->nama,
            'Member ID' => $member->member_id,
            'Tanggal Lahir' => $member->tanggal_lahir,
            'Alamat' => $member->alamat,
            'Nomor Telepon' => $member->nomor_telepon,
            'Jenis Kelamin' => $member->jenis_kelamin,
            'Masa Aktif' => $transaksi->tanggal_mulai . ' s/d ' . $transaksi->tanggal_selesai,
        ];

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
            $image->text($value, $startX + $maxLabelWidth + 20, $startY, function ($font) use ($fontRegular) {
                $font->filename($fontRegular);
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
}
