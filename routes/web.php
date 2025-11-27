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

    Route::get('/chart-tipe-member', [App\Http\Controllers\Dashboard::class, 'chartTipeMember'])->name('chart-tipe-member');

    Route::get('/data-member', [App\Http\Controllers\MemberController::class, 'index'])->name('data-member');
    Route::get('/data-member-get', [App\Http\Controllers\MemberController::class, 'get'])->name('data-member-get');
    Route::post('/data-member-store', [App\Http\Controllers\MemberController::class, 'store'])->name('data-member-store');
    Route::get('data-member-edit/{params}', [App\Http\Controllers\MemberController::class, 'edit'])->name('data-member-edit');
    Route::post('/data-member-update/{params}', [App\Http\Controllers\MemberController::class, 'update'])->name('data-member-update');
    Route::delete('/data-member-delete/{params}', [App\Http\Controllers\MemberController::class, 'delete'])->name('data-member-delete');
    Route::get('/edit-member-id/{params}', [App\Http\Controllers\MemberController::class, 'editMemberid'])->name('edit-member-id');
    Route::post('/update-member-id/{params}', [App\Http\Controllers\MemberController::class, 'updateMemberid'])->name('update-member-id');

    Route::get('/get-referal/{params}', [App\Http\Controllers\MemberController::class, 'getReferal'])->name('get-referal');
    Route::post('/update-referal/{params}', [App\Http\Controllers\MemberController::class, 'updateReferal'])->name('update-referal');

    Route::get('/paket', [App\Http\Controllers\PaketController::class, 'index'])->name('paket');
    Route::get('/paket-get', [App\Http\Controllers\PaketController::class, 'get'])->name('paket-get');
    Route::post('/paket-store', [App\Http\Controllers\PaketController::class, 'store'])->name('paket-store');
    Route::get('paket-edit/{params}', [App\Http\Controllers\PaketController::class, 'edit'])->name('paket-edit');
    Route::post('/paket-update/{params}', [App\Http\Controllers\PaketController::class, 'update'])->name('paket-update');
    Route::delete('/paket-delete/{params}', [App\Http\Controllers\PaketController::class, 'delete'])->name('paket-delete');

    Route::get('/clas', [App\Http\Controllers\ClasController::class, 'index'])->name('clas');
    Route::get('/clas-get', [App\Http\Controllers\ClasController::class, 'get'])->name('clas-get');
    Route::post('/clas-store', [App\Http\Controllers\ClasController::class, 'store'])->name('clas-store');
    Route::get('clas-edit/{params}', [App\Http\Controllers\ClasController::class, 'edit'])->name('clas-edit');
    Route::post('/clas-update/{params}', [App\Http\Controllers\ClasController::class, 'update'])->name('clas-update');
    Route::delete('/clas-delete/{params}', [App\Http\Controllers\ClasController::class, 'delete'])->name('clas-delete');

    Route::get('/instruktur', [App\Http\Controllers\InstrukturController::class, 'index'])->name('instruktur');
    Route::get('/instruktur-get', [App\Http\Controllers\InstrukturController::class, 'get'])->name('instruktur-get');
    Route::post('/instruktur-store', [App\Http\Controllers\InstrukturController::class, 'store'])->name('instruktur-store');
    Route::get('instruktur-edit/{params}', [App\Http\Controllers\InstrukturController::class, 'edit'])->name('instruktur-edit');
    Route::post('/instruktur-update/{params}', [App\Http\Controllers\InstrukturController::class, 'update'])->name('instruktur-update');
    Route::delete('/instruktur-delete/{params}', [App\Http\Controllers\InstrukturController::class, 'delete'])->name('instruktur-delete');

    Route::get('/transaksi', [App\Http\Controllers\TransaksiController::class, 'index'])->name('transaksi');
    Route::get('/transaksi-get', [App\Http\Controllers\TransaksiController::class, 'get'])->name('transaksi-get');
    Route::post('/transaksi-store', [App\Http\Controllers\TransaksiController::class, 'store'])->name('transaksi-store');
    Route::get('transaksi-edit/{params}', [App\Http\Controllers\TransaksiController::class, 'edit'])->name('transaksi-edit');
    Route::post('/transaksi-update/{params}', [App\Http\Controllers\TransaksiController::class, 'update'])->name('transaksi-update');
    Route::delete('/transaksi-delete/{params}', [App\Http\Controllers\TransaksiController::class, 'delete'])->name('transaksi-delete');
    Route::get('/konfirmasi-transaksi/{params}', [App\Http\Controllers\TransaksiController::class, 'konfirmasi'])->name('konfirmasi-transaksi');
    Route::get('/cancel-transaksi/{params}', [App\Http\Controllers\TransaksiController::class, 'cancel'])->name('cancel-transaksi');

    Route::get('/get-tanggal-expired/{params}', [App\Http\Controllers\TransaksiController::class, 'getTanggalExpired'])->name('get-tanggal-expired');

    Route::post('/edit-tanggal-expired/{params}', [App\Http\Controllers\TransaksiController::class, 'editTanggalExpired'])->name('edit-tanggal-expired');

    Route::get('/get-perpanjang-data/{params}', [App\Http\Controllers\TransaksiController::class, 'getDataPerpanjang'])->name('get-perpanjang-data');
    Route::post('/perpanjang-member/{params}', [App\Http\Controllers\TransaksiController::class, 'perpanjangMember'])->name('perpanjang-member');

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

    Route::get('/banner', [App\Http\Controllers\BannerController::class, 'index'])->name('banner');
    Route::get('/banner-get', [App\Http\Controllers\BannerController::class, 'get'])->name('banner-get');
    Route::post('/banner-store', [App\Http\Controllers\BannerController::class, 'store'])->name('banner-store');
    Route::get('banner-edit/{params}', [App\Http\Controllers\BannerController::class, 'edit'])->name('banner-edit');
    Route::post('/banner-update/{params}', [App\Http\Controllers\BannerController::class, 'update'])->name('banner-update');
    Route::delete('/banner-delete/{params}', [App\Http\Controllers\BannerController::class, 'delete'])->name('banner-delete');

    Route::get('/transaksi-clas', [App\Http\Controllers\TransaksiClasController::class, 'index'])->name('transaksi-clas');
    Route::get('/transaksi-clas-get', [App\Http\Controllers\TransaksiClasController::class, 'get'])->name('transaksi-clas-get');
    Route::get('/konfirmasi-transaksi-clas/{params}', [App\Http\Controllers\TransaksiClasController::class, 'konfirmasi'])->name('konfirmasi-transaksi-clas');
    Route::get('/cancel-transaksi-clas/{params}', [App\Http\Controllers\TransaksiClasController::class, 'cancel'])->name('cancel-transaksi-clas');
    Route::delete('/transaksi-clas-delete/{params}', [App\Http\Controllers\TransaksiClasController::class, 'delete'])->name('transaksi-clas-delete');

    Route::get('/laoran', [App\Http\Controllers\Laporan::class, 'index'])->name('laporan');
    Route::get('/laporan-get', [App\Http\Controllers\Laporan::class, 'get'])->name('laporan-get');
    Route::get('/laporan-export', [App\Http\Controllers\Laporan::class, 'export_excel'])->name('laporan-export');

    Route::get('/absen', [App\Http\Controllers\AbsensiController::class, 'index'])->name('absen');
    Route::get('/absen-get', [App\Http\Controllers\AbsensiController::class, 'get'])->name('absen-get');
});

Route::get('/logout', [App\Http\Controllers\Auth::class, 'logout'])->name('logout');
