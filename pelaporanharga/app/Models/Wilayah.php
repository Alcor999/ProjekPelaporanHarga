<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    protected $table = 'wilayah';

    protected $fillable = [
        'kecamatan',
        'desa',
        'kabupaten',
        'provinsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship with laporan harga
     */
    public function laporanHarga()
    {
        return $this->hasMany(LaporanHarga::class);
    }

    /**
     * Scope for active wilayah
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->desa,
            $this->kecamatan,
            $this->kabupaten,
            $this->provinsi,
        ]);

        return implode(', ', $parts);
    }
}
