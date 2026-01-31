<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use Illuminate\Http\Request;

class KomoditasController extends Controller
{
    public function index(Request $request)
    {
        $query = Komoditas::query();
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('grade', 'like', "%{$s}%")
                    ->orWhere('kategori', 'like', "%{$s}%");
            });
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        $komoditas = $query->orderBy('kategori')->orderBy('name')->orderBy('grade')->paginate(15);
        return view('admin.komoditas.index', compact('komoditas'));
    }

    public function create()
    {
        return view('admin.komoditas.form', ['komoditas' => new Komoditas()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'grade' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        $data = $request->only(['kategori', 'name', 'grade', 'description']);
        $data['is_active'] = true;
        Komoditas::create($data);
        return redirect()->route('admin.komoditas.index')->with('success', 'Komoditas berhasil ditambahkan');
    }

    public function edit(Komoditas $komoditas)
    {
        return view('admin.komoditas.form', compact('komoditas'));
    }

    public function update(Request $request, Komoditas $komoditas)
    {
        $request->validate([
            'kategori' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'grade' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $data = $request->only(['kategori', 'name', 'grade', 'description']);
        $data['is_active'] = $request->has('is_active');
        $komoditas->update($data);
        return redirect()->route('admin.komoditas.index')->with('success', 'Komoditas berhasil diupdate');
    }

    public function destroy(Komoditas $komoditas)
    {
        if ($komoditas->laporanHarga()->exists()) {
            return redirect()->back()->with('error', 'Komoditas tidak dapat dihapus karena memiliki laporan');
        }
        $komoditas->delete();
        return redirect()->route('admin.komoditas.index')->with('success', 'Komoditas berhasil dihapus');
    }
}
