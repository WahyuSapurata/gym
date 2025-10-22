<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOperasionalRequest;
use App\Http\Requests\UpdateOperasionalRequest;
use App\Models\Operasional;
use Illuminate\Http\Request;

class OperasionalController extends BaseController
{
    public function index()
    {
        $module = 'Operasional';
        return view('admin.operasional.index', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'uuid',
            'deskripsi',
            'biaya_operasional',
        ];

        $totalData = Operasional::count();

        $query = Operasional::select(
            'uuid',
            'deskripsi',
            'biaya_operasional',
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

    public function store(StoreOperasionalRequest $request)
    {
        Operasional::create([
            'deskripsi' => $request->deskripsi,
            'biaya_operasional' => preg_replace('/\D/', '', $request->biaya_operasional),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function edit($params)
    {
        return response()->json(Operasional::where('uuid', $params)->first());
    }

    public function update(StoreOperasionalRequest $update, $params)
    {
        $kategori = Operasional::where('uuid', $params)->first();
        $kategori->update([
            'deskripsi' => $update->deskripsi,
            'biaya_operasional' => preg_replace('/\D/', '', $update->biaya_operasional),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        Operasional::where('uuid', $params)->delete();
        return response()->json(['status' => 'success']);
    }
}
