<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->tinyInteger('hari'); // 1=Senin, 2=Selasa, ..., 7=Minggu
            $table->time('jam_buka')->nullable();
            $table->time('jam_tutup')->nullable();
            $table->boolean('is_buka')->default(true);
            $table->string('catatan', 255)->nullable();
            $table->timestamps();
            $table->unique(['supplier_id', 'hari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_jadwal');
    }
};
