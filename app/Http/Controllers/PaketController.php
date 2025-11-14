<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaketRequest;
use App\Http\Requests\UpdatePaketRequest;
use App\Models\Paket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaketController extends BaseController
{
    public function index()
    {
        $module = 'Paket';
        return view('admin.paket.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'pakets.uuid',
            'pakets.tipe_member',
            'pakets.nama_paket',
            'pakets.durasi_hari',
            'pakets.total_sesi',
            'pakets.harga',
            'pakets.deskripsi',
            'pakets.status',
            'pakets.gambar',
        ];

        $totalData = Paket::count();

        $query = Paket::select($columns);
        // Searching
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $totalFiltered = $query->count();

        // Sorting
        if ($request->order) {
            $orderCol = $columns[$request->order[0]['column']];
            $orderDir = $request->order[0]['dir'];
            $query->orderBy($orderCol, $orderDir);
        } else {
            $query->latest();
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

    public function store(StorePaketRequest $store)
    {
        $path = null;
        if ($store->hasFile('gambar')) {
            // Buat nama unik
            $fileName = time() . '_' . uniqid() . '.' . $store->gambar->extension();

            // Simpan di storage/app/public/foto_produk
            $path = $store->gambar->storeAs('paket', $fileName, 'public');
        }

        Paket::create([
            'tipe_member' => $store->tipe_member,
            'nama_paket' => $store->nama_paket,
            'durasi_hari' => $store->durasi_hari,
            'total_sesi' => $store->total_sesi,
            'harga' => preg_replace('/\D/', '', $store->harga),
            'deskripsi' => $store->deskripsi,
            'status' => $store->status,
            'gambar' => $path,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        $paket = Paket::where('uuid', $params)->first();
        return response()->json($paket);
    }

    public function update(StorePaketRequest $update, $params)
    {
        $member = Paket::where('uuid', $params)->first();
        // Simpan path lama
        $path = $member->gambar;

        // Jika ada upload baru
        if ($update->hasFile('gambar')) {
            // Hapus foto lama jika ada
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            // Simpan foto baru
            $fileName = time() . '_' . uniqid() . '.' . $update->file('gambar')->extension();
            $path = $update->file('gambar')->storeAs('paket', $fileName, 'public');
        }

        $member->update([
            'tipe_member' => $update->tipe_member,
            'nama_paket' => $update->nama_paket,
            'durasi_hari' => $update->durasi_hari,
            'total_sesi' => $update->total_sesi,
            'harga' => preg_replace('/\D/', '', $update->harga),
            'deskripsi' => $update->deskripsi,
            'status' => $update->status,
            'gambar' => $path,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        $paket = Paket::where('uuid', $params)->first();

        // Hapus gambar jika ada
        if ($paket->gambar && Storage::disk('public')->exists($paket->gambar)) {
            Storage::disk('public')->delete($paket->gambar);
        }

        // Hapus data paket dan user
        $paket->delete();

        return response()->json(['status' => 'success']);
    }

    public function getDataPaket()
    {
        $pakets = Paket::all();
        return response()->json($pakets);
    }
}
