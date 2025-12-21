<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAbsensiRequest;
use App\Http\Requests\UpdateAbsensiRequest;
use App\Models\Absensi;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiController extends BaseController
{
    public function index()
    {
        $module = 'Absensi';
        return view('admin.absensi.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'absensis.uuid',
            'absensis.uuid_member',
            'absensis.tanggal_absen',
            'absensis.jam_absen',
            'users.nama',
            'transaksis.tipe_member',
        ];

        $baseQuery = Absensi::leftJoin('members', 'members.uuid', '=', 'absensis.uuid_member')
            ->leftJoin('users', 'users.uuid', '=', 'members.uuid_user')
            ->leftJoin('transaksis', function ($join) {
                $join->on('transaksis.uuid_member', '=', 'members.uuid')
                    ->where('transaksis.is_active', 1);
            });

        // ========= FILTER TANGGAL ==========
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $baseQuery->whereBetween('absensis.tanggal_absen', [
                $request->tanggal_awal,
                $request->tanggal_akhir
            ]);
        }

        // ========= FILTER JAM RANGE ==========
        if ($request->filled('jam_absen_start') && $request->filled('jam_absen_end')) {
            $baseQuery->whereBetween('absensis.jam_absen', [
                $request->jam_absen_start,
                $request->jam_absen_end
            ]);
        }

        // ========= FILTER TIPE MEMBER ==========
        if ($request->filled('tipe_member')) {
            $baseQuery->where('transaksis.tipe_member', $request->tipe_member);
        }

        // === Hitung member hadir (unique member) ===
        $jumlah_member_hadir = (clone $baseQuery)->distinct('absensis.uuid_member')->count('absensis.uuid_member');

        // Query utama untuk datatable
        $query = (clone $baseQuery)->select(
            'absensis.uuid',
            'absensis.uuid_member',
            'absensis.tanggal_absen',
            'absensis.jam_absen',
            'members.uuid as uuid_user',
            'users.nama as nama',
            'transaksis.tipe_member'
        );

        // ========= SEARCH ==========
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }

        // Hitung total setelah filter
        $totalFiltered = $query->count();

        // SORT & PAGINATION
        $query->orderBy('absensis.created_at', 'desc')
            ->skip($request->start)
            ->take($request->length);

        $data = $query->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => Absensi::count(),
            'recordsFiltered' => $totalFiltered,
            'jumlah_member_hadir' => $jumlah_member_hadir,
            'data' => $data
        ]);
    }

    public function store(Request $request, $uuid_member)
    {
        $member = Member::where('uuid', $uuid_member)->first();
        if (!$member) {
            return response()->json(['message' => 'Member tidak ditemukan.'], 404);
        }
        Absensi::create([
            'uuid_member' => $request->uuid_member,
            'tanggal_absen' => $request->tanggal_absen,
            'jam_absen' => $request->jam_absen,
        ]);
        return response()->json(['message' => 'Absensi berhasil ditambahkan.']);
    }

    public function getAbsensiApi(Request $request)
    {
        $query = Absensi::with(['member.user'])
            ->orderBy('tanggal_absen', 'desc');

        // ========= FILTER TANGGAL (DD-MM-YYYY) ==========
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {

            // Convert request (Y-m-d) → d-m-Y
            $tglAwal = \Carbon\Carbon::parse($request->tanggal_awal)->format('d-m-Y');
            $tglAkhir = \Carbon\Carbon::parse($request->tanggal_akhir)->format('d-m-Y');

            $query->whereRaw(
                "STR_TO_DATE(tanggal_absen, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')",
                [$tglAwal, $tglAkhir]
            );
        }

        // ========= FILTER JAM RANGE ==========
        if ($request->filled('jam_awal') && $request->filled('jam_akhir')) {
            $query->whereBetween('jam_absen', [
                $request->jam_awal,
                $request->jam_akhir
            ]);
        }

        $absensis = $query->get();

        return response()->json([
            'status' => true,
            'total' => $absensis->count(),
            'data' => $absensis
        ]);
    }

    public function absen_harian()
    {
        $module = 'Absensi Harian';
        return view('admin.absensi.absen_harian', compact('module'));
    }

    public function getAbsenHarian(Request $request)
    {
        $tanggal = $request->tanggal;

        // Ambil jumlah absen per jam
        $data = Absensi::select(
            DB::raw("HOUR(jam_absen) as jam"),
            DB::raw("COUNT(*) as total")
        )
            ->where('tanggal_absen', $tanggal)
            ->whereTime('jam_absen', '>=', '06:00')
            ->whereTime('jam_absen', '<', '22:00')
            ->groupBy(DB::raw("HOUR(jam_absen)"))
            ->orderBy('jam')
            ->get();

        // Buat range jam 06 - 22 (biar jam kosong tetap tampil)
        $result = [];

        for ($jam = 6; $jam < 22; $jam++) {
            $found = $data->firstWhere('jam', $jam);

            $result[] = [
                'range_jam' => sprintf('%02d:00 - %02d:00', $jam, $jam + 1),
                'total_absen' => $found ? $found->total : 0
            ];
        }

        return response()->json([
            'status' => 'success',
            'tanggal' => $tanggal,
            'data' => $result
        ]);
    }
}
