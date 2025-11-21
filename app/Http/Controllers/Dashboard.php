<?php

namespace App\Http\Controllers;

use App\Models\Clas;
use App\Models\Instruktur;
use App\Models\Member;
use App\Models\Paket;
use Illuminate\Http\Request;

class Dashboard extends BaseController
{
    public function index()
    {
        if (auth()->check()) {
            return redirect()->back();
        }
        return redirect()->route('login.login-akun');
    }

    public function dashboard_admin()
    {
        $module = 'Dashboard Admin';
        $member = Member::count();
        $instruktur = Instruktur::count();
        $paket = Paket::count();
        $clas = Clas::count();
        return view('admin.dashboard.index', compact('module', 'member', 'instruktur', 'paket', 'clas'));
    }

    public function chartTipeMember()
    {
        // Hitung jumlah tiap tipe_member
        $data = Member::selectRaw("
            CASE
                WHEN tipe_member IS NULL OR tipe_member = '' THEN 'BELUM TERKONFIRMASI'
                ELSE UPPER(tipe_member)
            END as tipe,
            COUNT(*) as total
        ")
            ->groupBy('tipe')
            ->get();

        // Siapkan label tetap (agar chart rapi)
        $labels = ['GYM', 'FUNGSIONAL', 'STUDIO', 'BELUM TERKONFIRMASI'];

        // Mapping jumlah sesuai urutan label
        $counts = [];
        foreach ($labels as $label) {
            $counts[] = $data->firstWhere('tipe', $label)->total ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $counts,
        ]);
    }
}
