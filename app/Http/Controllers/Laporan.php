<?php

namespace App\Http\Controllers;

use App\Models\Operasional;
use App\Models\Penjualan;
use App\Models\Transaksi;
use App\Models\TransaksiClas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Laporan extends BaseController
{
    public function index()
    {
        $module = 'Laporan';
        return view('admin.laporan.index', compact('module'));
    }

    public function get(Request $request)
    {
        $data = [];

        // ==========================
        // 1️⃣ Ambil Semua Data
        // ==========================

        // Transaksi Member
        $transaksis = Transaksi::with('paket')->get();
        foreach ($transaksis as $trx) {
            $namaPaket = $trx->paket->nama_paket ?? '-';

            $data[] = [
                'tanggal'    => Carbon::parse($trx->tanggal_mulai)->format('d-m-Y'),
                'deskripsi'  => 'Pembayaran member (Tipe member: ' . ($trx->tipe_member ?? '-') . ') - Paket: ' . $namaPaket,
                'masuk'      => $trx->total_bayar,
                'keluar'     => 0,
            ];
        }

        // Transaksi Kelas
        $transaksiClass = TransaksiClas::with('clas')->get();
        foreach ($transaksiClass as $cls) {
            $namaClass = $cls->clas->nama_clas ?? '-';

            $data[] = [
                'tanggal'    => Carbon::parse($cls->created_at)->format('d-m-Y'),
                'deskripsi'  => 'Pembayaran kelas (' . $namaClass . ')',
                'masuk'      => $cls->total_bayar,
                'keluar'     => 0,
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
        // 2️⃣ Filter Tanggal dari Frontend
        // ==========================
        if ($request->filled(['tanggal_awal', 'tanggal_akhir'])) {

            $awal  = Carbon::createFromFormat('d-m-Y', $request->tanggal_awal);
            $akhir = Carbon::createFromFormat('d-m-Y', $request->tanggal_akhir);

            $data = array_filter($data, function ($item) use ($awal, $akhir) {
                $tanggal = Carbon::createFromFormat('d-m-Y', $item['tanggal']);
                return $tanggal->between($awal, $akhir);
            });
        }


        // ==========================
        // 3️⃣ Sorting default tanggal ASC
        // ==========================
        usort($data, function ($a, $b) {
            return Carbon::createFromFormat('d-m-Y', $a['tanggal'])
                <=> Carbon::createFromFormat('d-m-Y', $b['tanggal']);
        });


        // ==========================
        // 4️⃣ Searching
        // ==========================
        $search = $request->search['value'] ?? '';

        if (!empty($search)) {
            $data = array_filter($data, function ($item) use ($search) {
                return stripos($item['deskripsi'], $search) !== false ||
                    stripos($item['tanggal'], $search) !== false;
            });
        }


        // Total setelah Filter
        $totalFiltered = count($data);

        // ==========================
        // 5️⃣ Sorting dari DataTables
        // ==========================
        if (!empty($request->order)) {

            // DataTables kolom:
            // 0 = nomor (skip)
            // 1 = tanggal
            // 2 = deskripsi
            // 3 = masuk
            // 4 = keluar

            $columns = [null, 'tanggal', 'deskripsi', 'masuk', 'keluar'];

            $colIndex = $request->order[0]['column'];
            $orderCol = $columns[$colIndex] ?? 'tanggal';
            $orderDir = $request->order[0]['dir'];

            usort($data, function ($a, $b) use ($orderCol, $orderDir) {
                if ($orderCol === 'tanggal') {
                    $valueA = Carbon::createFromFormat('d-m-Y', $a['tanggal'])->timestamp;
                    $valueB = Carbon::createFromFormat('d-m-Y', $b['tanggal'])->timestamp;
                } else {
                    $valueA = $a[$orderCol];
                    $valueB = $b[$orderCol];
                }

                return $orderDir === 'asc' ? ($valueA <=> $valueB) : ($valueB <=> $valueA);
            });
        }


        // ==========================
        // 6️⃣ Saldo Akhir (DARI SEMUA DATA)
        // ==========================
        $saldoAkhir = 0;
        foreach ($data as $item) {
            $saldoAkhir += ($item['masuk'] - $item['keluar']);
        }


        // ==========================
        // 7️⃣ Pagination (DataTables)
        // ==========================
        $start  = intval($request->start ?? 0);
        $length = intval($request->length ?? 10);
        $pagedData = array_slice(array_values($data), $start, $length);


        // ==========================
        // 8️⃣ Return ke DataTables
        // ==========================
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalFiltered,
            'recordsFiltered' => $totalFiltered,
            'data' => $pagedData,
            'saldo_akhir' => $saldoAkhir,
        ]);
    }


    public function export_excel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom
        $headers = [
            'A1' => 'Tanggal',
            'B1' => 'Deskripsi',
            'C1' => 'Masuk',
            'D1' => 'Keluar',
        ];
        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col, $text);
        }

        $data = [];

        // Ambil Semua Data
        $transaksis = Transaksi::with('paket')->get();
        foreach ($transaksis as $trx) {
            $namaPaket = $trx->paket->nama_paket ?? '-';
            $data[] = [
                'tanggal'    => Carbon::parse($trx->tanggal_mulai)->format('d-m-Y'),
                'deskripsi'  => 'Pembayaran member (Tipe member: ' . ($trx->tipe_member ?? '-') . ') - Paket: ' . $namaPaket,
                'masuk'      => $trx->total_bayar,
                'keluar'     => 0,
            ];
        }

        $transaksiClass = TransaksiClas::with('clas')->get();
        foreach ($transaksiClass as $cls) {
            $namaClass = $cls->clas->nama_clas ?? '-';
            $data[] = [
                'tanggal'    => Carbon::parse($cls->created_at)->format('d-m-Y'),
                'deskripsi'  => 'Pembayaran kelas (' . $namaClass . ')',
                'masuk'      => $cls->total_bayar,
                'keluar'     => 0,
            ];
        }

        foreach (Operasional::all() as $op) {
            $data[] = [
                'tanggal' => Carbon::parse($op->created_at)->format('d-m-Y'),
                'deskripsi' => 'Operasional: ' . $op->deskripsi,
                'masuk' => 0,
                'keluar' => $op->biaya_operasional,
            ];
        }

        // Filter tanggal
        if ($request->filled(['tanggal_awal', 'tanggal_akhir'])) {
            $awal  = Carbon::createFromFormat('d-m-Y', $request->tanggal_awal);
            $akhir = Carbon::createFromFormat('d-m-Y', $request->tanggal_akhir);

            $data = array_filter($data, function ($item) use ($awal, $akhir) {
                $tanggal = Carbon::createFromFormat('d-m-Y', $item['tanggal']);
                return $tanggal->between($awal, $akhir);
            });
        }

        // Sorting tanggal ASC
        usort($data, function ($a, $b) {
            return Carbon::createFromFormat('d-m-Y', $a['tanggal'])
                <=> Carbon::createFromFormat('d-m-Y', $b['tanggal']);
        });

        // Isi data ke Excel
        $row = 2;
        foreach ($data as $d) {
            $sheet->setCellValue('A' . $row, $d['tanggal']);
            $sheet->setCellValue('B' . $row, $d['deskripsi']);
            $sheet->setCellValue('C' . $row, $d['masuk']);
            $sheet->setCellValue('D' . $row, $d['keluar']);

            // Format rupiah untuk kolom masuk dan keluar
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');

            $row++;
        }

        // Auto width
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Styling header
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9E1F2'],
            ],
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

        // Border semua data
        $sheet->getStyle('A1:D' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        // Total paling bawah
        // ==== Total di paling bawah ====
        $saldoAkhir = 0;
        foreach ($data as $item) {
            $saldoAkhir += ($item['masuk'] - $item['keluar']);
        }

        // Merge kolom A-C untuk label TOTAL
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->setCellValue('A' . $row, 'TOTAL');

        // Set saldo akhir pada kolom D
        $sheet->setCellValue('D' . $row, $saldoAkhir);

        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'FCE4D6'],
            ],
        ]);

        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('"Rp" #,##0');

        // Download
        $fileName = 'Laporan-export.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
