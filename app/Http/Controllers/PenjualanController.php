<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenjualanRequest;
use App\Http\Requests\UpdatePenjualanRequest;
use App\Models\Penjualan;
use Illuminate\Http\Request;

class PenjualanController extends BaseController
{
    public function store(Request $request)
    {
        $penjualan = Penjualan::create([
            'penjualan_items' => $request->penjualan_items,
        ]);

        return $this->sendResponse($penjualan, 'Penjualan created successfully.');
    }
}
