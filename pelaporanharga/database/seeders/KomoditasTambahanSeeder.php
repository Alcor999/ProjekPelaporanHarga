<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Komoditas;

class KomoditasTambahanSeeder extends Seeder
{
    public function run(): void
    {
        $baru = [
            ['kategori' => 'jagung', 'name' => 'Jagung Hibrida', 'grade' => 'Premium', 'description' => 'Jagung hibrida grade premium'],
            ['kategori' => 'jagung', 'name' => 'Jagung Lokal', 'grade' => 'Medium', 'description' => 'Jagung lokal grade medium'],
            ['kategori' => 'kedelai', 'name' => 'Kedelai Hitam', 'grade' => null, 'description' => 'Kedelai hitam'],
            ['kategori' => 'kedelai', 'name' => 'Kedelai Putih', 'grade' => null, 'description' => 'Kedelai putih'],
            ['kategori' => 'cabai', 'name' => 'Cabai Merah Keriting', 'grade' => null, 'description' => 'Cabai merah keriting'],
            ['kategori' => 'cabai', 'name' => 'Cabai Rawit', 'grade' => null, 'description' => 'Cabai rawit'],
        ];

        foreach ($baru as $d) {
            if (!Komoditas::where('name', $d['name'])->where('grade', $d['grade'])->exists()) {
                Komoditas::create(array_merge($d, ['is_active' => true]));
            }
        }

        Komoditas::whereNull('kategori')->orWhere('kategori', '')->update(['kategori' => 'padi']);
    }
}
