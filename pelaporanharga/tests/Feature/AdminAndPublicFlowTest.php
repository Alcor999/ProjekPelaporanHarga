<?php

namespace Tests\Feature;

use App\Models\Komoditas;
use App\Models\LaporanHarga;
use App\Models\Notification;
use App\Models\Penyetoran;
use App\Models\Supplier;
use App\Models\SupplierHarga;
use App\Models\User;
use App\Models\Wilayah;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminAndPublicFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petani;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@laporanharga.com')->firstOrFail();
        $this->petani = User::where('email', 'petani@laporanharga.com')->firstOrFail();
    }

    public function test_route_cache_succeeds_without_closure_routes(): void
    {
        $exitCode = Artisan::call('route:cache');
        $this->assertSame(0, $exitCode);

        Artisan::call('route:clear');
    }

    public function test_admin_can_update_penyetoran_status_and_creates_notification(): void
    {
        $supplier = Supplier::firstOrFail();
        $komoditas = Komoditas::firstOrFail();

        $penyetoran = Penyetoran::create([
            'user_id' => $this->petani->id,
            'supplier_id' => $supplier->id,
            'komoditas_id' => $komoditas->id,
            'jumlah' => 25,
            'satuan' => 'kg',
            'harga_per_satuan' => 6500,
            'total_nilai' => 162500,
            'status' => 'pending',
            'tanggal' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.penyetoran.update-status', $penyetoran), ['status' => 'completed'])
            ->assertRedirect();

        $this->assertSame('completed', $penyetoran->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->petani->id,
            'type' => 'penyetoran_completed',
            'related_id' => $penyetoran->id,
        ]);

        // Status yang sudah final tidak bisa diubah ulang
        $this->actingAs($this->admin)
            ->patch(route('admin.penyetoran.update-status', $penyetoran), ['status' => 'cancelled'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('completed', $penyetoran->fresh()->status);
    }

    public function test_admin_edit_laporan_requires_rejection_reason_when_rejected_and_notifies(): void
    {
        $wilayah = Wilayah::firstOrFail();
        $komoditas = Komoditas::firstOrFail();

        $laporan = LaporanHarga::create([
            'user_id' => $this->petani->id,
            'wilayah_id' => $wilayah->id,
            'komoditas_id' => $komoditas->id,
            'harga' => 7000,
            'satuan' => 'kg',
            'tanggal_panen' => now()->toDateString(),
            'tanggal_laporan' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.laporan.update', $laporan), [
                'harga' => 7000,
                'satuan' => 'kg',
                'tanggal_panen' => now()->toDateString(),
                'status' => 'rejected',
                'rejection_reason' => '',
            ])
            ->assertSessionHasErrors(['rejection_reason']);

        $this->actingAs($this->admin)
            ->put(route('admin.laporan.update', $laporan), [
                'harga' => 7100,
                'satuan' => 'kg',
                'tanggal_panen' => now()->toDateString(),
                'status' => 'rejected',
                'rejection_reason' => 'Harga tidak sesuai bukti nota',
            ])
            ->assertRedirect(route('admin.laporan.index'));

        $laporan->refresh();
        $this->assertSame('rejected', $laporan->status);
        $this->assertSame('Harga tidak sesuai bukti nota', $laporan->rejection_reason);
        $this->assertTrue(
            Notification::where('user_id', $this->petani->id)
                ->where('related_id', $laporan->id)
                ->where('type', 'laporan_rejected')
                ->exists()
        );
    }

    public function test_master_data_destroy_blocks_referenced_records_and_preserves_active_flag(): void
    {
        $wilayah = Wilayah::firstOrFail();
        $komoditas = Komoditas::firstOrFail();

        LaporanHarga::create([
            'user_id' => $this->petani->id,
            'wilayah_id' => $wilayah->id,
            'komoditas_id' => $komoditas->id,
            'harga' => 6800,
            'satuan' => 'kg',
            'tanggal_panen' => now()->toDateString(),
            'tanggal_laporan' => now()->toDateString(),
            'status' => 'approved',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.komoditas.destroy', $komoditas))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas('komoditas', ['id' => $komoditas->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.wilayah.destroy', $wilayah))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas('wilayah', ['id' => $wilayah->id]);

        // Tambah komoditas non-aktif lalu hapus ketika belum dipakai
        $this->actingAs($this->admin)
            ->post(route('admin.komoditas.store'), [
                'name' => 'Jagung Uji',
                'kategori' => 'jagung',
                'grade' => 'A',
            ])
            ->assertRedirect();

        $created = Komoditas::where('name', 'Jagung Uji')->firstOrFail();
        $this->assertFalse((bool) $created->is_active);

        $this->actingAs($this->admin)
            ->delete(route('admin.komoditas.destroy', $created))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('komoditas', ['id' => $created->id]);
    }

    public function test_public_harga_terkini_groups_by_satuan_and_statistik_live_supports_filters(): void
    {
        $wilayah = Wilayah::firstOrFail();
        $komoditas = Komoditas::firstOrFail();

        LaporanHarga::create([
            'user_id' => $this->petani->id,
            'wilayah_id' => $wilayah->id,
            'komoditas_id' => $komoditas->id,
            'harga' => 6500,
            'satuan' => 'kg',
            'tanggal_panen' => now()->toDateString(),
            'tanggal_laporan' => now()->toDateString(),
            'status' => 'approved',
        ]);

        LaporanHarga::create([
            'user_id' => $this->petani->id,
            'wilayah_id' => $wilayah->id,
            'komoditas_id' => $komoditas->id,
            'harga' => 650000,
            'satuan' => 'kwintal',
            'tanggal_panen' => now()->toDateString(),
            'tanggal_laporan' => now()->toDateString(),
            'status' => 'approved',
        ]);

        $response = $this->getJson(route('api.public.harga-terkini', ['komoditas_id' => $komoditas->id]));
        $response->assertOk()
            ->assertJsonPath('success', true);

        $items = $response->json('data');
        $this->assertCount(2, $items);
        $satuans = collect($items)->pluck('satuan')->sort()->values()->all();
        $this->assertSame(['kg', 'kwintal'], $satuans);

        $this->get(route('public.index'))->assertOk();

        $statResponse = $this->actingAs($this->admin)
            ->getJson(route('admin.statistik.api', [
                'periode' => 30,
                'wilayah_id' => $wilayah->id,
                'komoditas_id' => $komoditas->id,
            ]));

        $statResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('stats.satuan_periode', 'campuran satuan');
    }
}
