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
}
