<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarga;
use App\Models\Wilayah;
use App\Models\Komoditas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LaporanController extends Controller
{
    /**
     * Get all laporan for authenticated user
     */
    public function index(Request $request)
    {
        $laporan = $request->user()
            ->laporanHarga()
            ->with(['wilayah', 'komoditas'])
            ->orderBy('tanggal_laporan', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $laporan
        ]);
    }

    /**
     * Create new laporan
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wilayah_id' => 'required|exists:wilayah,id',
            'komoditas_id' => 'required|exists:komoditas,id',
            'harga' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:10',
            'tanggal_panen' => 'required|date|before_or_equal:today',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $laporan = LaporanHarga::create([
            'user_id' => $request->user()->id,
            'wilayah_id' => $request->wilayah_id,
            'komoditas_id' => $request->komoditas_id,
            'harga' => $request->harga,
            'satuan' => $request->satuan,
            'tanggal_panen' => $request->tanggal_panen,
            'catatan' => $request->catatan,
            'status' => 'pending',
        ]);

        $laporan->load(['wilayah', 'komoditas']);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dibuat',
            'data' => $laporan
        ], 201);
    }

    /**
     * Get specific laporan
     */
    public function show(Request $request, LaporanHarga $laporan)
    {
        // Ensure user can only access their own laporan
        if ($laporan->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $laporan->load(['wilayah', 'komoditas']);

        return response()->json([
            'success' => true,
            'data' => $laporan
        ]);
    }

    /**
     * Update laporan (only if status is pending)
     */
    public function update(Request $request, LaporanHarga $laporan)
    {
        // Ensure user can only access their own laporan
        if ($laporan->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Only allow update if status is pending
        if ($laporan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Laporan sudah diverifikasi, tidak dapat diubah'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'wilayah_id' => 'sometimes|exists:wilayah,id',
            'komoditas_id' => 'sometimes|exists:komoditas,id',
            'harga' => 'sometimes|numeric|min:0',
            'satuan' => 'sometimes|string|max:10',
            'tanggal_panen' => 'sometimes|date|before_or_equal:today',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $laporan->update($request->only([
            'wilayah_id', 'komoditas_id', 'harga', 'satuan', 'tanggal_panen', 'catatan'
        ]));

        $laporan->load(['wilayah', 'komoditas']);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diupdate',
            'data' => $laporan
        ]);
    }
}
