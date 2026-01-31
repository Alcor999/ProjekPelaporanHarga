<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\ReferensiController;

// Note: /api/public/harga-terkini is in routes/web.php for web frontend access

// Authentication routes (no auth required)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'registerPetani']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
});

// Reference data routes (no auth required)
Route::get('/ref/wilayah', [ReferensiController::class, 'wilayah']);
Route::get('/ref/komoditas', [ReferensiController::class, 'komoditas']);

// Petani routes (protected)
Route::middleware(['auth:sanctum', 'role:petani'])->prefix('petani')->group(function () {
    Route::apiResource('laporan', LaporanController::class);
});

