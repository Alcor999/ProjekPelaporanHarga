<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierRating;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierRatingController extends Controller
{
    public function store(Request $request, Supplier $supplier)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string|max:1000',
            'penyetoran_id' => 'required|integer|exists:penyetoran,id',
        ]);

        if (!$supplier->is_active) {
            return response()->json(['message' => 'Supplier tidak ditemukan'], 404);
        }

        try {
            $result = DB::transaction(function () use ($request, $supplier) {
                $penyetoran = auth()->user()
                    ->penyetoran()
                    ->where('id', $request->penyetoran_id)
                    ->lockForUpdate()
                    ->first();

                if (
                    !$penyetoran
                    || $penyetoran->supplier_id !== $supplier->id
                    || $penyetoran->status === 'cancelled'
                ) {
                    return ['error' => 'Penyetoran tidak valid atau telah dibatalkan'];
                }

                if (SupplierRating::where('penyetoran_id', $penyetoran->id)->exists()) {
                    return ['error' => 'Rating untuk penyetoran ini sudah pernah dikirim'];
                }

                $rating = SupplierRating::create([
                    'supplier_id' => $supplier->id,
                    'user_id' => auth()->id(),
                    'penyetoran_id' => $penyetoran->id,
                    'rating' => $request->rating,
                    'ulasan' => $request->ulasan,
                ]);

                return ['rating' => $rating];
            });
        } catch (QueryException $e) {
            return response()->json(['message' => 'Rating untuk penyetoran ini sudah pernah dikirim'], 422);
        }

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        $rating = $result['rating'];

        return response()->json([
            'success' => true,
            'message' => 'Rating berhasil disimpan',
            'data' => [
                'id' => $rating->id,
                'penyetoran_id' => $rating->penyetoran_id,
                'rating' => $rating->rating,
                'ulasan' => $rating->ulasan,
            ],
        ], 201);
    }
}
