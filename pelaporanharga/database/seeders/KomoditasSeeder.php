<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Komoditas;

class KomoditasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $komoditasData = [
            ['kategori' => 'padi', 'name' => 'IR64', 'grade' => 'Premium', 'description' => 'Varietas padi IR64 grade premium'],
            ['kategori' => 'padi', 'name' => 'IR64', 'grade' => 'Medium', 'description' => 'Varietas padi IR64 grade medium'],
            ['kategori' => 'padi', 'name' => 'Ciherang', 'grade' => 'Premium', 'description' => 'Varietas padi Ciherang grade premium'],
            ['kategori' => 'padi', 'name' => 'Ciherang', 'grade' => 'Medium', 'description' => 'Varietas padi Ciherang grade medium'],
            ['kategori' => 'padi', 'name' => 'Inpari 30', 'grade' => 'Premium', 'description' => 'Varietas padi Inpari 30 grade premium'],
            ['kategori' => 'padi', 'name' => 'Inpari 30', 'grade' => 'Medium', 'description' => 'Varietas padi Inpari 30 grade medium'],
            ['kategori' => 'jagung', 'name' => 'Jagung Hibrida', 'grade' => 'Premium', 'description' => 'Jagung hibrida grade premium'],
            ['kategori' => 'jagung', 'name' => 'Jagung Lokal', 'grade' => 'Medium', 'description' => 'Jagung lokal grade medium'],
            ['kategori' => 'kedelai', 'name' => 'Kedelai Hitam', 'grade' => null, 'description' => 'Kedelai hitam'],
            ['kategori' => 'kedelai', 'name' => 'Kedelai Putih', 'grade' => null, 'description' => 'Kedelai putih'],
            ['kategori' => 'cabai', 'name' => 'Cabai Merah Keriting', 'grade' => null, 'description' => 'Cabai merah keriting'],
            ['kategori' => 'cabai', 'name' => 'Cabai Rawit', 'grade' => null, 'description' => 'Cabai rawit'],
        ];

        foreach ($komoditasData as $data) {
            Komoditas::create($data);
        }
    }
}
