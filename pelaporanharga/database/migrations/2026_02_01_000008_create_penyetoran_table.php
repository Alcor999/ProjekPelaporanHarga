<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyetoran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('komoditas_id')->constrained('komoditas')->cascadeOnDelete();
            $table->decimal('jumlah', 15, 2);
            $table->string('satuan', 20)->default('kg');
            $table->decimal('harga_per_satuan', 15, 2);
            $table->decimal('total_nilai', 15, 2);
            $table->date('tanggal');
            $table->string('status', 20)->default('pending'); // pending, completed, cancelled
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyetoran');
    }
};
