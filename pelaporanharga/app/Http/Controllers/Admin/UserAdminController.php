<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    /**
     * List petani (pending & active)
     */
    public function index(Request $request)
    {
        $status = $request->get('status');

        $query = User::where('role', 'petani');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $petani = $query->orderBy('status')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('petani', 'status', 'search'));
    }

    /**
     * Approve petani (ubah status ke active)
     */
    public function approve(User $user)
    {
        if (!$user->isPetani()) {
            return redirect()->back()->with('error', 'Hanya akun petani yang dapat di-approve.');
        }

        $user->update(['status' => 'active']);

        return redirect()->back()->with('success', 'Akun petani berhasil di-approve.');
    }

    /**
     * Block / nonaktifkan petani
     */
    public function block(User $user)
    {
        if (!$user->isPetani()) {
            return redirect()->back()->with('error', 'Hanya akun petani yang dapat diblokir.');
        }

        $user->update(['status' => 'blocked']);

        return redirect()->back()->with('success', 'Akun petani berhasil dinonaktifkan.');
    }
}
