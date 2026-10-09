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
        Schema::create('pendaftaran_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('id_aktivitas')->constrained('aktivitas')->onDelete('cascade'); 
            $table->string('bukti_pendaftaran'); 
            $table->string('catatan_penolakan')->nullable(); 
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_aktivitas');
    }
};
