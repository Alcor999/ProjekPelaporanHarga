<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan_harga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // petani yang melapor
            $table->foreignId('wilayah_id')->constrained('wilayah')->onDelete('cascade'); // explicitly use 'wilayah' table
            $table->foreignId('komoditas_id')->constrained('komoditas')->onDelete('cascade'); // explicitly use 'komoditas' table
            $table->decimal('harga', 10, 2); // harga per kg/satuan
            $table->string('satuan')->default('kg'); // kg, ton, dll
            $table->date('tanggal_panen'); // kapan dipanen
            $table->date('tanggal_laporan')->default(now()); // kapan dilaporkan
            $table->text('catatan')->nullable(); // catatan tambahan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_harga');
    }
};
