<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LaporanHarga;
use App\Models\Wilayah;
use App\Models\Komoditas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $trenHarga = LaporanHarga::select([
                DB::raw('DATE(tanggal_laporan) as tanggal'),
                DB::raw('AVG(harga) as harga_rata'),
                DB::raw('COUNT(*) as jumlah')
            ])
            ->where('status', 'approved')
            ->where('tanggal_laporan', '>=', now()->subDays(14))
            ->groupBy(DB::raw('DATE(tanggal_laporan)'))
            ->orderBy('tanggal')
            ->get();

        return view('admin.dashboard', compact('stats', 'trenHarga'));
    }
}
