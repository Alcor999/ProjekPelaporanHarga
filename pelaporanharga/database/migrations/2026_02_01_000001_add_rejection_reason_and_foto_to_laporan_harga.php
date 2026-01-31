<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_harga', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
            $table->string('foto_path')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_harga', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'foto_path']);
        });
    }
};
