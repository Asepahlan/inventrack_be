<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SaldoController;
use App\Http\Controllers\LaporanController;

Route::post('/login', [AuthController::class, 'authenticate']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Master Data
    Route::apiResource('kategori', KategoriController::class);
    Route::apiResource('barang', BarangController::class);
    Route::apiResource('user', UserController::class);
    
    // Transaksi
    Route::apiResource('barang-masuk', BarangMasukController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('barang-keluar', BarangKeluarController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('saldo', SaldoController::class)->only(['index', 'store', 'destroy']);
    
    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index']);
});
