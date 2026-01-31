<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LaporanAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\PublicController;

// Public routes
Route::get('/', [PublicController::class, 'index'])->name('public.index');

// Public API route (accessible from web frontend via JavaScript)
// This route is also in api.php, but we keep it here for web frontend access
Route::get('/api/public/harga-terkini', [PublicController::class, 'apiHargaTerkini'])->name('api.public.harga-terkini');

// Note: Other API routes are in routes/api.php (no CSRF token required)
// API routes in web.php would require CSRF token, which is not needed for mobile apps

// Admin routes (web interface)
Route::middleware(['auth:web', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Laporan management
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanAdminController::class, 'index'])->name('index');
        Route::get('/{laporan}', [LaporanAdminController::class, 'show'])->name('show');
        Route::get('/{laporan}/edit', [LaporanAdminController::class, 'edit'])->name('edit');
        Route::put('/{laporan}', [LaporanAdminController::class, 'update'])->name('update');
        Route::post('/{laporan}/approve', [LaporanAdminController::class, 'approve'])->name('approve');
        Route::post('/{laporan}/reject', [LaporanAdminController::class, 'reject'])->name('reject');
    });

    // User petani management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/petani', [UserAdminController::class, 'index'])->name('petani.index');
        Route::post('/petani/{user}/approve', [UserAdminController::class, 'approve'])->name('petani.approve');
        Route::post('/petani/{user}/block', [UserAdminController::class, 'block'])->name('petani.block');
    });
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
