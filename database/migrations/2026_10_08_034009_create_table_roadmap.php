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
        Schema::create('roadmap', function (Blueprint $table) {
            $table->id();
            $table->string('nama_roadmap');
            $table->string('deskripsi');
            $table->string('foto')->nullable();
            $table->enum('kategori',['profesional','bisnis','birokrasi','akademisi']);
            $table->integer('partisipan');
            $table->timestamps();
        });

        Schema::create('pendaftaran_roadmap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('id_roadmap')->constrained('roadmap')->onDelete('cascade'); 
            $table->timestamps();
        });

        Schema::create('bulan_roadmap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_roadmap')->constrained('roadmap')->onDelete('cascade');
            $table->integer('bulan_ke');
        });

        Schema::create('materi_roadmap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_roadmap')->constrained('roadmap')->onDelete('cascade'); 
            $table->string('judul');
            $table->boolean('tipe');
            $table->string('topik');
            $table->text('deskripsi');
            $table->json('kartu_konten')->nullable();
            $table->integer('maksimal_tugas')->nullable();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roadmap');
        Schema::dropIfExists('pendaftaran_roadmap');
        Schema::dropIfExists('materi_roadmap');
        Schema::dropIfExists('bulan_roadmap');
    }
};
