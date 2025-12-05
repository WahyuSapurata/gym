<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Akad;
use App\Models\Member;
use App\Models\ReferalPoint;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MemberController extends BaseController
{
    public function index()
    {
        $module = 'Data Member';
        return view('admin.member.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'members.uuid',
            'members.uuid_user',
            'members.member_id',
            'members.jenis_kelamin',
            'members.tanggal_lahir',
            'members.alamat',
            'members.expired_at',
            'members.berat_badan',
            'members.tinggi_badan',
            'members.tipe_member',
            'members.status_member',
            'members.tgl_registrasi',
            'members.nomor_telepon',
            'members.foto_member',
            'users.nama',
            'users.username',
            'users.password_hash',
            'point', // dipetakan dari COALESCE(...)
        ];

        $totalData = Member::count();

        $query = Member::select([
            'members.*',
            'users.nama',
            'users.username',
            'users.password_hash',
            DB::raw('COALESCE(referal_points.point, 0) as point'),
            DB::raw('akads.foto as foto_akad')
        ])
            ->join('users', 'users.uuid', '=', 'members.uuid_user')
            ->leftJoin('referal_points', 'referal_points.uuid_member', '=', 'members.uuid') // ← perbaikan penting
            ->leftJoin('akads', 'akads.uuid_member', '=', 'members.uuid'); // ← perbaikan penting

        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];

            $query->where(function ($q) use ($search) {
                $q->orWhere('members.member_id', 'like', "%{$search}%")
                    ->orWhere('members.nomor_telepon', 'like', "%{$search}%")
                    ->orWhere('members.alamat', 'like', "%{$search}%")
                    ->orWhere('users.nama', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%");
            });
        }

        $totalFiltered = $query->count();

        // Sorting
        if ($request->order) {
            $orderColIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'];
            $orderCol = $columns[$orderColIndex] ?? 'members.created_at';
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest('members.created_at');
        }

        // Pagination
        $query->skip($request->start)->take($request->length);

        $data = $query->get()->map(function ($item) {
            $item->umur = !empty($item->tanggal_lahir)
                ? \Carbon\Carbon::parse($item->tanggal_lahir)->age
                : null;
            return $item;
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function store(StoreMemberRequest $store)
    {
        $path = null;
        if ($store->hasFile('foto_member')) {
            // Buat nama unik
            $fileName = time() . '_' . uniqid() . '.' . $store->foto_member->extension();

            // Simpan di storage/app/public/foto_produk
            $path = $store->foto_member->storeAs('foto_member', $fileName, 'public');
        }

        // $prefix = "K-";

        // // Cari PO terakhir di hari ini
        // $lastMember = Member::whereDate('created_at', now()->toDateString())
        //     ->orderBy('created_at', 'desc')
        //     ->first();

        // if ($lastMember) {
        //     // Ambil angka urut terakhir (setelah prefix)
        //     $lastNumber = intval(substr($lastMember->member_id, strrpos($lastMember->member_id, '-') + 1));
        //     $nextNumber = $lastNumber + 1;
        // } else {
        //     $nextNumber = 1;
        // }

        // $member_id = $prefix . "-" . $nextNumber;

        $user = User::create([
            'nama' => $store->nama,
            'username' => $store->username,
            'password' => Hash::make($store->password_hash),
            'password_hash' => $store->password_hash,
            'role' => 'member',
        ]);

        Member::create([
            'uuid_user' => $user->uuid,
            // 'member_id' => $member_id,
            'jenis_kelamin' => $store->jenis_kelamin,
            'tanggal_lahir' => $store->tanggal_lahir,
            'alamat' => $store->alamat,
            'berat_badan' => $store->berat_badan,
            'tinggi_badan' => $store->tinggi_badan,
            'tgl_registrasi' => $store->tgl_registrasi,
            'nomor_telepon' => $store->nomor_telepon,
            'foto_member' => $path,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        $member = Member::where('uuid', $params)->first();
        $user = User::where('uuid', $member->uuid_user)->first();
        if ($user) {
            $member->nama = $user->nama;
            $member->username = $user->username;
            $member->password_hash = $user->password_hash;
        }
        return response()->json($member);
    }

    public function update(StoreMemberRequest $update, $params)
    {
        $member = Member::where('uuid', $params)->first();
        $user = User::where('uuid', $member->uuid_user)->first();

        // Simpan path lama
        $path = $member->foto_member;

        // Jika ada upload baru
        if ($update->hasFile('foto_member')) {
            // Hapus foto lama jika ada
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            // Simpan foto baru
            $fileName = time() . '_' . uniqid() . '.' . $update->file('foto_member')->extension();
            $path = $update->file('foto_member')->storeAs('foto_member', $fileName, 'public');
        }

        $user->update([
            'nama' => $update->nama,
            'username' => $update->username,
            'password' => Hash::make($update->password_hash),
            'password_hash' => $update->password_hash,
        ]);

        $member->update([
            'jenis_kelamin' => $update->jenis_kelamin,
            'tanggal_lahir' => $update->tanggal_lahir,
            'berat_badan' => $update->berat_badan,
            'alamat' => $update->alamat,
            'tinggi_badan' => $update->tinggi_badan,
            'tgl_registrasi' => $update->tgl_registrasi,
            'nomor_telepon' => $update->nomor_telepon,
            'foto_member' => $path,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        $member = Member::where('uuid', $params)->first();
        $user = User::where('uuid', $member->uuid_user)->first();

        // Hapus foto_member jika ada
        if ($member->foto_member && Storage::disk('public')->exists($member->foto_member)) {
            Storage::disk('public')->delete($member->foto_member);
        }

        // Hapus data member dan user
        $member->delete();
        $user->delete();

        return response()->json(['status' => 'success']);
    }

    public function editMemberid($params)
    {
        $member = Member::where('uuid', $params)->first();
        if (!$member) {
            return response()->json(['status' => 'error', 'message' => 'Member not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'uuid_member' => $member->uuid,
                'member_id' => $member->member_id,
            ]
        ]);
    }

    public function updateMemberid(Request $request, $params)
    {
        $member = Member::where('uuid', $params)->first();
        if (!$member) {
            return response()->json(['status' => 'error', 'message' => 'Member not found'], 404);
        }

        // Validasi member_id
        $request->validate([
            'member_id' => 'required|string|max:255|unique:members,member_id,' . $member->id,
        ]);

        $member->update(['member_id' => $request->member_id]);

        return response()->json(['status' => 'success']);
    }

    public function getMemberDetail($uuid)
    {
        $member = Member::where('uuid', $uuid)->first();
        if (!$member) {
            return response()->json(['status' => 'error', 'message' => 'Member not found'], 404);
        }

        $transaksi = Transaksi::where('uuid_member', $member->uuid)
            ->where('is_active', true)
            ->with(['paket'])
            ->first();
        if (!$transaksi) {
            return response()->json(['status' => 'error', 'message' => 'No active transaction found for this member'], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => [
                'uuid_member' => $member->uuid,
                'nama_member' => $member->user->nama,
                'member_id' => $member->member_id,
                'nomor_telepon' => $member->nomor_telepon,
                'jenis_kelamin' => $member->jenis_kelamin,
                'expired_at' => $member->expired_at,
                'foto_member' => $member->foto_member ? $member->foto_member : null,
                'tanggal_mulai' => $transaksi->tanggal_mulai,
                'tanggal_selesai' => $transaksi->tanggal_selesai,
                'paket' => $transaksi->paket ? $transaksi->paket->nama_paket : null,
            ]
        ]);
    }

    public function getReferal($params)
    {
        $referal = ReferalPoint::where('uuid_member', $params)->first();
        $point = $referal ? $referal->point : 0;

        return $this->sendResponse($point, 'Get referral success');
    }

    public function updateReferal(Request $request, $params)
    {
        $validated = $request->validate([
            'point' => 'required|numeric',
        ]);

        $referal = ReferalPoint::updateOrCreate(
            ['uuid_member' => $params], // cari berdasarkan ini
            ['point' => $validated['point']] // update / insert
        );

        return $this->sendResponse($referal, 'Referral point updated');
    }

    public function getAkad($params)
    {
        $akad = Akad::where('uuid_member', $params)->first();
        $point = $akad ? $akad->foto : 0;

        return $this->sendResponse($point, 'Get referral success');
    }

    public function updateAkad(Request $request, $params)
    {
        // Validasi file hanya jika dikirim
        $request->validate([
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:10048',
        ]);

        $akad = Akad::where('uuid_member', $params)->first();

        $path = $akad->foto ?? null; // simpan foto lama jika tidak update

        // Jika ada file baru
        if ($request->hasFile('foto')) {

            // Hapus file lama jika ada
            if ($akad && $akad->foto && Storage::disk('public')->exists($akad->foto)) {
                Storage::disk('public')->delete($akad->foto);
            }

            // Buat nama unik
            $fileName = time() . '_' . uniqid() . '.' . $request->foto->extension();

            // Simpan ke storage/app/public/foto
            $path = $request->foto->storeAs('foto', $fileName, 'public');
        }

        // update or create
        $akad = Akad::updateOrCreate(
            ['uuid_member' => $params],
            ['foto' => $path]
        );

        return $this->sendResponse($akad, 'Akad updated');
    }
}
