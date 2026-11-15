<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\ReferensiController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\PenyetoranController;
use App\Http\Controllers\Api\SupplierRatingController;
use App\Http\Controllers\PublicController;

// Authentication routes (no auth required)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/register', [AuthController::class, 'registerPetani'])->middleware('throttle:register');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile'])->middleware('role:petani');
});

// Reference data routes (no auth required)
Route::get('/ref/wilayah', [ReferensiController::class, 'wilayah']);
Route::get('/ref/komoditas', [ReferensiController::class, 'komoditas']);
Route::get('/ref/kategori', [ReferensiController::class, 'kategori']);

// Public API - Harga terkini (untuk Flutter & web)
Route::get('/public/harga-terkini', [PublicController::class, 'apiHargaTerkini'])->name('api.public.harga-terkini');
Route::get('/public/harga-tren', [PublicController::class, 'apiHargaTren']);

// Supplier (public - no auth)
Route::get('/suppliers', [SupplierController::class, 'index']);
Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);

// Petani routes (protected)
Route::middleware(['auth:sanctum', 'role:petani'])->prefix('petani')->group(function () {
    Route::apiResource('laporan', LaporanController::class);
    Route::get('penyetoran', [PenyetoranController::class, 'index']);
    Route::post('penyetoran', [PenyetoranController::class, 'store']);
    Route::get('penyetoran/{penyetoran}', [PenyetoranController::class, 'show']);
    Route::post('suppliers/{supplier}/rating', [SupplierRatingController::class, 'store']);
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});
