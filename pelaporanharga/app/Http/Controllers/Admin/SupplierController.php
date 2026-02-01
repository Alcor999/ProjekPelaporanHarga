<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierHarga;
use App\Models\SupplierJadwal;
use App\Models\Wilayah;
use App\Models\Komoditas;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::with(['wilayah', 'harga.komoditas', 'jadwal'])->withCount('ratings');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('address', 'like', "%{$s}%");
            });
        }
        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }
        $suppliers = $query->orderBy('name')->paginate(15);
        $wilayah = Wilayah::active()->orderBy('kabupaten')->get();
        $komoditas = Komoditas::active()->orderBy('name')->get();
        return view('admin.supplier.index', compact('suppliers', 'wilayah', 'komoditas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'wilayah_id' => 'nullable|exists:wilayah,id',
            'description' => 'nullable|string',
        ]);
        $data = $request->only(['name', 'phone', 'address', 'wilayah_id', 'description']);
        $data['is_active'] = $request->has('is_active');
        Supplier::create($data);
        return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil ditambahkan');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'wilayah_id' => 'nullable|exists:wilayah,id',
            'description' => 'nullable|string',
        ]);
        $data = $request->only(['name', 'phone', 'address', 'wilayah_id', 'description']);
        $data['is_active'] = $request->has('is_active');
        $supplier->update($data);
        return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil diupdate');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->penyetoran()->exists()) {
            return redirect()->back()->with('error', 'Supplier tidak dapat dihapus karena memiliki data penyetoran');
        }
        $supplier->harga()->delete();
        $supplier->jadwal()->delete();
        $supplier->ratings()->delete();
        $supplier->delete();
        return redirect()->route('admin.supplier.index')->with('success', 'Supplier berhasil dihapus');
    }

    public function storeHarga(Request $request, Supplier $supplier)
    {
        $request->validate([
            'komoditas_id' => 'required|exists:komoditas,id',
            'harga_per_satuan' => 'required|numeric|min:0',
            'satuan' => 'nullable|string|max:20',
        ]);
        $data = $request->only(['komoditas_id', 'harga_per_satuan']);
        $data['satuan'] = $request->get('satuan', 'kg');
        $supplier->harga()->updateOrCreate(
            ['komoditas_id' => $data['komoditas_id']],
            ['harga_per_satuan' => $data['harga_per_satuan'], 'satuan' => $data['satuan']]
        );
        return redirect()->back()->with('success', 'Harga berhasil disimpan');
    }

    public function destroyHarga(Supplier $supplier, SupplierHarga $harga)
    {
        if ($harga->supplier_id !== $supplier->id) {
            abort(404);
        }
        $harga->delete();
        return redirect()->back()->with('success', 'Harga berhasil dihapus');
    }

    public function storeJadwal(Request $request, Supplier $supplier)
    {
        $request->validate([
            'hari' => 'required|integer|between:1,7',
            'jam_buka' => 'nullable|date_format:H:i',
            'jam_tutup' => 'nullable|date_format:H:i|after_or_equal:jam_buka',
            'is_buka' => 'boolean',
            'catatan' => 'nullable|string|max:255',
        ]);
        $data = $request->only(['hari', 'jam_buka', 'jam_tutup', 'catatan']);
        $data['is_buka'] = $request->has('is_buka') ? true : ($request->get('is_buka') === '1');
        $supplier->jadwal()->updateOrCreate(
            ['hari' => $data['hari']],
            $data
        );
        return redirect()->back()->with('success', 'Jadwal berhasil disimpan');
    }

    public function destroyJadwal(Supplier $supplier, SupplierJadwal $jadwal)
    {
        if ($jadwal->supplier_id !== $supplier->id) {
            abort(404);
        }
        $jadwal->delete();
        return redirect()->back()->with('success', 'Jadwal berhasil dihapus');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['wilayah', 'harga.komoditas', 'jadwal', 'ratings.user', 'penyetoran.user', 'penyetoran.komoditas']);
        $komoditas = Komoditas::active()->orderBy('name')->get();
        $wilayah = Wilayah::active()->orderBy('kabupaten')->get();
        return view('admin.supplier.show', compact('supplier', 'komoditas', 'wilayah'));
    }
}
