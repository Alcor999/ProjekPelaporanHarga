<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Wilayah;

class WilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wilayahData = [
            [
                'kecamatan' => 'Ngawi',
                'desa' => 'Ketanggi',
                'kabupaten' => 'Ngawi',
                'provinsi' => 'Jawa Timur',
            ],
            [
                'kecamatan' => 'Pangkur',
                'desa' => 'Pangkur',
                'kabupaten' => 'Ngawi',
                'provinsi' => 'Jawa Timur',
            ],
            [
                'kecamatan' => 'Paron',
                'desa' => 'Paron',
                'kabupaten' => 'Ngawi',
                'provinsi' => 'Jawa Timur',
            ],
            [
                'kecamatan' => 'Kedunggalar',
                'desa' => 'Kedunggalar',
                'kabupaten' => 'Ngawi',
                'provinsi' => 'Jawa Timur',
            ],
            [
                'kecamatan' => 'Karangjati',
                'desa' => 'Karangjati',
                'kabupaten' => 'Ngawi',
                'provinsi' => 'Jawa Timur',
            ],
        ];

        foreach ($wilayahData as $data) {
            Wilayah::create($data);
        }
    }
}
