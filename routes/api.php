<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\InstrukturController;
use App\Http\Controllers\PaketController;
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
    Route::get('/api-instruktur', [InstrukturController::class, 'get']);
    Route::get('/api-produk', [ProdukController::class, 'get']);
    Route::middleware('auth:sanctum')->group(function () {


        Route::get('/api-logout', [Auth::class, 'revoke']);
    });
});
