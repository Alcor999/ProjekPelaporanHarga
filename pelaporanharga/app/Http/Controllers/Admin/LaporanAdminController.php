<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarga;
use App\Models\Wilayah;
use App\Models\Komoditas;
use Illuminate\Http\Request;

class LaporanAdminController extends Controller
{
    /**
     * List all laporan with filters
     */
    public function index(Request $request)
    {
        $query = LaporanHarga::with(['user', 'wilayah', 'komoditas']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }

        if ($request->filled('komoditas_id')) {
            $query->where('komoditas_id', $request->komoditas_id);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_laporan', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_laporan', '<=', $request->tanggal_sampai);
        }

        $laporan = $query->orderBy('tanggal_laporan', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        $wilayah = Wilayah::active()->get();
        $komoditas = Komoditas::active()->get();

        return view('admin.laporan.index', compact('laporan', 'wilayah', 'komoditas'));
    }

    /**
     * Show laporan detail
     */
    public function show(LaporanHarga $laporan)
    {
        $laporan->load(['user', 'wilayah', 'komoditas']);
        return view('admin.laporan.show', compact('laporan'));
    }

    /**
     * Approve laporan
     */
    public function approve(LaporanHarga $laporan)
    {
        if ($laporan->status !== 'pending') {
            return redirect()->back()->with('error', 'Laporan sudah diproses sebelumnya');
        }

        $laporan->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'Laporan berhasil diapprove');
    }

    /**
     * Reject laporan
     */
    public function reject(LaporanHarga $laporan)
    {
        if ($laporan->status !== 'pending') {
            return redirect()->back()->with('error', 'Laporan sudah diproses sebelumnya');
        }

        $laporan->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Laporan berhasil direject');
    }

    /**
     * Edit laporan (admin can modify)
     */
    public function edit(LaporanHarga $laporan)
    {
        $laporan->load(['user', 'wilayah', 'komoditas']);
        $wilayah = Wilayah::active()->get();
        $komoditas = Komoditas::active()->get();

        return view('admin.laporan.edit', compact('laporan', 'wilayah', 'komoditas'));
    }

    /**
     * Update laporan
     */
    public function update(Request $request, LaporanHarga $laporan)
    {
        $request->validate([
            'harga' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:10',
            'tanggal_panen' => 'required|date|before_or_equal:today',
            'catatan' => 'nullable|string|max:500',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $laporan->update($request->only([
            'harga', 'satuan', 'tanggal_panen', 'catatan', 'status'
        ]));

        return redirect()->route('admin.laporan.index')->with('success', 'Laporan berhasil diupdate');
    }
}
