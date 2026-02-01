<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penyetoran extends Model
{
    protected $table = 'penyetoran';

    protected $fillable = [
        'user_id',
        'supplier_id',
        'komoditas_id',
        'jumlah',
        'satuan',
        'harga_per_satuan',
        'total_nilai',
        'tanggal',
        'status',
        'catatan',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'harga_per_satuan' => 'decimal:2',
        'total_nilai' => 'decimal:2',
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class);
    }

    public function rating()
    {
        return $this->hasOne(SupplierRating::class);
    }
}
