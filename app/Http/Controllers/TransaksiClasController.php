<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiClasRequest;
use App\Http\Requests\UpdateTransaksiClasRequest;
use App\Models\TransaksiClas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TransaksiClasController extends BaseController
{
    public function store(StoreTransaksiClasRequest $request, $params)
    {
        $path = null;
        if ($request->hasFile('bukti_pembayaran')) {
            // Buat nama unik
            $fileName = time() . '_' . uniqid() . '.' . $request->bukti_pembayaran->extension();

            // Simpan di storage/app/public/bukti_pembayaran
            $path = $request->bukti_pembayaran->storeAs('bukti_pembayaran', $fileName, 'public');
        }
        TransaksiClas::create([
            'uuid_clas' => $params,
            'nama' => $request->nama,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
            'nomor_telepon' => $request->nomor_telepon,
            'tanggal_lahir' => $request->tanggal_lahir,
            'total_bayar' => preg_replace('/\D/', '', $request->total_bayar),
            'bukti_pembayaran' => $path,
            'status' => 'paid',
        ]);

        return response()->json(['status' => 'success']);
    }

    public function konfirmasi($params)
    {
        $transaksi = TransaksiClas::where('uuid', $params)->first();
        $transaksi->update([
            'status' => 'success',
        ]);
        return response()->json(['status' => 'success']);
    }

    public function cancel($params)
    {
        $transaksi = TransaksiClas::where('uuid', $params)->first();
        $transaksi->update([
            'status' => 'cancel',
        ]);
        return response()->json(['status' => 'success']);
    }

    public function delete($params)
    {
        $transaksi = TransaksiClas::where('uuid', $params)->first();
        if ($transaksi) {
            // Hapus file bukti pembayaran jika ada
            if ($transaksi->bukti_pembayaran) {
                Storage::disk('public')->delete($transaksi->bukti_pembayaran);
            }
            $transaksi->delete();
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error', 'message' => 'Transaksi not found'], 404);
    }

    public function index()
    {
        $module = 'Transaksi Class';
        return view('admin.clas.transaksi', compact('module'));
    }

    public function get(Request $request)
    {
        $columns = [
            'transaksi_clas.uuid',
            'transaksi_clas.uuid_clas',
            'transaksi_clas.nama',
            'transaksi_clas.jenis_kelamin',
            'transaksi_clas.alamat',
            'transaksi_clas.nomor_telepon',
            'transaksi_clas.tanggal_lahir',
            'transaksi_clas.total_bayar',
            'transaksi_clas.bukti_pembayaran',
            'transaksi_clas.status',
            'clas.nama_clas as nama_clas',
        ];

        $totalData = TransaksiClas::count();

        $query = TransaksiClas::select(
            'transaksi_clas.uuid',
            'transaksi_clas.uuid_clas',
            'transaksi_clas.nama',
            'transaksi_clas.jenis_kelamin',
            'transaksi_clas.alamat',
            'transaksi_clas.nomor_telepon',
            'transaksi_clas.tanggal_lahir',
            'transaksi_clas.total_bayar',
            'transaksi_clas.bukti_pembayaran',
            'transaksi_clas.status',
            'clas.nama_clas as nama_clas',
        )->leftJoin('clas', 'clas.uuid', '=', 'transaksi_clas.uuid_clas');

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
}
