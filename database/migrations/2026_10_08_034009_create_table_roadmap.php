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
        Schema::create('table_roadmap', function (Blueprint $table) {
            $table->id();
            $table->string('nama_roadmap');
            $table->string('deskripsi');
            $table->string('foto')->nullable();
            $table->enum('kategori',['profesional','bisnis','birokrasi','akademisi']);
            $table->integer('partisipan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_roadmap');

    }
};
