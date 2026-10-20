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

        $dummyImages = [
            1 => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=800&auto=format&fit=crop&q=80',
            2 => 'https://images.unsplash.com/photo-1511497584788-87676104235f?w=800&auto=format&fit=crop&q=80',
            3 => 'https://images.unsplash.com/photo-1448375240586-882707db888b?w=800&auto=format&fit=crop&q=80',
            4 => 'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?w=800&auto=format&fit=crop&q=80',
            5 => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=800&auto=format&fit=crop&q=80',
        ];

        foreach ($dummyImages as $aktId => $url) {
            DB::table('gambar_aktivitas')->insert([
                'id_aktivitas' => $aktId,
                'gambar' => $url,
                'utama' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}