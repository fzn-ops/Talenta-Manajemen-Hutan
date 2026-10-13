<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Berita;
use Faker\Factory as Faker;

class BeritaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pakai Faker bahasa Indonesia
        $faker = Faker::create('id_ID');

        for ($i = 1; $i <= 50; $i++) {
            Berita::create([
                'judul'      => $faker->sentence(mt_rand(5, 10)), // Bikin judul 5-10 kata
                // Gue bungkus tag <p> karena biasanya deskripsi berita dari teks editor itu bentuknya HTML
                'deskripsi'  => '<p>' . implode('</p><p>', $faker->paragraphs(mt_rand(2, 4))) . '</p>', 
                'foto'       => null, // Kita set null dulu biar aman, atau bisa isi string 'berita/dummy.jpg'
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
            ]);
        }
    }
}