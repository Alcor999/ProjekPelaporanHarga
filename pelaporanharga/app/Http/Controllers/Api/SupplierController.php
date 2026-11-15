<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with(['wilayah', 'harga.komoditas', 'jadwal'])
            ->active()
            ->withCount('ratings')
            ->withAvg('ratings', 'rating');
        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        if ($request->filled('komoditas_id')) {
            $query->whereHas('harga', fn($q) => $q->where('komoditas_id', $request->komoditas_id));
        }
        $suppliers = $query->orderBy('name')->get();
        $data = $suppliers->map(function ($s) {
            $avgRating = round((float) ($s->ratings_avg_rating ?? 0), 1);
            return [
                'id' => $s->id,
                'name' => $s->name,
                'phone' => $s->phone,
                'address' => $s->address,
                'description' => $s->description,
                'wilayah' => $s->wilayah ? [
                    'id' => $s->wilayah->id,
                    'kabupaten' => $s->wilayah->kabupaten,
                    'kecamatan' => $s->wilayah->kecamatan,
                ] : null,
                'harga' => $s->harga->map(fn($h) => [
                    'komoditas_id' => $h->komoditas_id,
                    'komoditas' => ['id' => $h->komoditas->id, 'name' => $h->komoditas->name, 'grade' => $h->komoditas->grade],
                    'harga_per_satuan' => (float) $h->harga_per_satuan,
                    'satuan' => $h->satuan,
                ])->values(),
                'jadwal' => $s->jadwal->map(fn($j) => [
                    'hari' => $j->hari,
                    'nama_hari' => $j->nama_hari,
                    'jam_buka' => $j->jam_buka,
                    'jam_tutup' => $j->jam_tutup,
                    'is_buka' => $j->is_buka,
                    'jam_range' => $j->jam_range,
                    'catatan' => $j->catatan,
                ])->values(),
                'avg_rating' => $avgRating,
                'ratings_count' => $s->ratings_count,
            ];
        });
        return response()->json(['data' => $data]);
    }

    public function show(Supplier $supplier)
    {
        if (!$supplier->is_active) {
            return response()->json(['message' => 'Supplier tidak ditemukan'], 404);
        }
        $supplier->load(['wilayah', 'harga.komoditas', 'jadwal', 'ratings.user']);
        $avg = round((float) ($supplier->ratings->avg('rating') ?? 0), 1);
        return response()->json([
            'id' => $supplier->id,
            'name' => $supplier->name,
            'phone' => $supplier->phone,
            'address' => $supplier->address,
            'description' => $supplier->description,
            'wilayah' => $supplier->wilayah ? [
                'id' => $supplier->wilayah->id,
                'kabupaten' => $supplier->wilayah->kabupaten,
                'kecamatan' => $supplier->wilayah->kecamatan,
            ] : null,
            'harga' => $supplier->harga->map(fn($h) => [
                'komoditas_id' => $h->komoditas_id,
                'komoditas' => ['id' => $h->komoditas->id, 'name' => $h->komoditas->name, 'grade' => $h->komoditas->grade],
                'harga_per_satuan' => (float) $h->harga_per_satuan,
                'satuan' => $h->satuan,
            ])->values(),
            'jadwal' => $supplier->jadwal->map(fn($j) => [
                'hari' => $j->hari,
                'nama_hari' => $j->nama_hari,
                'jam_buka' => $j->jam_buka,
                'jam_tutup' => $j->jam_tutup,
                'is_buka' => $j->is_buka,
                'jam_range' => $j->jam_range,
                'catatan' => $j->catatan,
            ])->values(),
            'avg_rating' => $avg,
            'ratings_count' => $supplier->ratings->count(),
            'ratings' => $supplier->ratings->take(20)->map(fn($r) => [
                'id' => $r->id,
                'rating' => $r->rating,
                'ulasan' => $r->ulasan,
                'user' => ['name' => $r->user->name],
                'created_at' => $r->created_at->toIso8601String(),
            ])->values(),
        ]);
    }
}
