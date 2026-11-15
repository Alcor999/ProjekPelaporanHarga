<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LaporanAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\Admin\KomoditasController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\Admin\StatistikController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PenyetoranAdminController;
use App\Http\Controllers\PublicController;

// Public routes
Route::get('/', [PublicController::class, 'index'])->name('public.index');

// Admin routes (web interface)
Route::middleware(['auth:web', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanAdminController::class, 'index'])->name('index');
        Route::get('/{laporan}', [LaporanAdminController::class, 'show'])->name('show');
        Route::get('/{laporan}/edit', [LaporanAdminController::class, 'edit'])->name('edit');
        Route::put('/{laporan}', [LaporanAdminController::class, 'update'])->name('update');
        Route::post('/{laporan}/approve', [LaporanAdminController::class, 'approve'])->name('approve');
        Route::post('/{laporan}/reject', [LaporanAdminController::class, 'reject'])->name('reject');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/petani', [UserAdminController::class, 'index'])->name('petani.index');
        Route::post('/petani/{user}/approve', [UserAdminController::class, 'approve'])->name('petani.approve');
        Route::post('/petani/{user}/block', [UserAdminController::class, 'block'])->name('petani.block');
    });

    Route::resource('wilayah', WilayahController::class)->except(['show', 'create', 'edit']);
    Route::resource('komoditas', KomoditasController::class)
        ->parameters(['komoditas' => 'komoditas'])
        ->except(['show', 'create', 'edit']);
    Route::prefix('supplier')->name('supplier.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::post('/{supplier}/harga', [SupplierController::class, 'storeHarga'])->name('harga.store');
        Route::delete('/{supplier}/harga/{harga}', [SupplierController::class, 'destroyHarga'])->name('harga.destroy');
        Route::post('/{supplier}/jadwal', [SupplierController::class, 'storeJadwal'])->name('jadwal.store');
        Route::delete('/{supplier}/jadwal/{jadwal}', [SupplierController::class, 'destroyJadwal'])->name('jadwal.destroy');
        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
    });
    Route::get('/penyetoran', [PenyetoranAdminController::class, 'index'])->name('penyetoran.index');
    Route::patch('/penyetoran/{penyetoran}/status', [PenyetoranAdminController::class, 'updateStatus'])->name('penyetoran.update-status');
    Route::get('/broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
    Route::get('/broadcast/create', [BroadcastController::class, 'create'])->name('broadcast.create');
    Route::post('/broadcast', [BroadcastController::class, 'store'])->name('broadcast.store');
    Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik.index');
    Route::get('/statistik-live', [StatistikController::class, 'apiLive'])->name('statistik.api');
});

// Login Admin berbasis controller agar kompatibel dengan php artisan route:cache
Route::get('/panel/login', [AdminController::class, 'showLoginForm'])->name('admin.login')->middleware('guest');
Route::post('/panel/login', [AdminController::class, 'login'])->name('admin.login.post')->middleware(['guest', 'throttle:login']);

Route::redirect('/login', '/panel/login')->name('login');
Route::redirect('/admin/login', '/panel/login');

Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
