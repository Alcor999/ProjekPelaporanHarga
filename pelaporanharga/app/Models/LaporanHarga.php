<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanHarga extends Model
{
    protected $table = 'laporan_harga';

    protected $fillable = [
        'user_id',
        'wilayah_id',
        'komoditas_id',
        'harga',
        'satuan',
        'tanggal_panen',
        'tanggal_laporan',
        'catatan',
        'status',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'tanggal_panen' => 'date',
        'tanggal_laporan' => 'date',
    ];

    /**
     * Relationship with user (petani)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with wilayah
     */
    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    /**
     * Relationship with komoditas
     */
    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class);
    }

    /**
     * Scope for approved laporan
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending laporan
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
