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
        Schema::table('hasil_aktivitas', function (Blueprint $table) {
            $table->text('deskripsi')->change();
            $table->text('catatan_penolakan')->nullable()->change();
            $table->foreignId('id_materi_roadmap')->nullable()->change();
            $table->foreignId('id_pendaftaran_aktivitas')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_aktivitas', function (Blueprint $table) {
            $table->string('deskripsi', 255)->change();
            $table->string('catatan_penolakan', 255)->nullable()->change();
        });
    }
};
