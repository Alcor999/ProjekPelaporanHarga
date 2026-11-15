<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\SupplierHarga;
use App\Models\SupplierJadwal;
use App\Models\Komoditas;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $wilayah = Wilayah::first();
        $komoditas = Komoditas::take(3)->get();

        if ($komoditas->isEmpty()) {
            return;
        }

        $supplier1 = Supplier::firstOrCreate(
            ['name' => 'Supplier Padi Sejahtera'],
            [
                'phone' => '081234567890',
                'address' => 'Jl. Raya Ngawi No. 100',
                'wilayah_id' => $wilayah?->id,
                'description' => 'Menerima hasil panen padi dan komoditas pertanian',
                'is_active' => true,
            ]
        );

        foreach ($komoditas as $idx => $k) {
            SupplierHarga::firstOrCreate(
                [
                    'supplier_id' => $supplier1->id,
                    'komoditas_id' => $k->id,
                ],
                [
                    'harga_per_satuan' => 9000 + ($idx * 1000),
                    'satuan' => 'kg',
                ]
            );
        }

        for ($hari = 1; $hari <= 5; $hari++) {
            SupplierJadwal::firstOrCreate(
                ['supplier_id' => $supplier1->id, 'hari' => $hari],
                ['jam_buka' => '08:00', 'jam_tutup' => '17:00', 'is_buka' => true]
            );
        }
        SupplierJadwal::firstOrCreate(
            ['supplier_id' => $supplier1->id, 'hari' => 6],
            ['jam_buka' => '08:00', 'jam_tutup' => '12:00', 'is_buka' => true]
        );
        SupplierJadwal::firstOrCreate(
            ['supplier_id' => $supplier1->id, 'hari' => 7],
            ['is_buka' => false]
        );

        $supplier2 = Supplier::firstOrCreate(
            ['name' => 'Koperasi Tani Maju'],
            [
                'phone' => '081234567891',
                'address' => 'Jl. Pertanian No. 50',
                'wilayah_id' => $wilayah?->id,
                'description' => 'Koperasi petani, harga bersaing',
                'is_active' => true,
            ]
        );

        foreach ($komoditas as $idx => $k) {
            SupplierHarga::firstOrCreate(
                [
                    'supplier_id' => $supplier2->id,
                    'komoditas_id' => $k->id,
                ],
                [
                    'harga_per_satuan' => 9500 + ($idx * 1000),
                    'satuan' => 'kg',
                ]
            );
        }

        for ($hari = 1; $hari <= 6; $hari++) {
            SupplierJadwal::firstOrCreate(
                ['supplier_id' => $supplier2->id, 'hari' => $hari],
                ['jam_buka' => '07:00', 'jam_tutup' => '16:00', 'is_buka' => true]
            );
        }
    }
}
