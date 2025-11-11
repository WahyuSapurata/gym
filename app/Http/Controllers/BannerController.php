<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends BaseController
{
    public function index()
    {
        $module = 'Banner';
        return view('admin.banner.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'judul',
            'gambar',
        ];

        $totalData = Banner::count();

        $query = Banner::select(
            'uuid',
            'judul',
            'gambar',
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

    public function store(StoreBannerRequest $request)
    {
        $path = null;
        if ($request->hasFile('gambar')) {
            // Buat nama unik
            $fileName = time() . '_' . uniqid() . '.' . $request->gambar->extension();

            // Simpan di storage/app/public/foto_produk
            $path = $request->gambar->storeAs('gambar', $fileName, 'public');
        }

        Banner::create([
            'judul' => $request->judul,
            'gambar' => $path,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Banner::where('uuid', $params)->first());
    }

    public function update(StoreBannerRequest $update, $params)
    {
        $kategori = Banner::where('uuid', $params)->first();
        // Simpan path lama
        $path = $kategori->gambar;

        // Jika ada upload baru
        if ($update->hasFile('gambar')) {
            // Hapus foto lama jika ada
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            // Simpan foto baru
            $fileName = time() . '_' . uniqid() . '.' . $update->file('gambar')->extension();
            $path = $update->file('gambar')->storeAs('gambar', $fileName, 'public');
        }
        $kategori->update([
            'judul' => $update->judul,
            'gambar' => $path,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        $banner = Banner::where('uuid', $params)->first();

        // Hapus foto_member jika ada
        if ($banner->gambar && Storage::disk('public')->exists($banner->gambar)) {
            Storage::disk('public')->delete($banner->gambar);
        }

        // Hapus data member dan user
        $banner->delete();
        return response()->json(['status' => 'success']);
    }

    public function getData()
    {
        $banners = Banner::all();
        return response()->json($banners);
    }
}
