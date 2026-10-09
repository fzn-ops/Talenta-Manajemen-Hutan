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
        Schema::create('pengajuan_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('kategori');
            $table->integer('partisipan')->default(0);
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])
                  ->default('menunggu');
            $table->dateTime('deadline')->nullable();
            $table->timestamps();
        });

        Schema::create('gambar_pengajuan_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan_aktivitas')->constrained('pengajuan_aktivitas')->cascadeOnDelete();
            $table->string('gambar');
            $table->boolean('utama')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_aktivitas');
        Schema::dropIfExists('gambar_pengajuan_aktivitas');
    }
};
