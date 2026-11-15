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

        $latestPrices = $this->getLatestApprovedPrices($wilayahId, $komoditasId);
        $hargaPerKomoditas = $this->formatHargaPerKomoditas($latestPrices);

        // Data untuk filter dropdown
        $wilayah = Wilayah::active()->orderBy('provinsi')->orderBy('kabupaten')->orderBy('kecamatan')->get();
        $komoditas = Komoditas::active()->orderBy('kategori')->orderBy('name')->get();

        return view('public.index', compact('hargaPerKomoditas', 'wilayah', 'komoditas', 'wilayahId', 'komoditasId'));
    }

    /**
     * Ambil harga approved terbaru per pasangan (wilayah, komoditas, satuan) secara terbatch tanpa N+1.
     */
    private function getLatestApprovedPrices($wilayahId = null, $komoditasId = null)
    {
        $baseQuery = LaporanHarga::where('status', 'approved')
            ->select(['id', 'wilayah_id', 'komoditas_id', 'harga', 'satuan', 'tanggal_laporan']);

        if ($wilayahId) {
            $baseQuery->where('wilayah_id', $wilayahId);
        }
        if ($komoditasId) {
            $baseQuery->where('komoditas_id', $komoditasId);
        }

        $rows = $baseQuery->orderByDesc('tanggal_laporan')->orderByDesc('id')->get();
        if ($rows->isEmpty()) {
            return collect();
        }

        $grouped = $rows->groupBy(function ($row) {
            return $row->wilayah_id . '|' . $row->komoditas_id . '|' . strtolower((string) ($row->satuan ?: 'kg'));
        });

        $wilayahMap = Wilayah::whereIn('id', $rows->pluck('wilayah_id')->unique())
            ->select(['id', 'kecamatan', 'desa', 'kabupaten', 'provinsi'])
            ->get()
            ->keyBy('id');

        $komoditasMap = Komoditas::whereIn('id', $rows->pluck('komoditas_id')->unique())
            ->select(['id', 'name', 'grade', 'kategori'])
            ->get()
            ->keyBy('id');

        $results = collect();
        foreach ($grouped as $items) {
            $latest = $items->first();
            $wilayah = $wilayahMap->get($latest->wilayah_id);
            $komoditas = $komoditasMap->get($latest->komoditas_id);
            if (!$wilayah || !$komoditas) {
                continue;
            }

            $previous = $items->skip(1)->first();
            $latest->setRelation('wilayah', $wilayah);
            $latest->setRelation('komoditas', $komoditas);
            $latest->harga_terbaru = $latest->harga;
            $latest->tanggal_terbaru = $latest->tanggal_laporan;
            $latest->harga_sebelumnya = $previous?->harga;
            $latest->jumlah_laporan = $items->count();

            $results->push($latest);
        }

        return $results;
    }

    /**
     * API endpoint untuk harga terkini - SEMUA data approved dengan perbandingan per satuan
     */
    public function apiHargaTerkini(Request $request)
    {
        $wilayahId = $request->get('wilayah_id');
        $komoditasId = $request->get('komoditas_id');

        $latestPrices = $this->getLatestApprovedPrices($wilayahId, $komoditasId);
        $hargaPerKomoditas = $this->formatHargaPerKomoditas($latestPrices);

        return response()->json([
            'success' => true,
            'data' => $hargaPerKomoditas,
        ]);
    }

    private function formatHargaPerKomoditas($latestPrices)
    {
        return $latestPrices
            ->groupBy(fn($item) => $item->komoditas_id . '|' . strtolower((string) ($item->satuan ?: 'kg')))
            ->map(function ($items) {
                $first = $items->first();
                $hargaList = $items->map(fn($item) => (float) $item->harga_terbaru);

                return [
                    'komoditas' => $first->komoditas,
                    'komoditas_id' => $first->komoditas_id,
                    'satuan' => $first->satuan ?: 'kg',
                    'wilayah_data' => $items->map(function ($item) {
                        $hargaBaru = (float) $item->harga_terbaru;
                        $hargaLama = $item->harga_sebelumnya !== null ? (float) $item->harga_sebelumnya : null;

                        return [
                            'wilayah_id' => $item->wilayah_id,
                            'wilayah' => $item->wilayah,
                            'satuan' => $item->satuan ?: 'kg',
                            'harga_terbaru' => $hargaBaru,
                            'harga_sebelumnya' => $hargaLama,
                            'perubahan' => $hargaLama !== null ? round($hargaBaru - $hargaLama, 2) : null,
                            'perubahan_persen' => $hargaLama !== null && $hargaLama > 0
                                ? round((($hargaBaru - $hargaLama) / $hargaLama) * 100, 1)
                                : null,
                            'tanggal_update' => $item->tanggal_terbaru instanceof \DateTimeInterface
                                ? $item->tanggal_terbaru->format('Y-m-d')
                                : (string) $item->tanggal_terbaru,
                            'jumlah_laporan' => $item->jumlah_laporan,
                        ];
                    })->values(),
                    'harga_terendah' => $hargaList->min(),
                    'harga_tertinggi' => $hargaList->max(),
                    'harga_rata_rata' => round($hargaList->avg(), 2),
                    'total_wilayah' => $items->count(),
                ];
            })
            ->values();
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
