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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->renameColumn('name', 'nama');
            $table->string('role')->after('id')->nullable();
            $table->string('nim')->after('role')->unique()->nullable();
            $table->integer('angkatan')->after('nama')->nullable();
            $table->string('talent_mapping')->after('angkatan')->nullable();
            $table->string('no_handphone')->after('email')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('nama', 'name');
            $table->dropColumn([
                'role', 
                'nim', 
                'angkatan', 
                'talent_mapping', 
                'no_handphone'
                ]);
        });
    }
};
