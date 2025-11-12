<?php

namespace App\Http\Controllers;

use App\Models\Operasional;
use App\Models\Penjualan;
use App\Models\Transaksi;
use App\Models\TransaksiClas;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Laporan extends BaseController
{
    public function index()
    {
        $module = 'Laporan';
        return view('admin.laporan.index', compact('module'));
    }

    public function get(Request $request)
    {
        // ==========================
        // 1️⃣ Ambil semua data & gabungkan
        // ==========================
        $data = [];

        // Transaksi Member
        foreach (Transaksi::all() as $trx) {
            $data[] = [
                'tanggal' => Carbon::parse($trx->tanggal_mulai)->format('d-m-Y'),
                'deskripsi' => 'Pembayaran member (' . ($trx->tipe_member ?? '-') . ')',
                'masuk' => $trx->total_bayar,
                'keluar' => 0,
            ];
        }

        // Transaksi Kelas
        foreach (TransaksiClas::all() as $cls) {
            $data[] = [
                'tanggal' => Carbon::parse($cls->created_at)->format('d-m-Y'),
                'deskripsi' => 'Pembayaran kelas (' . $cls->nama . ')',
                'masuk' => $cls->total_bayar,
                'keluar' => 0,
            ];
        }

        // Penjualan Produk
        foreach (Penjualan::all() as $penjualan) {
            $items = json_decode($penjualan->penjualan_items, true);
            $total = 0;

            if (is_array($items)) {
                foreach ($items as $item) {
                    $total += $item['harga'] ?? 0;
                }
            }

            $data[] = [
                'tanggal' => Carbon::parse($penjualan->created_at)->format('d-m-Y'),
                'deskripsi' => 'Penjualan produk',
                'masuk' => $total,
                'keluar' => 0,
            ];
        }

        // Operasional (keluar)
        foreach (Operasional::all() as $op) {
            $data[] = [
                'tanggal' => Carbon::parse($op->created_at)->format('d-m-Y'),
                'deskripsi' => 'Operasional: ' . $op->deskripsi,
                'masuk' => 0,
                'keluar' => $op->biaya_operasional,
            ];
        }

        // ==========================
        // 2️⃣ Urutkan berdasarkan tanggal
        // ==========================
        usort($data, function ($a, $b) {
            $dateA = Carbon::createFromFormat('d-m-Y', $a['tanggal']);
            $dateB = Carbon::createFromFormat('d-m-Y', $b['tanggal']);
            return $dateA->timestamp <=> $dateB->timestamp;
        });

        // ==========================
        // 3️⃣ Searching
        // ==========================
        $search = $request->search['value'] ?? '';
        if (!empty($search)) {
            $data = array_filter($data, function ($item) use ($search) {
                return stripos($item['deskripsi'], $search) !== false ||
                    stripos($item['tanggal'], $search) !== false;
            });
        }

        $totalData = count($data);
        $totalFiltered = count($data);

        // ==========================
        // 4️⃣ Sorting (berdasarkan kolom di DataTables)
        // ==========================
        if (!empty($request->order)) {
            $columns = ['tanggal', 'deskripsi', 'masuk', 'keluar'];
            $orderCol = $columns[$request->order[0]['column']] ?? 'tanggal';
            $orderDir = $request->order[0]['dir'] ?? 'asc';

            usort($data, function ($a, $b) use ($orderCol, $orderDir) {
                if ($orderDir === 'asc') {
                    return $a[$orderCol] <=> $b[$orderCol];
                } else {
                    return $b[$orderCol] <=> $a[$orderCol];
                }
            });
        }

        // ==========================
        // 5️⃣ Pagination
        // ==========================
        $start = intval($request->start ?? 0);
        $length = intval($request->length ?? 10);
        $data = array_slice($data, $start, $length);

        // ==========================
        // 6️⃣ Hitung saldo akhir
        // ==========================
        $saldo = 0;
        foreach ($data as $item) {
            $saldo += ($item['masuk'] - $item['keluar']);
        }

        // ==========================
        // 7️⃣ Return format DataTables
        // ==========================
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => array_values($data),
            'saldo_akhir' => $saldo,
        ]);
    }
}
