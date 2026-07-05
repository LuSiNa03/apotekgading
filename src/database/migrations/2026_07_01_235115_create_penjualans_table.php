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
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->unsignedBigInteger('total_harga')->default(0);
            $table->string('metode_pembayaran'); // 'tunai', 'non-tunai'
            $table->string('status_pembayaran')->default('pending'); // 'pending', 'berhasil', 'gagal'
            $table->unsignedBigInteger('nominal_bayar')->nullable();
            $table->unsignedBigInteger('kembalian')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
