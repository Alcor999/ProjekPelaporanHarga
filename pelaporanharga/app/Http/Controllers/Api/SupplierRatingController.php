<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierRating;
use Illuminate\Http\Request;

class SupplierRatingController extends Controller
{
    public function store(Request $request, Supplier $supplier)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'nullable|string|max:1000',
            'penyetoran_id' => 'nullable|exists:penyetoran,id',
        ]);
        if (!$supplier->is_active) {
            return response()->json(['message' => 'Supplier tidak ditemukan'], 404);
        }
        if ($request->filled('penyetoran_id')) {
            $penyetoran = auth()->user()->penyetoran()->find($request->penyetoran_id);
            if (!$penyetoran || $penyetoran->supplier_id !== $supplier->id) {
                return response()->json(['message' => 'Penyetoran tidak valid'], 422);
            }
        }
        $rating = SupplierRating::create([
            'supplier_id' => $supplier->id,
            'user_id' => auth()->id(),
            'penyetoran_id' => $request->penyetoran_id,
            'rating' => $request->rating,
            'ulasan' => $request->ulasan,
        ]);
        return response()->json([
            'message' => 'Rating berhasil disimpan',
            'data' => [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'ulasan' => $rating->ulasan,
            ],
        ], 201);
    }
}
