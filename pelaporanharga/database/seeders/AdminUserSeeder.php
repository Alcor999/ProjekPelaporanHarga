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
        $adminEmail = 'admin@laporanharga.com';

        if (! User::where('email', $adminEmail)->exists()) {
            $password = env('ADMIN_INITIAL_PASSWORD');

            if (empty($password)) {
                if (! app()->environment(['local', 'testing'])) {
                    throw new \RuntimeException('ADMIN_INITIAL_PASSWORD wajib diatur saat seeding di environment production.');
                }

                $password = 'password';
            }

            User::create([
                'name' => 'Administrator',
                'email' => $adminEmail,
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '081234567890',
                'address' => 'Jl. Admin No. 1, Jakarta',
            ]);
        }

        if (app()->environment(['local', 'testing'])) {
            User::firstOrCreate(
                ['email' => 'petani@laporanharga.com'],
                [
                    'name' => 'Petani Demo',
                    'password' => Hash::make('password'),
                    'role' => 'petani',
                    'status' => 'active',
                    'phone' => '081234567891',
                    'address' => 'Jl. Petani No. 1, Bogor',
                ]
            );
        }
    }
}
