<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function index(Request $request)
    {
        $query = Wilayah::query();
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('kecamatan', 'like', "%{$s}%")
                    ->orWhere('desa', 'like', "%{$s}%")
                    ->orWhere('kabupaten', 'like', "%{$s}%")
                    ->orWhere('provinsi', 'like', "%{$s}%");
            });
        }
        $wilayah = $query->orderBy('provinsi')->orderBy('kabupaten')->orderBy('kecamatan')->paginate(15);
        return view('admin.wilayah.index', compact('wilayah'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kecamatan' => 'required|string|max:100',
            'desa' => 'nullable|string|max:100',
            'kabupaten' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',
        ]);
        $data = $request->only(['kecamatan', 'desa', 'kabupaten', 'provinsi']);
        $data['is_active'] = $request->has('is_active');
        Wilayah::create($data);
        return redirect()->route('admin.wilayah.index')->with('success', 'Wilayah berhasil ditambahkan');
    }

    public function update(Request $request, Wilayah $wilayah)
    {
        $request->validate([
            'kecamatan' => 'required|string|max:100',
            'desa' => 'nullable|string|max:100',
            'kabupaten' => 'required|string|max:100',
            'provinsi' => 'required|string|max:100',
            'is_active' => 'boolean',
        ]);
        $data = $request->only(['kecamatan', 'desa', 'kabupaten', 'provinsi']);
        $data['is_active'] = $request->has('is_active');
        $wilayah->update($data);
        return redirect()->route('admin.wilayah.index')->with('success', 'Wilayah berhasil diupdate');
    }

    public function destroy(Wilayah $wilayah)
    {
        if ($wilayah->laporanHarga()->exists() || $wilayah->suppliers()->exists()) {
            return redirect()->back()->with('error', 'Wilayah tidak dapat dihapus karena masih digunakan oleh laporan atau supplier.');
        }
        $wilayah->delete();
        return redirect()->route('admin.wilayah.index')->with('success', 'Wilayah berhasil dihapus');
    }
}
