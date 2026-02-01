<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_harga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('komoditas_id')->constrained('komoditas')->cascadeOnDelete();
            $table->decimal('harga_per_satuan', 15, 2);
            $table->string('satuan', 20)->default('kg');
            $table->timestamps();
            $table->unique(['supplier_id', 'komoditas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_harga');
    }
};
