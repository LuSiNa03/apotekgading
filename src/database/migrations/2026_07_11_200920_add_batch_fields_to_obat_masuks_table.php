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
        Schema::table('obat_masuks', function (Blueprint $table) {
            $table->string('nomor_batch')->nullable()->after('jumlah');
            $table->date('tanggal_kedaluwarsa')->nullable()->after('nomor_batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obat_masuks', function (Blueprint $table) {
            $table->dropColumn(['nomor_batch', 'tanggal_kedaluwarsa']);
        });
    }
};
