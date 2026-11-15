<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if ($user->role !== 'petani') {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini tidak memiliki akses ke aplikasi petani.',
            ], 403);
        }

        $result = DB::transaction(function () use ($user) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($lockedUser->status === 'pending') {
                throw ValidationException::withMessages([
                    'email' => ['Akun Anda masih menunggu persetujuan admin.'],
                ]);
            }

            if ($lockedUser->status === 'blocked' || !$lockedUser->isActive()) {
                throw ValidationException::withMessages([
                    'email' => ['Akun Anda telah diblokir atau dinonaktifkan. Silakan hubungi admin.'],
                ]);
            }

            $lockedUser->tokens()->delete();
            $token = $lockedUser->createToken('api-token')->plainTextToken;

            return [$lockedUser, $token];
        });

        [$activeUser, $token] = $result;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => $activeUser,
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Logout user and revoke token
     */
    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    /**
     * Register petani baru (status pending, menunggu approval admin)
     */
    public function registerPetani(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'petani',
            'status' => 'pending',
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil. Akun Anda akan diperiksa dan di-approve oleh admin.',
            'data' => $user,
        ], 201);
    }
}
