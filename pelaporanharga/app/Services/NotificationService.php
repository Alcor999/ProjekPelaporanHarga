<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Penyetoran;
use App\Models\User;
use App\Models\LaporanHarga;

class NotificationService
{
    public static function notifyLaporanApproved(LaporanHarga $laporan): void
    {
        $laporan->loadMissing('komoditas');
        $namaKomoditas = $laporan->komoditas?->name ?? 'Komoditas';
        $grade = $laporan->komoditas?->grade ? " ({$laporan->komoditas->grade})" : '';

        Notification::create([
            'user_id' => $laporan->user_id,
            'type' => 'laporan_approved',
            'title' => 'Laporan Diterima',
            'message' => "Laporan harga {$namaKomoditas}{$grade} senilai Rp " . number_format((float) $laporan->harga, 0, ',', '.') . " telah disetujui.",
            'related_type' => 'laporan_harga',
            'related_id' => $laporan->id,
        ]);
    }

    public static function notifyLaporanRejected(LaporanHarga $laporan, string $reason = ''): void
    {
        $laporan->loadMissing('komoditas');
        $namaKomoditas = $laporan->komoditas?->name ?? 'Komoditas';

        Notification::create([
            'user_id' => $laporan->user_id,
            'type' => 'laporan_rejected',
            'title' => 'Laporan Ditolak',
            'message' => "Laporan harga {$namaKomoditas} ditolak. " . ($reason ? "Alasan: {$reason}" : ''),
            'related_type' => 'laporan_harga',
            'related_id' => $laporan->id,
        ]);
    }

    public static function notifyPenyetoranStatusUpdated(Penyetoran $penyetoran): void
    {
        $penyetoran->loadMissing(['supplier', 'komoditas']);
        $namaSupplier = $penyetoran->supplier?->name ?? 'Supplier';
        $namaKomoditas = $penyetoran->komoditas?->name ?? 'Komoditas';
        $isCompleted = $penyetoran->status === 'completed';

        Notification::create([
            'user_id' => $penyetoran->user_id,
            'type' => 'penyetoran_' . $penyetoran->status,
            'title' => $isCompleted ? 'Penyetoran Selesai' : 'Penyetoran Dibatalkan',
            'message' => $isCompleted
                ? "Penyetoran {$namaKomoditas} ke {$namaSupplier} telah selesai diproses. Anda dapat memberikan ulasan supplier."
                : "Penyetoran {$namaKomoditas} ke {$namaSupplier} dibatalkan oleh admin.",
            'related_type' => 'penyetoran',
            'related_id' => $penyetoran->id,
        ]);
    }

    public static function notifyAccountApproved(User $user): void
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => 'account_approved',
            'title' => 'Akun Aktif',
            'message' => 'Akun Anda telah diaktifkan oleh admin. Sekarang Anda dapat login dan melaporkan harga komoditas.',
        ]);
    }

    public static function notifyBroadcast(User $user, string $title, string $message): void
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => 'broadcast',
            'title' => $title,
            'message' => $message,
        ]);
    }
}
