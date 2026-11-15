<?php

namespace Tests\Feature;

use App\Models\Komoditas;
use App\Models\LaporanHarga;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Wilayah;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationAndSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_migration_and_double_seed_are_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(Schema::hasTable('wilayah'));
        $this->assertTrue(Schema::hasTable('laporan_harga'));
        $this->assertTrue(Schema::hasTable('supplier_rating'));

        $this->assertSame(1, User::where('email', 'admin@laporanharga.com')->count());
        $this->assertSame(1, User::where('email', 'petani@laporanharga.com')->count());
        $this->assertSame(5, Wilayah::count());
        $this->assertSame(12, Komoditas::count());
        $this->assertSame(2, Supplier::count());
    }

    public function test_production_seed_omits_demo_accounts_and_demo_suppliers(): void
    {
        $originalEnv = $this->app['env'];
        $originalPassword = $_ENV['ADMIN_INITIAL_PASSWORD'] ?? null;

        try {
            $this->app['env'] = 'production';
            putenv('ADMIN_INITIAL_PASSWORD=SandiProduksiKuat123!');
            $_ENV['ADMIN_INITIAL_PASSWORD'] = 'SandiProduksiKuat123!';

            $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])
                ->assertSuccessful();

            $admin = User::where('email', 'admin@laporanharga.com')->first();
            $this->assertNotNull($admin);
            $this->assertTrue(Hash::check('SandiProduksiKuat123!', $admin->password));
            $this->assertSame(0, User::where('email', 'petani@laporanharga.com')->count());
            $this->assertSame(0, Supplier::count());
        } finally {
            $this->app['env'] = $originalEnv;
            if ($originalPassword === null) {
                putenv('ADMIN_INITIAL_PASSWORD');
                unset($_ENV['ADMIN_INITIAL_PASSWORD']);
            } else {
                putenv("ADMIN_INITIAL_PASSWORD={$originalPassword}");
                $_ENV['ADMIN_INITIAL_PASSWORD'] = $originalPassword;
            }
        }
    }

    public function test_production_seed_throws_when_admin_password_missing_for_new_admin(): void
    {
        $originalEnv = $this->app['env'];
        $originalPassword = $_ENV['ADMIN_INITIAL_PASSWORD'] ?? null;

        try {
            $this->app['env'] = 'production';
            putenv('ADMIN_INITIAL_PASSWORD');
            unset($_ENV['ADMIN_INITIAL_PASSWORD']);

            $this->expectException(\RuntimeException::class);
            $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);
        } finally {
            $this->app['env'] = $originalEnv;
            if ($originalPassword !== null) {
                putenv("ADMIN_INITIAL_PASSWORD={$originalPassword}");
                $_ENV['ADMIN_INITIAL_PASSWORD'] = $originalPassword;
            }
        }
    }

    public function test_api_store_sets_tanggal_laporan_explicitly_using_app_timezone(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertSame('Asia/Jakarta', config('app.timezone'));

        Carbon::setTestNow(Carbon::create(2026, 11, 15, 1, 30, 0, 'Asia/Jakarta'));

        try {
            $petani = User::where('email', 'petani@laporanharga.com')->firstOrFail();
            $wilayah = Wilayah::firstOrFail();
            $komoditas = Komoditas::firstOrFail();

            $response = $this->actingAs($petani, 'sanctum')->postJson('/api/petani/laporan', [
                'wilayah_id' => $wilayah->id,
                'komoditas_id' => $komoditas->id,
                'harga' => 125000000.50,
                'satuan' => 'ton',
                'tanggal_panen' => '2026-11-15',
            ]);

            $response->assertCreated();
            $laporan = LaporanHarga::firstOrFail();
            $this->assertSame('2026-11-15', $laporan->tanggal_laporan->toDateString());
            $this->assertSame('125000000.50', $laporan->harga);
        } finally {
            Carbon::setTestNow();
        }
    }
}
