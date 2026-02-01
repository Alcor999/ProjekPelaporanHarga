<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierHarga extends Model
{
    protected $table = 'supplier_harga';

    protected $fillable = [
        'supplier_id',
        'komoditas_id',
        'harga_per_satuan',
        'satuan',
    ];

    protected $casts = [
        'harga_per_satuan' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class);
    }
}
