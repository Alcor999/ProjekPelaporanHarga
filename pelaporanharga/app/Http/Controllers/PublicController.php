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
     * Homepage publik dengan harga terkini - SEMUA data approved dengan perbandingan wilayah
     */
    public function index(Request $request)
    {
        $wilayahId = $request->get('wilayah_id');
        $komoditasId = $request->get('komoditas_id');

        // Subquery untuk mendapatkan harga terbaru per komoditas per wilayah
        $latestPrices = $this->getLatestApprovedPrices($wilayahId, $komoditasId);

        // Group by komoditas untuk perbandingan antar wilayah
        $hargaPerKomoditas = $latestPrices->groupBy('komoditas_id')->map(function ($items) {
            $first = $items->first();
            $hargaList = $items->map(fn($item) => (float) $item->harga_terbaru);
            return [
                'komoditas' => $first->komoditas,
                'komoditas_id' => $first->komoditas_id,
                'wilayah_data' => $items->map(fn($item) => [
                    'wilayah_id' => $item->wilayah_id,
                    'wilayah' => $item->wilayah,
                    'harga_terbaru' => (float) $item->harga_terbaru,
                    'harga_sebelumnya' => $item->harga_sebelumnya ? (float) $item->harga_sebelumnya : null,
                    'perubahan' => $item->harga_sebelumnya ? round((float) $item->harga_terbaru - (float) $item->harga_sebelumnya, 2) : null,
                    'perubahan_persen' => $item->harga_sebelumnya && (float) $item->harga_sebelumnya > 0
                        ? round((((float) $item->harga_terbaru - (float) $item->harga_sebelumnya) / (float) $item->harga_sebelumnya) * 100, 1)
                        : null,
                    'tanggal_update' => $item->tanggal_terbaru,
                    'jumlah_laporan' => $item->jumlah_laporan,
                ])->values(),
                'harga_terendah' => $hargaList->min(),
                'harga_tertinggi' => $hargaList->max(),
                'harga_rata_rata' => round($hargaList->avg(), 2),
                'total_wilayah' => $items->count(),
            ];
        })->values();

        // Data untuk filter dropdown
        $wilayah = Wilayah::active()->orderBy('provinsi')->orderBy('kabupaten')->orderBy('kecamatan')->get();
        $komoditas = Komoditas::active()->orderBy('kategori')->orderBy('name')->get();

        return view('public.index', compact('hargaPerKomoditas', 'wilayah', 'komoditas', 'wilayahId', 'komoditasId'));
    }

    /**
     * Get latest approved prices per komoditas per wilayah with previous price for comparison
     */
    private function getLatestApprovedPrices($wilayahId = null, $komoditasId = null)
    {
        // Build base query with filters
        $baseQuery = LaporanHarga::where('status', 'approved');
        if ($wilayahId) {
            $baseQuery->where('wilayah_id', $wilayahId);
        }
        if ($komoditasId) {
            $baseQuery->where('komoditas_id', $komoditasId);
        }

        // Get unique wilayah-komoditas combinations
        $combinations = (clone $baseQuery)
            ->select('wilayah_id', 'komoditas_id')
            ->groupBy('wilayah_id', 'komoditas_id')
            ->get();

        $results = collect();

        foreach ($combinations as $combo) {
            // Get latest approved laporan for this combination
            $latest = LaporanHarga::where('wilayah_id', $combo->wilayah_id)
                ->where('komoditas_id', $combo->komoditas_id)
                ->where('status', 'approved')
                ->orderByDesc('tanggal_laporan')
                ->orderByDesc('id')
                ->with(['wilayah:id,kecamatan,desa,kabupaten,provinsi', 'komoditas:id,name,grade,kategori'])
                ->first();

            if (!$latest) continue;

            // Get previous price (second latest)
            $previous = LaporanHarga::where('wilayah_id', $combo->wilayah_id)
                ->where('komoditas_id', $combo->komoditas_id)
                ->where('status', 'approved')
                ->where('id', '<', $latest->id)
                ->orderByDesc('tanggal_laporan')
                ->orderByDesc('id')
                ->first();

            // Count total approved reports
            $count = LaporanHarga::where('wilayah_id', $combo->wilayah_id)
                ->where('komoditas_id', $combo->komoditas_id)
                ->where('status', 'approved')
                ->count();

            $latest->harga_terbaru = $latest->harga;
            $latest->tanggal_terbaru = $latest->tanggal_laporan;
            $latest->harga_sebelumnya = $previous?->harga;
            $latest->jumlah_laporan = $count;

            $results->push($latest);
        }

        return $results;
    }

    /**
     * API endpoint untuk harga terkini - SEMUA data approved dengan perbandingan
     */
    public function apiHargaTerkini(Request $request)
    {
        $wilayahId = $request->get('wilayah_id');
        $komoditasId = $request->get('komoditas_id');

        $latestPrices = $this->getLatestApprovedPrices($wilayahId, $komoditasId);

        // Group by komoditas untuk perbandingan antar wilayah
        $hargaPerKomoditas = $latestPrices->groupBy('komoditas_id')->map(function ($items) {
            $first = $items->first();
            $hargaList = $items->map(fn($item) => (float) $item->harga_terbaru);
            return [
                'komoditas' => $first->komoditas,
                'komoditas_id' => $first->komoditas_id,
                'wilayah_data' => $items->map(fn($item) => [
                    'wilayah_id' => $item->wilayah_id,
                    'wilayah' => $item->wilayah,
                    'harga_terbaru' => (float) $item->harga_terbaru,
                    'harga_sebelumnya' => $item->harga_sebelumnya ? (float) $item->harga_sebelumnya : null,
                    'perubahan' => $item->harga_sebelumnya ? round((float) $item->harga_terbaru - (float) $item->harga_sebelumnya, 2) : null,
                    'perubahan_persen' => $item->harga_sebelumnya && (float) $item->harga_sebelumnya > 0
                        ? round((((float) $item->harga_terbaru - (float) $item->harga_sebelumnya) / (float) $item->harga_sebelumnya) * 100, 1)
                        : null,
                    'tanggal_update' => $item->tanggal_terbaru,
                    'jumlah_laporan' => $item->jumlah_laporan,
                ])->values(),
                'harga_terendah' => $hargaList->min(),
                'harga_tertinggi' => $hargaList->max(),
                'harga_rata_rata' => round($hargaList->avg(), 2),
                'total_wilayah' => $items->count(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $hargaPerKomoditas,
        ]);
    }

    /**
     * API endpoint untuk grafik tren harga
     */
    public function apiHargaTren(Request $request)
    {
        $wilayahId = $request->get('wilayah_id');
        $komoditasId = $request->get('komoditas_id');
        $days = min(90, max(7, (int) $request->get('days', 30)));

        $startDate = now()->subDays($days)->format('Y-m-d');

        $query = LaporanHarga::select([
                DB::raw('DATE(tanggal_laporan) as tanggal'),
                'wilayah_id',
                'komoditas_id',
                DB::raw('AVG(harga) as harga_rata_rata'),
                DB::raw('MIN(harga) as harga_terendah'),
                DB::raw('MAX(harga) as harga_tertinggi'),
                DB::raw('COUNT(*) as jumlah_laporan')
            ])
            ->with(['wilayah:id,kecamatan,desa,kabupaten', 'komoditas:id,name,grade,kategori'])
            ->where('status', 'approved')
            ->whereDate('tanggal_laporan', '>=', $startDate);

        if ($wilayahId) {
            $query->where('wilayah_id', $wilayahId);
        }
        if ($komoditasId) {
            $query->where('komoditas_id', $komoditasId);
        }

        $data = $query->groupBy(DB::raw('DATE(tanggal_laporan)'), 'wilayah_id', 'komoditas_id')
            ->orderBy('tanggal')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'days' => $days
        ]);
    }
}
