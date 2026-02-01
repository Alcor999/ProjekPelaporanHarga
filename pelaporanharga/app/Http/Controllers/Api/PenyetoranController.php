<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penyetoran;
use App\Models\Supplier;
use App\Models\SupplierHarga;
use Illuminate\Http\Request;

class PenyetoranController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->penyetoran()->with(['supplier', 'komoditas']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $penyetoran = $query->latest('tanggal')->paginate(15);
        $data = $penyetoran->through(fn($p) => [
            'id' => $p->id,
            'supplier' => ['id' => $p->supplier->id, 'name' => $p->supplier->name],
            'komoditas' => ['id' => $p->komoditas->id, 'name' => $p->komoditas->name, 'grade' => $p->komoditas->grade],
            'jumlah' => (float) $p->jumlah,
            'satuan' => $p->satuan,
            'harga_per_satuan' => (float) $p->harga_per_satuan,
            'total_nilai' => (float) $p->total_nilai,
            'tanggal' => $p->tanggal->format('Y-m-d'),
            'status' => $p->status,
            'catatan' => $p->catatan,
        ]);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'komoditas_id' => 'required|exists:komoditas,id',
            'jumlah' => 'required|numeric|min:0.01',
            'satuan' => 'nullable|string|max:20',
            'tanggal' => 'required|date',
            'catatan' => 'nullable|string',
        ]);
        $supplier = Supplier::findOrFail($request->supplier_id);
        if (!$supplier->is_active) {
            return response()->json(['message' => 'Supplier tidak tersedia'], 422);
        }
        $hargaRow = SupplierHarga::where('supplier_id', $supplier->id)->where('komoditas_id', $request->komoditas_id)->first();
        if (!$hargaRow) {
            return response()->json(['message' => 'Supplier tidak menerima komoditas ini'], 422);
        }
        $hargaPerSatuan = (float) $hargaRow->harga_per_satuan;
        $satuan = $request->get('satuan', $hargaRow->satuan);
        $jumlah = (float) $request->jumlah;
        $totalNilai = $hargaPerSatuan * $jumlah;
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
        return response()->json([
            'message' => 'Penyetoran berhasil dicatat',
            'data' => [
                'id' => $penyetoran->id,
                'supplier' => ['id' => $supplier->id, 'name' => $supplier->name],
                'komoditas' => ['id' => $penyetoran->komoditas->id, 'name' => $penyetoran->komoditas->name],
                'jumlah' => $penyetoran->jumlah,
                'satuan' => $penyetoran->satuan,
                'harga_per_satuan' => $penyetoran->harga_per_satuan,
                'total_nilai' => $penyetoran->total_nilai,
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
