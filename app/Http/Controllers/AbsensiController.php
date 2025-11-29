<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAbsensiRequest;
use App\Http\Requests\UpdateAbsensiRequest;
use App\Models\Absensi;
use App\Models\Member;
use Illuminate\Http\Request;

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
            'members.uuid',
            'users.nama',
        ];

        $baseQuery = Absensi::leftJoin('members', 'members.uuid', '=', 'absensis.uuid_member')
            ->leftJoin('users', 'users.uuid', '=', 'members.uuid_user');

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

        // === Hitung member hadir (unique member) ===
        $jumlah_member_hadir = (clone $baseQuery)->distinct('absensis.uuid_member')->count('absensis.uuid_member');

        // Query utama untuk datatable
        $query = (clone $baseQuery)->select(
            'absensis.uuid',
            'absensis.uuid_member',
            'absensis.tanggal_absen',
            'absensis.jam_absen',
            'members.uuid as uuid_user',
            'users.nama as nama'
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
}
