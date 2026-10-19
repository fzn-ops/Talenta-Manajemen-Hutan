<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HasilAktivitas;
use App\Models\User;
use App\Models\MateriRoadmap;
use App\Models\PendaftaranAktivitas;

class HasilAktivitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fauzan = User::where('nim', 'J0403231085')->first() ?? User::first();
        $farhan = User::where('nim', 'J0403231075')->first() ?? User::first();

        $materiList = MateriRoadmap::all();
        $pendaftaranList = PendaftaranAktivitas::all();

        $defaultMateri = $materiList->first();
        $defaultPendaftaran = $pendaftaranList->first();

        $dummyItems = [
            [
                'user' => $fauzan,
                'status' => 'menunggu',
                'deskripsi' => "Mengikuti kegiatan Hackathon V2.0 2026 tingkat nasional dan berhasil menyelesaikan tantangan pembuatan prototipe solusi digital untuk kehutanan berkelanjutan dalam kurun waktu 48 jam. Tim menyusun arsitektur sistem, API integrasi, dan dashboard analytics.",
                'media' => 'bukti_hackathon_fauzan.jpg',
                'catatan_penolakan' => null,
            ],
            [
                'user' => $farhan,
                'status' => 'menunggu',
                'deskripsi' => "Berhasil menyelesaikan pelatihan data science dan implementasi model machine learning untuk estimasi biomassa pohon menggunakan citra satelit Sentinel-2. Kode program dan visualisasi grafik sudah dilampirkan.",
                'media' => 'sertifikat_pelatihan_ds.jpg',
                'catatan_penolakan' => null,
            ],
            [
                'user' => $fauzan,
                'status' => 'disetujui',
                'deskripsi' => "Dokumentasi keikutsertaan sebagai finalis kompetisi UI/UX Design Competition 2026 dengan perancangan mobile app pelaporan pembalakan liar secara real-time berbasis geolokasi.",
                'media' => 'finalis_uiux_2026.png',
                'catatan_penolakan' => null,
            ],
            [
                'user' => $farhan,
                'status' => 'ditolak',
                'deskripsi' => "Pengumpulan bukti keikutsertaan seminar nasional konservasi sumberdaya hutan.",
                'media' => 'seminar_konservasi.jpg',
                'catatan_penolakan' => 'Dokumen bukti tidak menyertakan stempel resmi panitia dan deskripsi luaran belum memenuhi standar minimum.',
            ],
            [
                'user' => $fauzan,
                'status' => 'menunggu',
                'deskripsi' => "Implementasi integrasi smart sensor IoT pada lahan percobaan hutan pendidikan untuk monitoring suhu dan kelembaban tanah otomatis.",
                'media' => 'dokumentasi_iot_hutan.jpg',
                'catatan_penolakan' => null,
            ],
            [
                'user' => $farhan,
                'status' => 'disetujui',
                'deskripsi' => "Penyelesaian mini riset pemetaan tutupan tajuk pohon berbasis drone UAV orthophoto di wilayah Bogor. Menghasilkan peta klasifikasi NDVI presisi tinggi.",
                'media' => 'peta_orthophoto_uav.png',
                'catatan_penolakan' => null,
            ],
            [
                'user' => $fauzan,
                'status' => 'menunggu',
                'deskripsi' => "Mengikuti workshop pengembangan model bisnis wirausaha kehutanan ramah lingkungan (ecopreneurship) bersama industri kayu sengon.",
                'media' => 'workshop_ecopreneur.jpg',
                'catatan_penolakan' => null,
            ],
            [
                'user' => $farhan,
                'status' => 'ditolak',
                'deskripsi' => "Laporan magang lapangan singkat pengenalan spesies flora endemik di Taman Nasional.",
                'media' => 'laporan_flora_singkat.pdf',
                'catatan_penolakan' => 'Data Tidak Lengkap. Format laporan tidak sesuai dengan template resmi Talenta.',
            ],
            [
                'user' => $fauzan,
                'status' => 'menunggu',
                'deskripsi' => "Pembuatan modul ajar interaktif untuk pengenalan sistem sertifikasi kayu FSC bagi mahasiswa angkatan baru.",
                'media' => 'modul_fsc_interaktif.png',
                'catatan_penolakan' => null,
            ],
            [
                'user' => $farhan,
                'status' => 'menunggu',
                'deskripsi' => "Partisipasi dalam lokakarya kebijakan tata ruang hutan bersama dinas kehutanan provinsi Jawa Barat.",
                'media' => 'lokakarya_kebijakan.jpg',
                'catatan_penolakan' => null,
            ],
        ];

        foreach ($dummyItems as $idx => $item) {
            $user = $item['user'] ?? $fauzan;
            $materi = $materiList->get($idx % max(1, $materiList->count())) ?? $defaultMateri;
            $pendaftaran = $pendaftaranList->get($idx % max(1, $pendaftaranList->count())) ?? $defaultPendaftaran;

            HasilAktivitas::create([
                'id_user'                  => $user->id,
                'id_materi_roadmap'        => $materi?->id,
                'id_pendaftaran_aktivitas' => $pendaftaran?->id,
                'deskripsi'                => $item['deskripsi'],
                'media'                    => $item['media'],
                'catatan_penolakan'        => $item['catatan_penolakan'],
                'status'                   => $item['status'],
            ]);
        }
    }
}
