<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Laporan extends BaseController
{
    public function index()
    {
        $module = 'Laporan';
        return view('admin.laporan.index', compact('module'));
    }
}
