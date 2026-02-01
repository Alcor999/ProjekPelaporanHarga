<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierJadwal extends Model
{
    protected $table = 'supplier_jadwal';

    protected $fillable = [
        'supplier_id',
        'hari',
        'jam_buka',
        'jam_tutup',
        'is_buka',
        'catatan',
    ];

    protected $casts = [
        'is_buka' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public static function namaHari()
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
    }

    public function getNamaHariAttribute()
    {
        return self::namaHari()[$this->hari] ?? 'Hari ' . $this->hari;
    }

    public function getJamRangeAttribute()
    {
        if (!$this->is_buka) {
            return 'Tutup';
        }
        if ($this->jam_buka && $this->jam_tutup) {
            return substr($this->jam_buka, 0, 5) . ' - ' . substr($this->jam_tutup, 0, 5);
        }
        return '-';
    }
}
