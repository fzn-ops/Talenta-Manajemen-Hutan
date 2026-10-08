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
        Schema::create('table_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengajuan_aktivitas')
                ->constrained('pengajuan_aktivitas')
                ->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('kategori');
            $table->integer('partisipan');
            $table->enum('jenis', [
                'umum',
                'lomba',
            ]);
            $table->boolean('status');
            $table->dateTime('deadline');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_aktivitas');
    }
};
