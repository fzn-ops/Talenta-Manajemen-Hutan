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
        Schema::create('hasil_aktivitas', function (Blueprint $table) {
            $table->id(); 
            // Foreign Keys (FK)
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_materi_roadmap')->constrained('materi_roadmap')->onDelete('cascade');
            $table->foreignId('id_pendaftaran_aktivitas')->constrained('pendaftaran_aktivitas')->onDelete('cascade');
            // Kolom string
            $table->string('deskripsi'); 
            $table->string('media')->nullable();
            $table->string('catatan_penolakan')->nullable(); 
            // status: enumeration
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            // created_at & update_at: datetime
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_aktivitas');
    }
};
