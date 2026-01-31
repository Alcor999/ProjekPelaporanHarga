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
            [
                'name' => 'IR64',
                'grade' => 'Premium',
                'description' => 'Varietas padi IR64 grade premium',
            ],
            [
                'name' => 'IR64',
                'grade' => 'Medium',
                'description' => 'Varietas padi IR64 grade medium',
            ],
            [
                'name' => 'Ciherang',
                'grade' => 'Premium',
                'description' => 'Varietas padi Ciherang grade premium',
            ],
            [
                'name' => 'Ciherang',
                'grade' => 'Medium',
                'description' => 'Varietas padi Ciherang grade medium',
            ],
            [
                'name' => 'Inpari 30',
                'grade' => 'Premium',
                'description' => 'Varietas padi Inpari 30 grade premium',
            ],
            [
                'name' => 'Inpari 30',
                'grade' => 'Medium',
                'description' => 'Varietas padi Inpari 30 grade medium',
            ],
        ];

        foreach ($komoditasData as $data) {
            Komoditas::create($data);
        }
    }
}
