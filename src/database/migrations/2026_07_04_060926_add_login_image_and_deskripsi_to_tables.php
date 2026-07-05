<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add login_image to identitas_apoteks
        Schema::table('identitas_apoteks', function (Blueprint $table) {
            $table->string('login_image')->nullable()->after('logo');
        });

        // Add deskripsi to obats
        Schema::table('obats', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('nama_obat');
        });
    }

    public function down(): void
    {
        Schema::table('identitas_apoteks', function (Blueprint $table) {
            $table->dropColumn('login_image');
        });

        Schema::table('obats', function (Blueprint $table) {
            $table->dropColumn('deskripsi');
        });
    }
};
