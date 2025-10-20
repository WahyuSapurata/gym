<?php

namespace App\Http\Controllers;

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
        return view('admin.dashboard.index', compact('module'));
    }
}
