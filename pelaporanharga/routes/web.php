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
use App\Http\Controllers\Admin\StatistikController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PenyetoranAdminController;
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
    Route::get('/broadcast', [BroadcastController::class, 'index'])->name('broadcast.index');
    Route::get('/broadcast/create', [BroadcastController::class, 'create'])->name('broadcast.create');
    Route::post('/broadcast', [BroadcastController::class, 'store'])->name('broadcast.store');
    Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik.index');
    Route::get('/statistik-live', [StatistikController::class, 'apiLive'])->name('statistik.api');
});

// Login Admin - route terpisah untuk menghindari konflik
Route::get('/panel/login', function () {
    return view('admin.auth.login');
})->name('admin.login')->middleware('guest');

Route::post('/panel/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Hanya admin yang dapat login di sini.',
            ]);
        }
        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.',
    ])->withInput($request->only('email'));
})->name('admin.login.post')->middleware('guest');

// Redirect /login dan /admin/login ke panel login
Route::get('/login', function () {
    return redirect()->to('/panel/login');
})->name('login');
Route::get('/admin/login', function () {
    return redirect()->to('/panel/login');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');
