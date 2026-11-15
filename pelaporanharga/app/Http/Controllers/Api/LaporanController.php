<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarga;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LaporanController extends Controller
{
    /**
     * Get all laporan for authenticated user (with filters)
     */
    public function index(Request $request)
    {
        $query = $request->user()
            ->laporanHarga()
            ->with(['wilayah', 'komoditas']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        if ($request->filled('komoditas_id')) {
            $query->where('komoditas_id', $request->komoditas_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('komoditas', function ($qk) use ($search) {
                    $qk->where('name', 'like', "%{$search}%")
                        ->orWhere('grade', 'like', "%{$search}%");
                })->orWhere('catatan', 'like', "%{$search}%");
            });
        }
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_laporan', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_laporan', '<=', $request->tanggal_sampai);
        }

        $laporan = $query->orderBy('tanggal_laporan', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $laporan
        ]);
    }

    /**
     * Create new laporan
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wilayah_id' => 'required|exists:wilayah,id',
            'komoditas_id' => 'required|exists:komoditas,id',
            'harga' => 'required|numeric|gt:0|max:999999999999.99',
            'satuan' => 'required|string|max:10',
            'tanggal_panen' => 'required|date|before_or_equal:today',
            'catatan' => 'nullable|string|max:500',
            'foto' => 'nullable|string|max:3000000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $fotoPath = $request->filled('foto')
                ? $this->saveFotoFromBase64($request->foto)
                : null;
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }

        $laporan = LaporanHarga::create([
            'user_id' => $request->user()->id,
            'wilayah_id' => $request->wilayah_id,
            'komoditas_id' => $request->komoditas_id,
            'harga' => $request->harga,
            'satuan' => $request->satuan,
            'tanggal_panen' => $request->tanggal_panen,
            'tanggal_laporan' => now()->toDateString(),
            'catatan' => $request->catatan,
            'foto_path' => $fotoPath,
            'status' => 'pending',
        ]);

        $laporan->load(['wilayah', 'komoditas']);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dibuat',
            'data' => $laporan
        ], 201);
    }

    /**
     * Get specific laporan
     */
    public function show(Request $request, LaporanHarga $laporan)
    {
        if ($laporan->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $laporan->load(['wilayah', 'komoditas']);

        return response()->json([
            'success' => true,
            'data' => $laporan
        ]);
    }

    /**
     * Update laporan (only if status is pending)
     */
    public function update(Request $request, LaporanHarga $laporan)
    {
        if ($laporan->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($laporan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Laporan sudah diverifikasi, tidak dapat diubah'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'wilayah_id' => 'sometimes|exists:wilayah,id',
            'komoditas_id' => 'sometimes|exists:komoditas,id',
            'harga' => 'sometimes|numeric|gt:0|max:999999999999.99',
            'satuan' => 'sometimes|string|max:10',
            'tanggal_panen' => 'sometimes|date|before_or_equal:today',
            'catatan' => 'nullable|string|max:500',
            'foto' => 'nullable|string|max:3000000',
            'remove_foto' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['wilayah_id', 'komoditas_id', 'harga', 'satuan', 'tanggal_panen', 'catatan']);
        $oldFotoToDelete = null;

        if ($request->filled('foto')) {
            try {
                // Simpan foto baru terlebih dahulu agar kegagalan validasi tidak menghapus foto lama.
                $newFotoPath = $this->saveFotoFromBase64($request->foto);
            } catch (ValidationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($laporan->foto_path) {
                $oldFotoToDelete = $laporan->foto_path;
            }
            $data['foto_path'] = $newFotoPath;
        } elseif ($request->boolean('remove_foto')) {
            if ($laporan->foto_path) {
                $oldFotoToDelete = $laporan->foto_path;
            }
            $data['foto_path'] = null;
        }

        $laporan->update($data);

        if ($oldFotoToDelete) {
            Storage::disk('public')->delete($oldFotoToDelete);
        }

        $laporan->load(['wilayah', 'komoditas']);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diupdate',
            'data' => $laporan
        ]);
    }

    /**
     * Delete laporan (only if status is pending)
     */
    public function destroy(Request $request, LaporanHarga $laporan)
    {
        if ($laporan->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($laporan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya laporan yang belum diverifikasi yang dapat dihapus'
            ], 422);
        }

        if ($laporan->foto_path) {
            Storage::disk('public')->delete($laporan->foto_path);
        }

        $laporan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dihapus'
        ]);
    }

    private function saveFotoFromBase64(?string $base64): ?string
    {
        if ($base64 === null || trim($base64) === '') {
            return null;
        }

        $allowedHeaderExts = ['jpeg', 'jpg', 'png', 'webp'];
        if (preg_match('/^data:image\/([a-zA-Z0-9.+-]+);base64,/', $base64, $matches)) {
            $declared = strtolower($matches[1]);
            if (!in_array($declared, $allowedHeaderExts, true)) {
                throw ValidationException::withMessages([
                    'foto' => ['Format foto harus JPG, PNG, atau WEBP.'],
                ]);
            }
            $base64 = substr($base64, strpos($base64, ',') + 1);
        }

        $data = base64_decode($base64, true);
        if ($data === false || $data === '') {
            throw ValidationException::withMessages([
                'foto' => ['Data foto base64 tidak valid.'],
            ]);
        }

        if (strlen($data) > 2 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'foto' => ['Ukuran foto maksimal 2 MB.'],
            ]);
        }

        $imageInfo = @getimagesizefromstring($data);
        if ($imageInfo === false || empty($imageInfo['mime'])) {
            throw ValidationException::withMessages([
                'foto' => ['File foto tidak valid atau bukan gambar yang didukung.'],
            ]);
        }

        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        $extension = $mimeToExt[$imageInfo['mime']] ?? null;
        if ($extension === null) {
            throw ValidationException::withMessages([
                'foto' => ['Format foto harus JPG, PNG, atau WEBP.'],
            ]);
        }

        $filename = 'laporan/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($filename, $data);

        return $filename;
    }
}
