<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penyetoran;
use App\Models\Supplier;
use App\Models\SupplierHarga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenyetoranController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()
            ->penyetoran()
            ->with(['supplier', 'komoditas'])
            ->withExists('rating');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $penyetoran = $query->latest('tanggal')->latest('id')->paginate(15);
        $data = $penyetoran->through(fn($p) => [
            'id' => $p->id,
            'supplier' => [
                'id' => $p->supplier?->id,
                'name' => $p->supplier?->name ?? '-',
            ],
            'komoditas' => [
                'id' => $p->komoditas?->id,
                'name' => $p->komoditas?->name ?? '-',
                'grade' => $p->komoditas?->grade,
            ],
            'jumlah' => (float) $p->jumlah,
            'satuan' => $p->satuan,
            'harga_per_satuan' => (float) $p->harga_per_satuan,
            'total_nilai' => (float) $p->total_nilai,
            'tanggal' => $p->tanggal->format('Y-m-d'),
            'status' => $p->status,
            'has_rating' => (bool) $p->rating_exists,
            'catatan' => $p->catatan,
        ]);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'komoditas_id' => 'required|exists:komoditas,id',
            'jumlah' => 'required|numeric|gt:0|max:999999999999.99',
            'tanggal' => 'required|date',
            'catatan' => 'nullable|string|max:500',
        ]);

        $supplier = Supplier::findOrFail($request->supplier_id);
        if (!$supplier->is_active) {
            return response()->json(['message' => 'Supplier tidak tersedia'], 422);
        }

        $result = DB::transaction(function () use ($request, $supplier) {
            $hargaRow = SupplierHarga::where('supplier_id', $supplier->id)
                ->where('komoditas_id', $request->komoditas_id)
                ->lockForUpdate()
                ->first();

            if (!$hargaRow) {
                return ['error' => 'Supplier tidak menerima komoditas ini'];
            }

            $hargaPerSatuan = (float) $hargaRow->harga_per_satuan;
            if ($hargaPerSatuan <= 0) {
                return ['error' => 'Harga komoditas supplier belum valid'];
            }

            // Kunci satuan dan harga sesuai data resmi supplier agar payload client tidak dapat mengubah unit.
            $satuan = $hargaRow->satuan;
            $jumlah = (float) $request->jumlah;
            $totalNilai = round($hargaPerSatuan * $jumlah, 2);

            $penyetoran = Penyetoran::create([
                'user_id' => auth()->id(),
                'supplier_id' => $supplier->id,
                'komoditas_id' => $request->komoditas_id,
                'jumlah' => $jumlah,
                'satuan' => $satuan,
                'harga_per_satuan' => $hargaPerSatuan,
                'total_nilai' => $totalNilai,
                'tanggal' => $request->tanggal,
                'status' => 'pending',
                'catatan' => $request->catatan,
            ]);

            return ['penyetoran' => $penyetoran];
        });

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 422);
        }

        $penyetoran = $result['penyetoran']->load('komoditas');

        return response()->json([
            'success' => true,
            'message' => 'Penyetoran berhasil dicatat',
            'data' => [
                'id' => $penyetoran->id,
                'supplier' => ['id' => $supplier->id, 'name' => $supplier->name],
                'komoditas' => [
                    'id' => $penyetoran->komoditas?->id,
                    'name' => $penyetoran->komoditas?->name ?? '-',
                ],
                'jumlah' => (float) $penyetoran->jumlah,
                'satuan' => $penyetoran->satuan,
                'harga_per_satuan' => (float) $penyetoran->harga_per_satuan,
                'total_nilai' => (float) $penyetoran->total_nilai,
                'tanggal' => $penyetoran->tanggal->format('Y-m-d'),
                'status' => $penyetoran->status,
            ],
        ], 201);
    }

    public function show(Penyetoran $penyetoran)
    {
        if ($penyetoran->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $penyetoran->load(['supplier', 'komoditas']);
        return response()->json([
            'id' => $penyetoran->id,
            'supplier' => ['id' => $penyetoran->supplier->id, 'name' => $penyetoran->supplier->name, 'phone' => $penyetoran->supplier->phone],
            'komoditas' => ['id' => $penyetoran->komoditas->id, 'name' => $penyetoran->komoditas->name, 'grade' => $penyetoran->komoditas->grade],
            'jumlah' => (float) $penyetoran->jumlah,
            'satuan' => $penyetoran->satuan,
            'harga_per_satuan' => (float) $penyetoran->harga_per_satuan,
            'total_nilai' => (float) $penyetoran->total_nilai,
            'tanggal' => $penyetoran->tanggal->format('Y-m-d'),
            'status' => $penyetoran->status,
            'catatan' => $penyetoran->catatan,
            'created_at' => $penyetoran->created_at->toIso8601String(),
        ]);
    }
}
