<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LaporanHarga;
use App\Models\Wilayah;
use App\Models\Komoditas;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_petani' => User::where('role', 'petani')->count(),
            'total_laporan' => LaporanHarga::count(),
            'laporan_pending' => LaporanHarga::where('status', 'pending')->count(),
            'laporan_approved' => LaporanHarga::where('status', 'approved')->count(),
            'total_wilayah' => Wilayah::active()->count(),
            'total_komoditas' => Komoditas::active()->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
