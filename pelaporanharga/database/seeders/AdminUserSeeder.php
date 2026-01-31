<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@laporanharga.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'phone' => '081234567890',
            'address' => 'Jl. Admin No. 1, Jakarta',
        ]);

        User::create([
            'name' => 'Petani Demo',
            'email' => 'petani@laporanharga.com',
            'password' => Hash::make('password'),
            'role' => 'petani',
            'status' => 'active',
            'phone' => '081234567891',
            'address' => 'Jl. Petani No. 1, Bogor',
        ]);
    }
}
