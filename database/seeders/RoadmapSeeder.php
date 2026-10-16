<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Roadmap;
use App\Models\BulanRoadmap;
use App\Models\MateriRoadmap;
use Illuminate\Support\Facades\Schema;

class RoadmapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        MateriRoadmap::truncate();
        BulanRoadmap::truncate();
        Roadmap::truncate();
        Schema::enableForeignKeyConstraints();

        $roadmapsData = [
            [
                'nama_roadmap' => 'Persiapan Karir Software Engineer & Fullstack Developer',
                'kategori'     => 'Profesional',
                'partisipan'   => 48,
                'months'       => [
                    [
                        'bulan_ke' => 1,
                        'items'    => [
                            [
                                'tipe'           => 1, // Materi
                                'topik'          => 'Materi 1',
                                'judul'          => 'Cara Mendapatkan Return Usaha 100% dalam 1 Bulan',
                                'deskripsi'      => '<p>Materi fundamental untuk memahami strategi percepatan pengembangan dan model integrasi berkelanjutan pada ekosistem industri modern.</p>',
                                'maksimal_tugas' => 0,
                                'cards'          => [
                                    [
                                        'id'       => 'c1',
                                        'type'     => 'text',
                                        'content'  => '<p>Selamat datang di modul pembelajaran awal. Pada materi ini, peserta akan mempelajari konsep dasar integrasi sistem, analisis kebutuhan arsitektur data, dan tata kelola alur kerja profesional.</p><p>Pastikan Anda membaca setiap referensi dan berdiskusi aktif melalui platform Talenta.</p>',
                                        'fileName' => '',
                                    ],
                                    [
                                        'id'       => 'c2',
                                        'type'     => 'video',
                                        'content'  => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                                        'fileName' => '',
                                    ],
                                ],
                            ],
                            [
                                'tipe'           => 0, // Tugas
                                'topik'          => 'Tugas 1',
                                'judul'          => 'Analisis Kelayakan Finansial dan Pasar Produk Hutan',
                                'deskripsi'      => '<p>Tugas mandiri untuk menganalisis potensi pasar dan perhitungan ROI serta payback period dari produk olahan hutan berkelanjutan.</p>',
                                'maksimal_tugas' => 1,
                                'cards'          => [
                                    [
                                        'id'       => 'c3',
                                        'type'     => 'text',
                                        'content'  => '<p>Kerjakan analisis kelayakan finansial menggunakan template spreadsheet yang tersedia. Hitung Break Even Point (BEP) dan susun proyeksi arus kas 3 tahun ke depan.</p>',
                                        'fileName' => '',
                                    ],
                                    [
                                        'id'       => 'c4',
                                        'type'     => 'task_submission',
                                        'content'  => 'Ikuti Kegiatan Terkait Analisis Kelayakan Finansial dan Pasar Produk Hutan',
                                        'fileName' => '',
                                        'isFixed'  => true,
                                    ],
                                ],
                            ],
                            [
                                'tipe'           => 1, // Materi
                                'topik'          => 'Materi 2',
                                'judul'          => 'Strategi Pemasaran Digital & Branding Komoditas Kehutanan',
                                'deskripsi'      => '<p>Memahami cara membangun identitas brand ramah lingkungan dan optimalisasi kanal digital.</p>',
                                'maksimal_tugas' => 0,
                                'cards'          => [
                                    [
                                        'id'       => 'c5',
                                        'type'     => 'text',
                                        'content'  => '<p>Strategi digital marketing berbasis story-telling hijau (green marketing) memiliki daya pikat tinggi bagi konsumen modern. Pelajari bagaimana merancang kampanye digital yang kredibel dan berdampak.</p>',
                                        'fileName' => '',
                                    ],
                                ],
                            ],
                            [
                                'tipe'           => 0, // Tugas
                                'topik'          => 'Tugas 2',
                                'judul'          => 'Pembuatan Pitch Deck Bisnis Agroforestry',
                                'deskripsi'      => '<p>Susun pitch deck sebanyak 10-12 slide yang merangkum model bisnis dan proyeksi usaha Anda.</p>',
                                'maksimal_tugas' => 1,
                                'cards'          => [
                                    [
                                        'id'       => 'c6',
                                        'type'     => 'text',
                                        'content'  => '<p>Unggah pitch deck dalam format PDF. Sertakan problem statement, solution, target market, competitive landscape, dan financial ask.</p>',
                                        'fileName' => '',
                                    ],
                                    [
                                        'id'       => 'c7',
                                        'type'     => 'task_submission',
                                        'content'  => 'Ikuti Kegiatan Terkait Pembuatan Pitch Deck Bisnis Agroforestry',
                                        'fileName' => '',
                                        'isFixed'  => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'bulan_ke' => 2,
                        'items'    => [
                            [
                                'tipe'           => 1, // Materi
                                'topik'          => 'Materi 1',
                                'judul'          => 'Pengembangan Model Bisnis Ekowisata Berbasis Komunitas',
                                'deskripsi'      => '<p>Pelajari tahapan merancang paket ekowisata dan pelibatan masyarakat lokal.</p>',
                                'maksimal_tugas' => 0,
                                'cards'          => [
                                    [
                                        'id'       => 'c8',
                                        'type'     => 'text',
                                        'content'  => '<p>Model Community-Based Tourism (CBT) menekankan keberlanjutan lingkungan dan pemberdayaan ekonomi masyarakat di sekitar kawasan hutan.</p>',
                                        'fileName' => '',
                                    ],
                                ],
                            ],
                            [
                                'tipe'           => 0, // Tugas
                                'topik'          => 'Tugas 1',
                                'judul'          => 'Rancangan Program Eduwisata Hutan Pendidikan',
                                'deskripsi'      => '<p>Buat proposal program eduwisata lengkap dengan jadwal kegiatan dan estimasi anggaran.</p>',
                                'maksimal_tugas' => 1,
                                'cards'          => [
                                    [
                                        'id'       => 'c9',
                                        'type'     => 'text',
                                        'content'  => '<p>Susun proposal kegiatan eduwisata berdurasi 2 hari 1 malam untuk kelompok siswa sekolah menengah.</p>',
                                        'fileName' => '',
                                    ],
                                    [
                                        'id'       => 'c10',
                                        'type'     => 'task_submission',
                                        'content'  => 'Ikuti Kegiatan Terkait Rancangan Program Eduwisata Hutan Pendidikan',
                                        'fileName' => '',
                                        'isFixed'  => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'nama_roadmap' => 'Inkubasi Bisnis & Rintisan Startup Berbasis Hasil Hutan',
                'kategori'     => 'Bisnis',
                'partisipan'   => 72,
                'months'       => [
                    [
                        'bulan_ke' => 1,
                        'items'    => [
                            [
                                'tipe'           => 1,
                                'topik'          => 'Materi 1',
                                'judul'          => 'Validasi Masalah & Peluang Pasar Produk Hutan Non-Kayu',
                                'deskripsi'      => '<p>Tahapan customer discovery dan validasi market size untuk produk turunan hasil hutan.</p>',
                                'maksimal_tugas' => 0,
                                'cards'          => [
                                    [
                                        'id'       => 'c11',
                                        'type'     => 'text',
                                        'content'  => '<p>Pemanfaatan madu hutan, minyak atsiri, dan tanaman obat memiliki potensi ekspor yang menjanjikan jika didukung sertifikasi organik dan traceability yang jelas.</p>',
                                        'fileName' => '',
                                    ],
                                ],
                            ],
                            [
                                'tipe'           => 0,
                                'topik'          => 'Tugas 1',
                                'judul'          => 'Penyusunan Business Model Canvas (BMC)',
                                'deskripsi'      => '<p>Identifikasi 9 blok bangunan bisnis untuk rintisan startup kehutanan Anda.</p>',
                                'maksimal_tugas' => 1,
                                'cards'          => [
                                    [
                                        'id'       => 'c12',
                                        'type'     => 'text',
                                        'content'  => '<p>Isi template BMC lengkap dan jelaskan value proposition unik yang membedakan produk Anda dari kompetitor konvensional.</p>',
                                        'fileName' => '',
                                    ],
                                    [
                                        'id'       => 'c13',
                                        'type'     => 'task_submission',
                                        'content'  => 'Ikuti Kegiatan Terkait Penyusunan Business Model Canvas (BMC)',
                                        'fileName' => '',
                                        'isFixed'  => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'nama_roadmap' => 'Jalur Masuk ASN & Karir Birokrasi Lingkungan Hidup',
                'kategori'     => 'Birokrasi',
                'partisipan'   => 35,
                'months'       => [
                    [
                        'bulan_ke' => 1,
                        'items'    => [
                            [
                                'tipe'           => 1,
                                'topik'          => 'Materi 1',
                                'judul'          => 'Struktur Kelembagaan & Kebijakan Tata Kelola Kehutanan',
                                'deskripsi'      => '<p>Mempelajari kerangka kerja Kementerian Lingkungan Hidup dan Kehutanan serta instansi daerah.</p>',
                                'maksimal_tugas' => 0,
                                'cards'          => [
                                    [
                                        'id'       => 'c14',
                                        'type'     => 'text',
                                        'content'  => '<p>Memahami hierarki regulasi undang-undang kehutanan, peraturan pemerintah, dan peraturan menteri dalam pengelolaan kawasan konservasi dan hutan lindung.</p>',
                                        'fileName' => '',
                                    ],
                                ],
                            ],
                            [
                                'tipe'           => 0,
                                'topik'          => 'Tugas 1',
                                'judul'          => 'Analisis Kasus Kebijakan Tata Ruang Kehutanan',
                                'deskripsi'      => '<p>Kajian kritis terhadap implementasi perhutanan sosial di tingkat tapak.</p>',
                                'maksimal_tugas' => 1,
                                'cards'          => [
                                    [
                                        'id'       => 'c15',
                                        'type'     => 'text',
                                        'content'  => '<p>Susun policy brief 2 halaman mengenai solusi penyelesaian konflik tenurial di kawasan hutan produksi.</p>',
                                        'fileName' => '',
                                    ],
                                    [
                                        'id'       => 'c16',
                                        'type'     => 'task_submission',
                                        'content'  => 'Ikuti Kegiatan Terkait Analisis Kasus Kebijakan Tata Ruang Kehutanan',
                                        'fileName' => '',
                                        'isFixed'  => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'nama_roadmap' => 'Persiapan Studi Lanjut S2/S3 & Publikasi Ilmiah Kehutanan',
                'kategori'     => 'Akademisi',
                'partisipan'   => 28,
                'months'       => [
                    [
                        'bulan_ke' => 1,
                        'items'    => [
                            [
                                'tipe'           => 1,
                                'topik'          => 'Materi 1',
                                'judul'          => 'Strategi Penulisan Proposal Riset & Aplikasi Beasiswa',
                                'deskripsi'      => '<p>Panduan menyusun research proposal berstandar internasional dan kiat wawancara beasiswa.</p>',
                                'maksimal_tugas' => 0,
                                'cards'          => [
                                    [
                                        'id'       => 'c17',
                                        'type'     => 'text',
                                        'content'  => '<p>Pelajari struktur state-of-the-art, novelty, dan metodologi riset bioteknologi serta konservasi genetik pohon hutan.</p>',
                                        'fileName' => '',
                                    ],
                                ],
                            ],
                            [
                                'tipe'           => 0,
                                'topik'          => 'Tugas 1',
                                'judul'          => 'Draft Awal Research Statement & Study Plan',
                                'deskripsi'      => '<p>Menyusun 1.000 kata rencana studi dan tujuan riset pascasarjana.</p>',
                                'maksimal_tugas' => 1,
                                'cards'          => [
                                    [
                                        'id'       => 'c18',
                                        'type'     => 'text',
                                        'content'  => '<p>Tuliskan motivasi, pengalaman penelitian terdahulu, dan kontribusi riset Anda terhadap penanganan perubahan iklim.</p>',
                                        'fileName' => '',
                                    ],
                                    [
                                        'id'       => 'c19',
                                        'type'     => 'task_submission',
                                        'content'  => 'Ikuti Kegiatan Terkait Draft Awal Research Statement & Study Plan',
                                        'fileName' => '',
                                        'isFixed'  => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'nama_roadmap' => 'Sertifikasi Konsultan Lingkungan & AMDAL Profesional',
                'kategori'     => 'Profesional',
                'partisipan'   => 63,
                'months'       => [
                    [
                        'bulan_ke' => 1,
                        'items'    => [
                            [
                                'tipe'           => 1,
                                'topik'          => 'Materi 1',
                                'judul'          => 'Metodologi Penapisan & Pelingkupan AMDAL',
                                'deskripsi'      => '<p>Standar baku mutu lingkungan dan teknik identifikasi dampak penting hipotetik.</p>',
                                'maksimal_tugas' => 0,
                                'cards'          => [
                                    [
                                        'id'       => 'c20',
                                        'type'     => 'text',
                                        'content'  => '<p>Peserta mempelajari instrumen Environmental Impact Assessment (EIA) sesuai Permen LHK terbaru dan standar ISO 14001.</p>',
                                        'fileName' => '',
                                    ],
                                ],
                            ],
                            [
                                'tipe'           => 0,
                                'topik'          => 'Tugas 1',
                                'judul'          => 'Penyusunan Matriks Rencana Pengelolaan Lingkungan (RKL)',
                                'deskripsi'      => '<p>Praktik perumusan mitigasi dampak terhadap flora, fauna, dan kualitas air.</p>',
                                'maksimal_tugas' => 1,
                                'cards'          => [
                                    [
                                        'id'       => 'c21',
                                        'type'     => 'text',
                                        'content'  => '<p>Susun matriks RKL-RPL untuk studi kasus pembangunan jalan akses kawasan hutan tanaman industri.</p>',
                                        'fileName' => '',
                                    ],
                                    [
                                        'id'       => 'c22',
                                        'type'     => 'task_submission',
                                        'content'  => 'Ikuti Kegiatan Terkait Penyusunan Matriks Rencana Pengelolaan Lingkungan (RKL)',
                                        'fileName' => '',
                                        'isFixed'  => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'nama_roadmap' => 'Pengembangan Usaha Ekowisata & Agroforestry Berkelanjutan',
                'kategori'     => 'Bisnis',
                'partisipan'   => 31,
                'months'       => [
                    [
                        'bulan_ke' => 1,
                        'items'    => [
                            [
                                'tipe'           => 1,
                                'topik'          => 'Materi 1',
                                'judul'          => 'Desain Lansekap Kawasan Wisata Alam & Agroforestri',
                                'deskripsi'      => '<p>Kombinasi komoditas produktif (kopi/kakao) dengan konservasi tutupan tajuk pohon.</p>',
                                'maksimal_tugas' => 0,
                                'cards'          => [
                                    [
                                        'id'       => 'c23',
                                        'type'     => 'text',
                                        'content'  => '<p>Teknik silvikultur dan penataan jalur interpretasi wisata yang edukatif tanpa merusak ekosistem tegakan hutan.</p>',
                                        'fileName' => '',
                                    ],
                                ],
                            ],
                            [
                                'tipe'           => 0,
                                'topik'          => 'Tugas 1',
                                'judul'          => 'Perhitungan Daya Dukung Wisata (Carrying Capacity)',
                                'deskripsi'      => '<p>Menghitung batas maksimum pengunjung harian agar tidak mendegradasi lingkungan.</p>',
                                'maksimal_tugas' => 1,
                                'cards'          => [
                                    [
                                        'id'       => 'c24',
                                        'type'     => 'text',
                                        'content'  => '<p>Gunakan rumus Physical Carrying Capacity (PCC) dan Real Carrying Capacity (RCC) pada studi kasus jalur trekking hutan pinus.</p>',
                                        'fileName' => '',
                                    ],
                                    [
                                        'id'       => 'c25',
                                        'type'     => 'task_submission',
                                        'content'  => 'Ikuti Kegiatan Terkait Perhitungan Daya Dukung Wisata (Carrying Capacity)',
                                        'fileName' => '',
                                        'isFixed'  => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($roadmapsData as $rData) {
            $months = $rData['months'];
            unset($rData['months']);

            $roadmap = Roadmap::create($rData);

            foreach ($months as $mData) {
                $items = $mData['items'];
                unset($mData['items']);

                $mData['id_roadmap'] = $roadmap->id;
                $bulan = BulanRoadmap::create($mData);

                foreach ($items as $item) {
                    MateriRoadmap::create([
                        'id_bulan_roadmap' => $bulan->id,
                        'judul'            => $item['judul'],
                        'tipe'             => $item['tipe'],
                        'topik'            => $item['topik'],
                        'deskripsi'        => $item['deskripsi'],
                        'kartu_konten'     => $item['cards'],
                        'maksimal_tugas'   => $item['maksimal_tugas'],
                    ]);
                }
            }
        }
    }
}
