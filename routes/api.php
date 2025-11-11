<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ClasController;
use App\Http\Controllers\InstrukturController;
use App\Http\Controllers\PaketController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProdukController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('cors')->group(function () {
    Route::get('/api-paket', [PaketController::class, 'get']);
    Route::post('/api-register', [Auth::class, 'register']);
    Route::post('/api-login', [Auth::class, 'do_login']);
    Route::get('/api-banner', [BannerController::class, 'getData']);
    Route::get('/api-instruktur', [InstrukturController::class, 'getData']);
    Route::get('/api-instruktur/{uuid}', [InstrukturController::class, 'getDetail']);
    Route::get('/api-clas', [ClasController::class, 'getData']);
    Route::get('/api-clas/{uuid}', [ClasController::class, 'getDetail']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('role:kasir')->group(function () {
            Route::get('/api-produk', [ProdukController::class, 'getData']);
            Route::post('/api-penjualan', [PenjualanController::class, 'store']);
        });

        Route::middleware('role:member')->group(function () {
            // Route::get('/api-member', [Auth::class, 'getMember']);
            Route::get('/api-member/{uuid}', [Auth::class, 'getMemberDetail']);
            // Route::post('/api-member-update', [Auth::class, 'updateMember']);
            // Route::post('/api-member-ubah-password', [Auth::class, 'ubahPassword']);
            // Route::post('/api-member-ubah-foto', [Auth::class, 'ubahFoto']);
            // Route::get('/api-member-paket', [Auth::class, 'getPaketMember']);
            // Route::get('/api-member-clas', [Auth::class, 'getClasMember']);
            // Route::get('/api-member-transaksi', [Auth::class, 'getTransaksiMember']);
            // Route::get('/api-member-transaksi/{uuid}', [Auth::class, 'getTransaksiDetail']);
        });

        Route::get('/api-logout', [Auth::class, 'revoke']);
    });
});
