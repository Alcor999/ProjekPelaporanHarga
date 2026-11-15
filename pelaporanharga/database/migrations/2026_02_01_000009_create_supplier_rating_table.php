<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_rating', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('penyetoran_id')->nullable()->constrained('penyetoran')->nullOnDelete();
            $table->tinyInteger('rating'); // 1-5
            $table->text('ulasan')->nullable();
            $table->timestamps();
            $table->unique('penyetoran_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_rating');
    }
};
