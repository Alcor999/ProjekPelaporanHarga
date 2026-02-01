<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierRating extends Model
{
    protected $table = 'supplier_rating';

    protected $fillable = [
        'supplier_id',
        'user_id',
        'penyetoran_id',
        'rating',
        'ulasan',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function penyetoran()
    {
        return $this->belongsTo(Penyetoran::class);
    }
}
