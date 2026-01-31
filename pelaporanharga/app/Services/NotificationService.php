<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\LaporanHarga;

class NotificationService
{
    public static function notifyLaporanApproved(LaporanHarga $laporan): void
    {
        Notification::create([
            'user_id' => $laporan->user_id,
            'type' => 'laporan_approved',
            'title' => 'Laporan Diterima',
            'message' => "Laporan harga {$laporan->komoditas->name} ({$laporan->komoditas->grade}) senilai Rp " . number_format($laporan->harga, 0, ',', '.') . " telah disetujui.",
            'related_type' => 'laporan_harga',
            'related_id' => $laporan->id,
        ]);
    }

    public static function notifyLaporanRejected(LaporanHarga $laporan, string $reason = ''): void
    {
        Notification::create([
            'user_id' => $laporan->user_id,
            'type' => 'laporan_rejected',
            'title' => 'Laporan Ditolak',
            'message' => "Laporan harga {$laporan->komoditas->name} ditolak. " . ($reason ? "Alasan: {$reason}" : ''),
            'related_type' => 'laporan_harga',
            'related_id' => $laporan->id,
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
