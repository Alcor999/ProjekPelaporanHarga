<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'phone',
        'address',
        'wilayah_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function harga()
    {
        return $this->hasMany(SupplierHarga::class);
    }

    public function jadwal()
    {
        return $this->hasMany(SupplierJadwal::class)->orderBy('hari');
    }

    public function penyetoran()
    {
        return $this->hasMany(Penyetoran::class);
    }

    public function ratings()
    {
        return $this->hasMany(SupplierRating::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function avgRating()
    {
        return round($this->ratings()->avg('rating') ?? 0, 1);
    }

    public function ratingsCount()
    {
        return $this->ratings()->count();
    }
}
