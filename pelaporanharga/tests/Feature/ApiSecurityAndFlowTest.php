<?php

namespace Tests\Feature;

use App\Models\Komoditas;
use App\Models\LaporanHarga;
use App\Models\Penyetoran;
use App\Models\Supplier;
use App\Models\SupplierHarga;
use App\Models\User;
use App\Models\Wilayah;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiSecurityAndFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_rate_limiter_blocks_sixth_attempt(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => 'petani@laporanharga.com',
                'password' => 'salah-password',
            ])->assertStatus(422);
        }

        $this->postJson('/api/login', [
            'email' => 'petani@laporanharga.com',
            'password' => 'salah-password',
        ])->assertStatus(429);
    }

    public function test_login_checks_password_before_role_and_differentiates_pending_and_blocked(): void
    {
        $this->postJson('/api/login', [
            'email' => 'admin@laporanharga.com',
            'password' => 'salah',
        ])->assertStatus(422);

        $this->postJson('/api/login', [
            'email' => 'admin@laporanharga.com',
            'password' => 'password',
        ])->assertStatus(403);

        $pending = User::create([
            'name' => 'Petani Pending',
            'email' => 'pending@example.com',
            'password' => Hash::make('password'),
            'role' => 'petani',
            'status' => 'pending',
        ]);

        $this->postJson('/api/login', [
            'email' => $pending->email,
            'password' => 'password',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Akun Anda masih menunggu persetujuan admin.');

        $blocked = User::create([
            'name' => 'Petani Blocked',
            'email' => 'blocked@example.com',
            'password' => Hash::make('password'),
            'role' => 'petani',
            'status' => 'blocked',
        ]);

        $this->postJson('/api/login', [
            'email' => $blocked->email,
            'password' => 'password',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Akun Anda telah diblokir atau dinonaktifkan. Silakan hubungi admin.');
    }

    public function test_blocking_petani_revokes_sanctum_tokens_immediately(): void
    {
        $admin = User::where('email', 'admin@laporanharga.com')->firstOrFail();
        $petani = User::where('email', 'petani@laporanharga.com')->firstOrFail();

        $loginResponse = $this->postJson('/api/login', [
            'email' => $petani->email,
            'password' => 'password',
        ])->assertOk();

        $token = $loginResponse->json('data.token');
        $this->assertNotEmpty($token);

        $this->getJson('/api/petani/laporan', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertOk();

        app('auth')->forgetGuards();

        $this->actingAs($admin, 'web')
            ->post(route('admin.users.petani.block', $petani))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, $petani->fresh()->tokens()->count());
        $this->assertSame('blocked', $petani->fresh()->status);

        app('auth')->forgetGuards();

        $this->getJson('/api/petani/laporan', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(401);
    }

    public function test_laporan_photo_upload_validation_and_safe_update_retention(): void
    {
        $petani = User::where('email', 'petani@laporanharga.com')->firstOrFail();
        $wilayah = Wilayah::firstOrFail();
        $komoditas = Komoditas::firstOrFail();

        // 1x1 transparent PNG
        $validPng = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
        $svgPayload = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        $fakePngPayload = 'data:image/png;base64,' . base64_encode('<?php echo "xss"; ?>');

        $this->actingAs($petani, 'sanctum')->postJson('/api/petani/laporan', [
            'wilayah_id' => $wilayah->id,
            'komoditas_id' => $komoditas->id,
            'harga' => 10000,
            'satuan' => 'kg',
            'tanggal_panen' => now()->toDateString(),
            'foto' => $svgPayload,
        ])->assertStatus(422)->assertJsonValidationErrors(['foto']);

        $createResponse = $this->actingAs($petani, 'sanctum')->postJson('/api/petani/laporan', [
            'wilayah_id' => $wilayah->id,
            'komoditas_id' => $komoditas->id,
            'harga' => 10000,
            'satuan' => 'kg',
            'tanggal_panen' => now()->toDateString(),
            'foto' => $validPng,
        ])->assertCreated();

        $laporanId = $createResponse->json('data.id');
        $originalFotoPath = LaporanHarga::findOrFail($laporanId)->foto_path;
        $this->assertNotNull($originalFotoPath);
        Storage::disk('public')->assertExists($originalFotoPath);

        // Update dengan foto: null harus mempertahankan foto lama
        $this->actingAs($petani, 'sanctum')->putJson("/api/petani/laporan/{$laporanId}", [
            'harga' => 11500,
            'foto' => null,
        ])->assertOk();

        $this->assertSame($originalFotoPath, LaporanHarga::findOrFail($laporanId)->foto_path);
        Storage::disk('public')->assertExists($originalFotoPath);

        // Update dengan foto palsu gagal 422 dan tetap mempertahankan foto lama
        $this->actingAs($petani, 'sanctum')->putJson("/api/petani/laporan/{$laporanId}", [
            'harga' => 12000,
            'foto' => $fakePngPayload,
        ])->assertStatus(422)->assertJsonValidationErrors(['foto']);

        $this->assertSame($originalFotoPath, LaporanHarga::findOrFail($laporanId)->foto_path);
        Storage::disk('public')->assertExists($originalFotoPath);
    }

    public function test_penyetoran_locks_supplier_satuan_and_enforces_single_rating_per_penyetoran(): void
    {
        $petani = User::where('email', 'petani@laporanharga.com')->firstOrFail();
        $supplier = Supplier::where('is_active', true)->firstOrFail();
        $hargaRow = SupplierHarga::where('supplier_id', $supplier->id)->firstOrFail();

        $storeResp = $this->actingAs($petani, 'sanctum')->postJson('/api/petani/penyetoran', [
            'supplier_id' => $supplier->id,
            'komoditas_id' => $hargaRow->komoditas_id,
            'jumlah' => 100,
            'satuan' => 'ton', // mencoba override satuan dari client
            'tanggal' => now()->toDateString(),
        ])->assertCreated();

        $penyetoranId = $storeResp->json('data.id');
        $penyetoran = Penyetoran::findOrFail($penyetoranId);
        $this->assertSame($hargaRow->satuan, $penyetoran->satuan);
        $this->assertSame(
            number_format((float) $hargaRow->harga_per_satuan * 100, 2, '.', ''),
            $penyetoran->total_nilai
        );

        // Rating tanpa penyetoran_id wajib ditolak 422
        $this->actingAs($petani, 'sanctum')->postJson("/api/petani/suppliers/{$supplier->id}/rating", [
            'rating' => 5,
            'ulasan' => 'Mantap',
        ])->assertStatus(422)->assertJsonValidationErrors(['penyetoran_id']);

        // Rating pertama sukses
        $this->actingAs($petani, 'sanctum')->postJson("/api/petani/suppliers/{$supplier->id}/rating", [
            'penyetoran_id' => $penyetoranId,
            'rating' => 5,
            'ulasan' => 'Bagus sekali',
        ])->assertCreated();

        // Daftar penyetoran menampilkan has_rating: true
        $this->actingAs($petani, 'sanctum')->getJson('/api/petani/penyetoran')
            ->assertOk()
            ->assertJsonPath('data.0.has_rating', true);

        // Rating kedua pada penyetoran yang sama ditolak 422
        $this->actingAs($petani, 'sanctum')->postJson("/api/petani/suppliers/{$supplier->id}/rating", [
            'penyetoran_id' => $penyetoranId,
            'rating' => 4,
            'ulasan' => 'Coba duplikat',
        ])->assertStatus(422);
    }
}
