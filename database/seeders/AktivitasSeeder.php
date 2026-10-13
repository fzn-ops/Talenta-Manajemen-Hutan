<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AktivitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('aktivitas')->insert([
            [
                'judul' => 'Seminar Teknologi Informasi',
                'deskripsi' => 'Seminar mengenai perkembangan teknologi informasi dan transformasi digital.',
                'kategori' => json_encode(['Akademisi', 'Profesional']), 
                'partisipan' => 100,
                'jenis' => 'umum',
                'status' => true,
                'deadline' => Carbon::now()->addDays(14),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'judul' => 'Lomba Pengembangan Aplikasi',
                'deskripsi' => 'Kompetisi pengembangan aplikasi inovatif untuk mahasiswa.',
                'kategori' => json_encode(['Bisnis', 'Profesional']),
                'partisipan' => 50,
                'jenis' => 'lomba',
                'status' => true,
                'deadline' => Carbon::now()->addDays(30),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'judul' => 'Workshop UI/UX Design',
                'deskripsi' => 'Pelatihan dasar perancangan antarmuka dan pengalaman pengguna.',
                'kategori' => json_encode(['Birokrat', 'Akademisi']),
                'partisipan' => 75,
                'jenis' => 'umum',
                'status' => true,
                'deadline' => Carbon::now()->addDays(21),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'judul' => 'Lomba Desain Antarmuka',
                'deskripsi' => 'Kompetisi desain antarmuka aplikasi untuk meningkatkan kreativitas mahasiswa.',
                'kategori' => json_encode(['Bisnis']),
                'partisipan' => 40,
                'jenis' => 'lomba',
                'status' => false,
                'deadline' => Carbon::now()->subDays(7),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'judul' => 'Pelatihan Data Science',
                'deskripsi' => 'Pelatihan pengolahan data, visualisasi, dan pengenalan machine learning.',
                'kategori' => json_encode(['Profesional', 'Akademisi', 'Birokrat']),
                'partisipan' => 60,
                'jenis' => 'umum',
                'status' => true,
                'deadline' => Carbon::now()->addDays(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}