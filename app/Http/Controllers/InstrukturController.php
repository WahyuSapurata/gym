<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstrukturRequest;
use App\Http\Requests\UpdateInstrukturRequest;
use App\Models\Instruktur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstrukturController extends BaseController
{
    public function index()
    {
        $module = 'Instruktur';
        return view('admin.instruktur.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'nama',
            'keahlian',
            'pengalaman',
            'foto_instruktur',
        ];

        $totalData = Instruktur::count();

        $query = Instruktur::select(
            'uuid',
            'nama',
            'keahlian',
            'pengalaman',
            'foto_instruktur',
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

        $data = $query->get()->map(function ($item) {
            // Kalau keahlian null → kasih array kosong
            $subs = json_decode($item->keahlian, true) ?? [];
            // Ambil hanya value dari tagify
            $item->keahlian = collect($subs)->pluck('value')->toArray();
            return $item;
        });

        // Format response DataTables
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function store(StoreInstrukturRequest $request)
    {
        $path = null;
        if ($request->hasFile('foto_instruktur')) {
            // Buat nama unik
            $fileName = time() . '_' . uniqid() . '.' . $request->foto_instruktur->extension();

            // Simpan di storage/app/public/foto_produk
            $path = $request->foto_instruktur->storeAs('foto_instruktur', $fileName, 'public');
        }

        Instruktur::create([
            'nama' => $request->nama,
            'keahlian' => $request->keahlian,
            'pengalaman' => $request->pengalaman,
            'foto_instruktur' => $path,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Instruktur::where('uuid', $params)->first());
    }

    public function update(StoreInstrukturRequest $update, $params)
    {
        $clas = Instruktur::where('uuid', $params)->first();
        // Simpan path lama
        $path = $clas->foto_instruktur;

        // Jika ada upload baru
        if ($update->hasFile('foto_instruktur')) {
            // Hapus foto lama jika ada
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            // Simpan foto baru
            $fileName = time() . '_' . uniqid() . '.' . $update->file('foto_instruktur')->extension();
            $path = $update->file('foto_instruktur')->storeAs('foto_instruktur', $fileName, 'public');
        }
        $clas->update([
            'nama' => $update->nama,
            'keahlian' => $update->keahlian,
            'pengalaman' => $update->pengalaman,
            'foto_instruktur' => $path,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        $instruktur = Instruktur::where('uuid', $params)->first();

        // Hapus foto_member jika ada
        if ($instruktur->foto_instruktur && Storage::disk('public')->exists($instruktur->foto_instruktur)) {
            Storage::disk('public')->delete($instruktur->foto_instruktur);
        }

        // Hapus data member dan user
        $instruktur->delete();
        return response()->json(['status' => 'success']);
    }

    public function getData()
    {
        $data = Instruktur::select('uuid', 'nama', 'keahlian', 'pengalaman', 'foto_instruktur')
            ->get()
            ->map(function ($item) {
                // Kalau keahlian null → kasih array kosong
                $subs = json_decode($item->keahlian, true) ?? [];
                // Ambil hanya value dari tagify
                $item->keahlian = collect($subs)->pluck('value')->toArray();
                return $item;
            });

        return response()->json($data);
    }

    public function getDetail($uuid)
    {
        $instruktur = Instruktur::where('uuid', $uuid)->first();
        if (!$instruktur) {
            return response()->json(['message' => 'Instruktur not found'], 404);
        }

        // Kalau keahlian null → kasih array kosong
        $subs = json_decode($instruktur->keahlian, true) ?? [];
        // Ambil hanya value dari tagify
        $instruktur->keahlian = collect($subs)->pluck('value')->toArray();

        return response()->json($instruktur);
    }
}
