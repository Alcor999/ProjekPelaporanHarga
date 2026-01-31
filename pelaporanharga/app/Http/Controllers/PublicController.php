<?php

namespace App\Http\Controllers;

use App\Models\LaporanHarga;
use App\Models\Wilayah;
use App\Models\Komoditas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    /**
     * Homepage publik dengan harga terkini
     */
    public function index(Request $request)
    {
        $wilayahId = $request->get('wilayah_id');
        $komoditasId = $request->get('komoditas_id');
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));

        // Query harga agregat per wilayah dan komoditas
        $query = LaporanHarga::select([
                'wilayah_id',
                'komoditas_id',
                DB::raw('AVG(harga) as harga_rata_rata'),
                DB::raw('MIN(harga) as harga_terendah'),
                DB::raw('MAX(harga) as harga_tertinggi'),
                DB::raw('COUNT(*) as jumlah_laporan')
            ])
            ->with(['wilayah', 'komoditas'])
            ->where('status', 'approved')
            ->whereDate('tanggal_laporan', $tanggal);

        if ($wilayahId) {
            $query->where('wilayah_id', $wilayahId);
        }

        if ($komoditasId) {
            $query->where('komoditas_id', $komoditasId);
        }

        // Urutkan berdasarkan ID (tanpa join ke tabel relasi agar kompatibel di semua DB)
        $hargaAgregat = $query->groupBy('wilayah_id', 'komoditas_id')
                              ->orderBy('wilayah_id')
                              ->orderBy('komoditas_id')
                              ->get();

        // Data untuk filter dropdown (khusus Ngawi)
        $wilayah = Wilayah::active()
            ->where('kabupaten', 'Ngawi')
            ->orderBy('kecamatan')
            ->orderBy('desa')
            ->get();
        $komoditas = Komoditas::active()->orderBy('name')->get();

        return view('public.index', compact('hargaAgregat', 'wilayah', 'komoditas', 'tanggal', 'wilayahId', 'komoditasId'));
    }

    /**
     * API endpoint untuk harga terkini (untuk AJAX polling)
     */
    public function apiHargaTerkini(Request $request)
    {
        $wilayahId = $request->get('wilayah_id');
        $komoditasId = $request->get('komoditas_id');
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));

        $query = LaporanHarga::select([
                'wilayah_id',
                'komoditas_id',
                DB::raw('AVG(harga) as harga_rata_rata'),
                DB::raw('MIN(harga) as harga_terendah'),
                DB::raw('MAX(harga) as harga_tertinggi'),
                DB::raw('COUNT(*) as jumlah_laporan'),
                DB::raw('MAX(tanggal_laporan) as tanggal_terakhir')
            ])
            ->with(['wilayah:id,kecamatan,desa,kabupaten', 'komoditas:id,name,grade'])
            ->where('status', 'approved')
            ->whereDate('tanggal_laporan', $tanggal);

        if ($wilayahId) {
            $query->where('wilayah_id', $wilayahId);
        }

        if ($komoditasId) {
            $query->where('komoditas_id', $komoditasId);
        }

        $data = $query->groupBy('wilayah_id', 'komoditas_id')
                      ->orderBy('wilayah_id')
                      ->orderBy('komoditas_id')
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'tanggal' => $tanggal
        ]);
    }
}
