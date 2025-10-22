<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdukRequest;
use App\Http\Requests\UpdateProdukRequest;
use App\Models\Produk;
use Illuminate\Http\Request;

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
        ];

        $totalData = Produk::count();

        $query = Produk::select(
            'uuid',
            'nama_produk',
            'deskripsi',
            'harga',
            'stok',
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
        Produk::create([
            'nama_produk' => $request->nama_produk,
            'deskripsi' => $request->deskripsi,
            'harga' => preg_replace('/\D/', '', $request->harga),
            'stok' => $request->stok,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Produk::where('uuid', $params)->first());
    }

    public function update(StoreProdukRequest $update, $params)
    {
        $kategori = Produk::where('uuid', $params)->first();
        $kategori->update([
            'nama_produk' => $update->nama_produk,
            'deskripsi' => $update->deskripsi,
            'harga' => preg_replace('/\D/', '', $update->harga),
            'stok' => $update->stok,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        Produk::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}
