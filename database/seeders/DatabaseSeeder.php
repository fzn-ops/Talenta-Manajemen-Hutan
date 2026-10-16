<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(UserDummy::class);
        $this->call(AktivitasSeeder::class);
        $this->call(KarirSeeder::class);
        $this->call(BeritaSeeder::class);
        $this->call(RoadmapSeeder::class);
        $this->call(PendaftaranAktivitasSeeder::class);
        $this->call(PengajuanAktivitasSeeder::class);
    }
}
