<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdukController extends BaseController
{
    public function index()
    {
        $module = 'Produk';
        return view('admin.produk.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'nama_produk',
            'deskripsi',
            'harga',
            'stok',
            'foto_produk',
        ];

        $totalData = Produk::count();

        $query = Produk::select(
            'uuid',
            'nama_produk',
            'deskripsi',
            'harga',
            'stok',
            'foto_produk',
        );

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

    public function store(StoreProdukRequest $request)
    {
        $path = null;
        if ($request->hasFile('foto_produk')) {
            // Buat nama unik
            $fileName = time() . '_' . uniqid() . '.' . $request->foto_produk->extension();

            // Simpan di storage/app/public/foto_produk
            $path = $request->foto_produk->storeAs('foto_produk', $fileName, 'public');
        }
        Produk::create([
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'harga' => preg_replace('/\D/', '', $request->harga),
            'stok' => $request->stok,
            'foto_produk' => $path,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Produk::where('uuid', $params)->first());
    }

    public function update(StoreProdukRequest $update, $params)
    {
        $produk = Produk::where('uuid', $params)->first();
        $path = $produk->foto_produk;

        // Jika ada upload baru
        if ($update->hasFile('foto_produk')) {
            // Hapus foto lama jika ada
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            // Simpan foto baru
            $fileName = time() . '_' . uniqid() . '.' . $update->file('foto_produk')->extension();
            $path = $update->file('foto_produk')->storeAs('foto_produk', $fileName, 'public');
        }
        $produk->update([
            'nama_produk' => $update->nama_produk,
            'deskripsi' => $update->deskripsi,
            'harga' => preg_replace('/\D/', '', $update->harga),
            'stok' => $update->stok,
            'foto_produk' => $path,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        $produk = Produk::where('uuid', $params)->first();

        // Hapus foto_member jika ada
        if ($produk->foto_produk && Storage::disk('public')->exists($produk->foto_produk)) {
            Storage::disk('public')->delete($produk->foto_produk);
        }

        // Hapus data member dan user
        $produk->delete();
        return response()->json(['status' => 'success']);
    }

    public function getData()
    {
        return response()->json(Produk::all());
    }
}
