<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarga;
use App\Models\User;
use App\Models\Wilayah;
use App\Models\Komoditas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    public function index(Request $request)
    {
        $periode = min(90, max(7, (int) $request->get('periode', 30)));
        $wilayahId = $request->get('wilayah_id');
        $komoditasId = $request->get('komoditas_id');

        $tanggalDari = now()->subDays($periode)->startOfDay();

        // Statistik utama
        $stats = [
            'total_petani' => User::where('role', 'petani')->count(),
            'petani_aktif' => User::where('role', 'petani')->where('status', 'active')->count(),
            'total_laporan' => LaporanHarga::count(),
            'laporan_periode' => LaporanHarga::where('tanggal_laporan', '>=', $tanggalDari)->count(),
            'laporan_pending' => LaporanHarga::where('status', 'pending')->count(),
            'laporan_approved' => LaporanHarga::where('status', 'approved')->count(),
            'laporan_rejected' => LaporanHarga::where('status', 'rejected')->count(),
            'total_wilayah' => Wilayah::active()->count(),
            'total_komoditas' => Komoditas::active()->count(),
        ];

        // Harga rata-rata periode ini
        $hargaQuery = LaporanHarga::where('status', 'approved')
            ->where('tanggal_laporan', '>=', $tanggalDari);
        if ($wilayahId) $hargaQuery->where('wilayah_id', $wilayahId);
        if ($komoditasId) $hargaQuery->where('komoditas_id', $komoditasId);
        $satuanAktif = (clone $hargaQuery)->distinct()->pluck('satuan')->filter()->values();
        $stats['harga_rata_periode'] = (clone $hargaQuery)->avg('harga') ?? 0;
        $stats['satuan_periode'] = $satuanAktif->count() === 1 ? $satuanAktif->first() : ($satuanAktif->isEmpty() ? 'kg' : 'campuran satuan');

        // Statistik per wilayah
        $perWilayah = LaporanHarga::select([
                'wilayah_id',
                DB::raw('COUNT(*) as jumlah_laporan'),
                DB::raw('AVG(harga) as harga_rata'),
                DB::raw('MIN(harga) as harga_min'),
                DB::raw('MAX(harga) as harga_max'),
            ])
            ->with('wilayah:id,kecamatan,kabupaten')
            ->where('status', 'approved')
            ->where('tanggal_laporan', '>=', $tanggalDari);
        if ($wilayahId) $perWilayah->where('wilayah_id', $wilayahId);
        if ($komoditasId) $perWilayah->where('komoditas_id', $komoditasId);
        $perWilayah = $perWilayah->groupBy('wilayah_id')->orderByDesc('jumlah_laporan')->limit(10)->get();

        // Statistik per komoditas
        $perKomoditas = LaporanHarga::select([
                'komoditas_id',
                DB::raw('COUNT(*) as jumlah_laporan'),
                DB::raw('AVG(harga) as harga_rata'),
                DB::raw('MIN(harga) as harga_min'),
                DB::raw('MAX(harga) as harga_max'),
            ])
            ->with('komoditas:id,name,grade,kategori')
            ->where('status', 'approved')
            ->where('tanggal_laporan', '>=', $tanggalDari);
        if ($wilayahId) $perKomoditas->where('wilayah_id', $wilayahId);
        if ($komoditasId) $perKomoditas->where('komoditas_id', $komoditasId);
        $perKomoditas = $perKomoditas->groupBy('komoditas_id')->orderByDesc('jumlah_laporan')->limit(10)->get();

        // Tren harga harian
        $trenHarga = LaporanHarga::select([
                DB::raw('DATE(tanggal_laporan) as tanggal'),
                DB::raw('AVG(harga) as harga_rata'),
                DB::raw('COUNT(*) as jumlah'),
            ])
            ->where('status', 'approved')
            ->where('tanggal_laporan', '>=', $tanggalDari);
        if ($wilayahId) $trenHarga->where('wilayah_id', $wilayahId);
        if ($komoditasId) $trenHarga->where('komoditas_id', $komoditasId);
        $trenHarga = $trenHarga->groupBy(DB::raw('DATE(tanggal_laporan)'))->orderBy('tanggal')->get();

        // Statistik per kategori komoditas
        $perKategori = LaporanHarga::select([
                DB::raw('komoditas.kategori as kategori'),
                DB::raw('COUNT(*) as jumlah'),
                DB::raw('AVG(laporan_harga.harga) as harga_rata'),
            ])
            ->join('komoditas', 'laporan_harga.komoditas_id', '=', 'komoditas.id')
            ->where('laporan_harga.status', 'approved')
            ->where('laporan_harga.tanggal_laporan', '>=', $tanggalDari);
        if ($wilayahId) $perKategori->where('laporan_harga.wilayah_id', $wilayahId);
        $perKategori = $perKategori->groupBy('komoditas.kategori')->orderByDesc('jumlah')->get();

        // Top petani pelapor (batched user lookup tanpa N+1)
        $topPetaniRaw = LaporanHarga::select('user_id')
            ->selectRaw('COUNT(*) as total_laporan')
            ->where('tanggal_laporan', '>=', $tanggalDari)
            ->groupBy('user_id')
            ->orderByDesc('total_laporan')
            ->limit(10)
            ->get();

        $topUsers = User::whereIn('id', $topPetaniRaw->pluck('user_id'))->get()->keyBy('id');
        $topPetani = $topPetaniRaw->map(function ($item) use ($topUsers) {
            $user = $topUsers->get($item->user_id);
            return (object) [
                'name' => $user?->name ?? '-',
                'email' => $user?->email ?? '-',
                'total_laporan' => $item->total_laporan,
            ];
        });

        $wilayah = Wilayah::active()->orderBy('kabupaten')->orderBy('kecamatan')->get();
        $komoditas = Komoditas::active()->orderBy('kategori')->orderBy('name')->get();

        return view('admin.statistik.index', compact(
            'stats', 'perWilayah', 'perKomoditas', 'trenHarga', 'perKategori', 'topPetani',
            'wilayah', 'komoditas', 'periode', 'wilayahId', 'komoditasId'
        ));
    }

    public function apiLive(Request $request)
    {
        $periode = min(90, max(7, (int) $request->get('periode', 30)));
        $wilayahId = $request->get('wilayah_id');
        $komoditasId = $request->get('komoditas_id');
        $tanggalDari = now()->subDays($periode)->startOfDay();

        $approvedBase = LaporanHarga::where('status', 'approved')
            ->where('tanggal_laporan', '>=', $tanggalDari);
        if ($wilayahId) {
            $approvedBase->where('wilayah_id', $wilayahId);
        }
        if ($komoditasId) {
            $approvedBase->where('komoditas_id', $komoditasId);
        }

        $laporanPeriodeQuery = LaporanHarga::where('tanggal_laporan', '>=', $tanggalDari);
        if ($wilayahId) {
            $laporanPeriodeQuery->where('wilayah_id', $wilayahId);
        }
        if ($komoditasId) {
            $laporanPeriodeQuery->where('komoditas_id', $komoditasId);
        }

        $satuanAktif = (clone $approvedBase)->distinct()->pluck('satuan')->filter()->values();

        $stats = [
            'total_petani' => User::where('role', 'petani')->count(),
            'total_laporan' => LaporanHarga::count(),
            'laporan_pending' => LaporanHarga::where('status', 'pending')->count(),
            'laporan_approved' => LaporanHarga::where('status', 'approved')->count(),
            'laporan_rejected' => LaporanHarga::where('status', 'rejected')->count(),
            'laporan_periode' => $laporanPeriodeQuery->count(),
            'harga_rata' => (clone $approvedBase)->avg('harga'),
            'satuan_periode' => $satuanAktif->count() === 1 ? $satuanAktif->first() : ($satuanAktif->isEmpty() ? 'kg' : 'campuran satuan'),
        ];

        $trenHarga = (clone $approvedBase)
            ->select([
                DB::raw('DATE(tanggal_laporan) as tanggal'),
                DB::raw('AVG(harga) as harga_rata'),
                DB::raw('COUNT(*) as jumlah'),
            ])
            ->groupBy(DB::raw('DATE(tanggal_laporan)'))
            ->orderBy('tanggal')
            ->get();

        return response()->json(['success' => true, 'stats' => $stats, 'tren' => $trenHarga]);
    }
}
