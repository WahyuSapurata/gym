<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', [App\Http\Controllers\Dashboard::class, 'index'])->name('home.index');

Route::group(['prefix' => 'login', 'middleware' => ['guest'], 'as' => 'login.'], function () {
    Route::get('/login-akun', [App\Http\Controllers\Auth::class, 'show'])->name('login-akun');
    Route::post('/login-proses', [App\Http\Controllers\Auth::class, 'login_proses'])->name('login-proses');
});

Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth'],
    'as' => 'admin.'
], function () {
    Route::get('/dashboard-admin', [App\Http\Controllers\Dashboard::class, 'dashboard_admin'])->name('dashboard-admin');

    Route::get('/data-member', [App\Http\Controllers\MemberController::class, 'index'])->name('data-member');
    Route::get('/data-member-get', [App\Http\Controllers\MemberController::class, 'get'])->name('data-member-get');
    Route::post('/data-member-store', [App\Http\Controllers\MemberController::class, 'store'])->name('data-member-store');
    Route::get('data-member-edit/{params}', [App\Http\Controllers\MemberController::class, 'edit'])->name('data-member-edit');
    Route::post('/data-member-update/{params}', [App\Http\Controllers\MemberController::class, 'update'])->name('data-member-update');
    Route::delete('/data-member-delete/{params}', [App\Http\Controllers\MemberController::class, 'delete'])->name('data-member-delete');

    Route::get('/paket', [App\Http\Controllers\PaketController::class, 'index'])->name('paket');
    Route::get('/paket-get', [App\Http\Controllers\PaketController::class, 'get'])->name('paket-get');
    Route::post('/paket-store', [App\Http\Controllers\PaketController::class, 'store'])->name('paket-store');
    Route::get('paket-edit/{params}', [App\Http\Controllers\PaketController::class, 'edit'])->name('paket-edit');
    Route::post('/paket-update/{params}', [App\Http\Controllers\PaketController::class, 'update'])->name('paket-update');
    Route::delete('/paket-delete/{params}', [App\Http\Controllers\PaketController::class, 'delete'])->name('paket-delete');

    Route::get('/transaksi', [App\Http\Controllers\TransaksiController::class, 'index'])->name('transaksi');
    Route::get('/transaksi-get', [App\Http\Controllers\TransaksiController::class, 'get'])->name('transaksi-get');
    Route::post('/transaksi-store', [App\Http\Controllers\TransaksiController::class, 'store'])->name('transaksi-store');
    Route::get('transaksi-edit/{params}', [App\Http\Controllers\TransaksiController::class, 'edit'])->name('transaksi-edit');
    Route::post('/transaksi-update/{params}', [App\Http\Controllers\TransaksiController::class, 'update'])->name('transaksi-update');
    Route::delete('/transaksi-delete/{params}', [App\Http\Controllers\TransaksiController::class, 'delete'])->name('transaksi-delete');
    Route::get('/konfirmasi-transaksi/{params}', [App\Http\Controllers\TransaksiController::class, 'konfirmasi'])->name('konfirmasi-transaksi');
    Route::get('/cancel-transaksi/{params}', [App\Http\Controllers\TransaksiController::class, 'cancel'])->name('cancel-transaksi');

    Route::get('/cetak-invoice/{params}', [App\Http\Controllers\TransaksiController::class, 'invoiceView'])->name('cetak-invoice');
    Route::get('/cetak-kartu/{params}', [App\Http\Controllers\TransaksiController::class, 'cetak_kartu'])->name('cetak-kartu');

    Route::get('/produk', [App\Http\Controllers\ProdukController::class, 'index'])->name('produk');
    Route::get('/produk-get', [App\Http\Controllers\ProdukController::class, 'get'])->name('produk-get');
    Route::post('/produk-store', [App\Http\Controllers\ProdukController::class, 'store'])->name('produk-store');
    Route::get('produk-edit/{params}', [App\Http\Controllers\ProdukController::class, 'edit'])->name('produk-edit');
    Route::post('/produk-update/{params}', [App\Http\Controllers\ProdukController::class, 'update'])->name('produk-update');
    Route::delete('/produk-delete/{params}', [App\Http\Controllers\ProdukController::class, 'delete'])->name('produk-delete');

    Route::get('/oprasional', [App\Http\Controllers\OperasionalController::class, 'index'])->name('oprasional');
    Route::get('/oprasional-get', [App\Http\Controllers\OperasionalController::class, 'get'])->name('oprasional-get');
    Route::post('/oprasional-store', [App\Http\Controllers\OperasionalController::class, 'store'])->name('oprasional-store');
    Route::get('oprasional-edit/{params}', [App\Http\Controllers\OperasionalController::class, 'edit'])->name('oprasional-edit');
    Route::post('/oprasional-update/{params}', [App\Http\Controllers\OperasionalController::class, 'update'])->name('oprasional-update');
    Route::delete('/oprasional-delete/{params}', [App\Http\Controllers\OperasionalController::class, 'delete'])->name('oprasional-delete');
});

Route::get('/logout', [App\Http\Controllers\Auth::class, 'logout'])->name('logout');
