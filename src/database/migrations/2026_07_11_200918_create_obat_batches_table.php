<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('obat_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('obats')->onDelete('cascade');
            $table->string('nomor_batch')->nullable();
            $table->date('tanggal_kedaluwarsa');
            $table->integer('harga_beli');
            $table->integer('quantity');
            $table->integer('remaining_quantity');
            $table->timestamps();
        });

        // Backfill existing medicine stock
        $obats = DB::table('obats')->where('stok', '>', 0)->get();
        foreach ($obats as $obat) {
            DB::table('obat_batches')->insert([
                'obat_id' => $obat->id,
                'nomor_batch' => 'BATCH-INITIAL',
                'tanggal_kedaluwarsa' => $obat->tanggal_kedaluwarsa,
                'harga_beli' => $obat->harga_beli,
                'quantity' => $obat->stok,
                'remaining_quantity' => $obat->stok,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obat_batches');
    }
};
