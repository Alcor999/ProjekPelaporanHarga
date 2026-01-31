<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use App\Models\Komoditas;
use Illuminate\Http\Request;

class ReferensiController extends Controller
{
    /**
     * Get all active wilayah
     */
    public function wilayah(Request $request)
    {
        $wilayah = Wilayah::active()
            ->where('kabupaten', 'Ngawi')
            ->orderBy('kecamatan')
            ->orderBy('desa')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $wilayah
        ]);
    }

    /**
     * Get all active komoditas
     */
    public function komoditas(Request $request)
    {
        $komoditas = Komoditas::active()
            ->orderBy('name')
            ->orderBy('grade')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $komoditas
        ]);
    }
}
