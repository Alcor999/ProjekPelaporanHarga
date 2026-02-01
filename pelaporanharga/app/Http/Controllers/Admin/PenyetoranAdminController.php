<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penyetoran;
use Illuminate\Http\Request;

class PenyetoranAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Penyetoran::with(['user', 'supplier', 'komoditas']);
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }
        $penyetoran = $query->latest('tanggal')->paginate(15);
        return view('admin.penyetoran.index', compact('penyetoran'));
    }
}
