<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use App\Models\Komoditas;
use Illuminate\Http\Request;

class ReferensiController extends Controller
{
    /**
     * Get all active wilayah (optionally filter by kabupaten)
     */
    public function wilayah(Request $request)
    {
        $query = Wilayah::active()->orderBy('provinsi')->orderBy('kabupaten')->orderBy('kecamatan')->orderBy('desa');

        if ($request->filled('kabupaten')) {
            $query->where('kabupaten', $request->kabupaten);
        }

        $wilayah = $query->get();

        return response()->json([
            'success' => true,
            'data' => $wilayah
        ]);
    }

    /**
     * Get all active komoditas (optionally filter by kategori)
     */
    public function komoditas(Request $request)
    {
        $query = Komoditas::active()->orderBy('kategori')->orderBy('name')->orderBy('grade');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $komoditas = $query->get();

        return response()->json([
            'success' => true,
            'data' => $komoditas
        ]);
    }

    /**
     * Get list of kategori for filter
     */
    public function kategori(Request $request)
    {
        $kategori = Komoditas::active()
            ->distinct()
            ->pluck('kategori')
            ->filter()
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $kategori
        ]);
    }
}
