<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LaporanAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\Admin\KomoditasController;
use App\Http\Controllers\Admin\BroadcastController;
use App\Http\Controllers\PublicController;

// Public routes
Route::get('/', [PublicController::class, 'index'])->name('public.index');
Route::get('/api/public/harga-terkini', [PublicController::class, 'apiHargaTerkini'])->name('api.public.harga-terkini');

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

    Route::resource('wilayah', WilayahController::class)->except(['show']);
    Route::resource('komoditas', KomoditasController::class)->except(['show']);
    Route::get('/broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
    Route::get('/broadcast/create', [BroadcastController::class, 'create'])->name('broadcast.create');
    Route::post('/broadcast', [BroadcastController::class, 'store'])->name('broadcast.store');
});

// Redirect generic /login ke halaman login admin
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Auth routes (simplified for demo)
Route::get('/admin/login', function () {
    return view('admin.auth.login');
})->name('admin.login')->middleware('guest');

Route::post('/admin/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.',
    ]);
})->name('admin.login.post')->middleware('guest');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');
