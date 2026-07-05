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
        Schema::create('obats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_obat_id')->constrained('kategori_obats')->onDelete('restrict');
            $table->string('nama_obat');
            $table->string('barcode')->unique()->nullable();
            $table->unsignedBigInteger('harga_beli');
            $table->unsignedBigInteger('harga_jual');
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(0);
            $table->date('tanggal_kedaluwarsa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obats');
    }
};
