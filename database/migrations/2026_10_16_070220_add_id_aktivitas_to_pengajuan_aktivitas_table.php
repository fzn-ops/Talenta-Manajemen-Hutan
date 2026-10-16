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
        Schema::table('pengajuan_aktivitas', function (Blueprint $table) {
            $table->foreignId('id_aktivitas')->nullable()->after('id_user')->constrained('aktivitas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_aktivitas', function (Blueprint $table) {
            $table->dropForeign(['id_aktivitas']);
            $table->dropColumn('id_aktivitas');
        });
    }
};
