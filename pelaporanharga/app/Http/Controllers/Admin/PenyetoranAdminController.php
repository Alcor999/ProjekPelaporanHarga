<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penyetoran;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $penyetoran = $query->latest('tanggal')->latest('id')->paginate(15);
        return view('admin.penyetoran.index', compact('penyetoran'));
    }

    public function updateStatus(Request $request, Penyetoran $penyetoran)
    {
        $request->validate([
            'status' => 'required|in:completed,cancelled',
        ]);

        $updated = DB::transaction(function () use ($request, $penyetoran) {
            $locked = Penyetoran::whereKey($penyetoran->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                return null;
            }

            $locked->update(['status' => $request->status]);
            return $locked;
        });

        if (!$updated) {
            return redirect()->back()->with('error', 'Penyetoran sudah diproses sebelumnya.');
        }

        NotificationService::notifyPenyetoranStatusUpdated($updated);

        return redirect()->back()->with(
            'success',
            $request->status === 'completed'
                ? 'Penyetoran ditandai selesai.'
                : 'Penyetoran dibatalkan.'
        );
    }
}
