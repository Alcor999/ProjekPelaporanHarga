<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Komoditas extends Model
{
    protected $fillable = [
        'kategori',
        'name',
        'grade',
        'description',
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

    public function supplierHarga()
    {
        return $this->hasMany(SupplierHarga::class);
    }

    public function penyetoran()
    {
        return $this->hasMany(Penyetoran::class);
    }

    /**
     * Scope for active komoditas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
