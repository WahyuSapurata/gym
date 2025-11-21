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

        // Total records tanpa filter
        $totalData = Absensi::count();

        $query = Absensi::select(
            'absensis.uuid',
            'absensis.uuid_member',
            'absensis.tanggal_absen',
            'absensis.jam_absen',
            'members.uuid as uuid_user',
            'users.nama as nama'
        )
            ->leftJoin('members', 'members.uuid', '=', 'absensis.uuid_member')
            ->leftJoin('users', 'users.uuid', '=', 'members.uuid_user');

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }

        // Setelah filter
        $totalFiltered = $query->count();

        // Sorting
        $query->latest('absensis.created_at'); // default

        // Pagination
        $query->skip($request->start)->take($request->length);

        // Ambil data
        $data = $query->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
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

    public function getAbsensiApi()
    {
        $absensis = Absensi::with(['member.user'])
            ->groupBy('tanggal_absen')
            ->orderBy('tanggal_absen', 'desc')
            ->get();

        return response()->json($absensis);
    }
}
