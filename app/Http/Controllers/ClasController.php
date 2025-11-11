<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClasRequest;
use App\Http\Requests\UpdateClasRequest;
use App\Models\Clas;
use App\Models\Instruktur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClasController extends BaseController
{
    public function index()
    {
        $module = 'Class';
        $instruktur = Instruktur::all();
        return view('admin.clas.index', compact('module', 'instruktur'));
    }

    public function get(Request $request)
    {
        $columns = [
            'clas.uuid',
            'clas.uuid_instruktur',
            'clas.nama_clas',
            'clas.harga',
            'clas.kategori',
            'clas.jadwal',
            'clas.durasi',
            'clas.slot',
            'clas.banner',
            'instrukturs.nama as nama_instruktur',
        ];

        $totalData = Clas::count();

        $query = Clas::select(
            'clas.uuid',
            'clas.uuid_instruktur',
            'clas.nama_clas',
            'clas.harga',
            'clas.kategori',
            'clas.jadwal',
            'clas.durasi',
            'clas.slot',
            'clas.banner',
            'instrukturs.nama as nama_instruktur',
        )->leftJoin('instrukturs', 'instrukturs.uuid', '=', 'clas.uuid_instruktur');

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

    public function store(StoreClasRequest $request)
    {
        $path = null;
        if ($request->hasFile('banner')) {
            // Buat nama unik
            $fileName = time() . '_' . uniqid() . '.' . $request->banner->extension();

            // Simpan di storage/app/public/foto_produk
            $path = $request->banner->storeAs('banner', $fileName, 'public');
        }

        Clas::create([
            'uuid_instruktur' => $request->uuid_instruktur,
            'nama_clas' => $request->nama_clas,
            'harga' => preg_replace('/\D/', '', $request->harga),
            'kategori' => $request->kategori,
            'jadwal' => $request->jadwal,
            'durasi' => $request->durasi,
            'slot' => $request->slot,
            'banner' => $path,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Clas::where('uuid', $params)->first());
    }

    public function update(StoreClasRequest $update, $params)
    {
        $clas = Clas::where('uuid', $params)->first();
        // Simpan path lama
        $path = $clas->banner;

        // Jika ada upload baru
        if ($update->hasFile('banner')) {
            // Hapus foto lama jika ada
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            // Simpan foto baru
            $fileName = time() . '_' . uniqid() . '.' . $update->file('banner')->extension();
            $path = $update->file('banner')->storeAs('banner', $fileName, 'public');
        }
        $clas->update([
            'uuid_instruktur' => $update->uuid_instruktur,
            'nama_clas' => $update->nama_clas,
            'harga' => preg_replace('/\D/', '', $update->harga),
            'kategori' => $update->kategori,
            'jadwal' => $update->jadwal,
            'durasi' => $update->durasi,
            'slot' => $update->slot,
            'banner' => $path,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        $clas = Clas::where('uuid', $params)->first();

        // Hapus foto_member jika ada
        if ($clas->banner && Storage::disk('public')->exists($clas->banner)) {
            Storage::disk('public')->delete($clas->banner);
        }

        // Hapus data member dan user
        $clas->delete();
        return response()->json(['status' => 'success']);
    }
}
