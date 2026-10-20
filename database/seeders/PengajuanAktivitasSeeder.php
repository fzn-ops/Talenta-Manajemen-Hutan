<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PengajuanAktivitas;
use App\Models\GambarPengajuanAktivitas;
use App\Models\User;
use Carbon\Carbon;

class PengajuanAktivitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan user ada, jika belum ada jalankan UserDummy
        $mahasiswa = User::where('role', 'mahasiswa')->first();
        if (!$mahasiswa) {
            $this->call(UserDummy::class);
            $mahasiswa = User::where('role', 'mahasiswa')->first();
        }

        if (!$mahasiswa) {
            $mahasiswa = User::first();
        }

        $userId = $mahasiswa->id;

        $dummyList = [
            [
                'judul' => 'Pelatihan Mitigasi Risiko Kebakaran Hutan & Lahan Berbasis IoT',
                'deskripsi' => 'Pelatihan intensif pemantauan hotspot kebakaran hutan menggunakan sensor IoT dan satelit cuaca secara real-time untuk memperkuat kapasitas mitigasi bencana di kawasan konservasi.',
                'kategori' => 'Profesional, Akademisi',
                'status' => 'menunggu',
                'catatan_penolakan' => null,
                'deadline' => Carbon::now()->addDays(20),
                'images' => [
                    'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1511497584788-87676104235f?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'judul' => 'Workshop Valuasi Ekonomi Karbon dan Inventarisasi GRK Kehutanan',
                'deskripsi' => 'Workshop komprehensif penghitungan simpanan karbon pohon (biomassa) dan penyusunan dokumen emisi GRK untuk mendukung skema perdagangan karbon sukarela (voluntary carbon market).',
                'kategori' => 'Bisnis, Profesional',
                'status' => 'disetujui',
                'catatan_penolakan' => null,
                'deadline' => Carbon::now()->addDays(35),
                'images' => [
                    'https://images.unsplash.com/photo-1448375240586-882707db888b?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'judul' => 'Sosialisasi Sertifikasi SVLK Kayu Rakyat dan Ekspor Berkelanjutan',
                'deskripsi' => 'Sosialisasi kepatuhan sertifikasi Sistem Verifikasi Legalitas dan Kelestarian (SVLK) bagi UMKM dan pengelola hutan rakyat guna menembus pasar ekspor global.',
                'kategori' => 'Birokrat, Bisnis',
                'status' => 'ditolak',
                'catatan_penolakan' => 'Dokumen lampiran proposal belum ditandatangani oleh pemateri dan rincian anggaran belum lengkap.',
                'deadline' => Carbon::now()->addDays(12),
                'images' => [
                    'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'judul' => 'Seminar Konservasi Koridor Satwa dan Biodiversitas Hutan Tropis',
                'deskripsi' => 'Seminar ilmiah membedah riset identifikasi jalur jelajah satwa liar endemik serta perencanaan tata ruang terpadu untuk mencegah fragmentasi habitat hutan.',
                'kategori' => 'Akademisi, Birokrat',
                'status' => 'menunggu',
                'catatan_penolakan' => null,
                'deadline' => Carbon::now()->addDays(25),
                'images' => [
                    'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1448375240586-882707db888b?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'judul' => 'Bootcamp Pemetaan Drone LiDAR dan SIG Tata Kelola Kawasan Hutan',
                'deskripsi' => 'Pelatihan teknik fotogrametri udara drone dan ekstraksi digital surface model (DSM) untuk pemetaan kontur, biomassa tegakan, dan batas kawasan hutan produksi.',
                'kategori' => 'Profesional, Akademisi, Bisnis',
                'status' => 'menunggu',
                'catatan_penolakan' => null,
                'deadline' => Carbon::now()->addDays(40),
                'images' => [
                    'https://images.unsplash.com/photo-1508614589041-895b88991e3e?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'judul' => 'Kompetisi Ide Bisnis Silvopastura dan Hasil Hutan Bukan Kayu (HHBK)',
                'deskripsi' => 'Kompetisi perancangan model bisnis integrasi kehutanan dan peternakan (silvopasture) serta pemanfaatan getah, madu, dan minyak atsiri secara berkelanjutan.',
                'kategori' => 'Bisnis',
                'status' => 'disetujui',
                'catatan_penolakan' => null,
                'deadline' => Carbon::now()->addDays(45),
                'images' => [
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'judul' => 'Webinar Regulasi Tata Batas Kawasan Hutan dan Penataan Ruang Nasional',
                'deskripsi' => 'Diskusi kebijakan hukum agraria dan kehutanan terkait percepatan pengukuhan kawasan hutan lindung dan hak kelola masyarakat hukum adat.',
                'kategori' => 'Birokrat, Akademisi',
                'status' => 'ditolak',
                'catatan_penolakan' => 'Jadwal pelaksanaan bertabrakan dengan agenda resmi fakultas dan format poster tidak memenuhi rasio standar.',
                'deadline' => Carbon::now()->addDays(15),
                'images' => [
                    'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'judul' => 'Pelatihan Restorasi Lanskap Hutan Mangrove dan Blue Carbon',
                'deskripsi' => 'Program praktik lapangan teknik pembibitan Rhizophora serta pemantauan sedimentasi dan keanekaragaman biota perairan pesisir.',
                'kategori' => 'Profesional, Birokrat',
                'status' => 'menunggu',
                'catatan_penolakan' => null,
                'deadline' => Carbon::now()->addDays(30),
                'images' => [
                    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'judul' => 'Forum Diskusi Pengelolaan Hutan Berbasis Kelompok Tani Hutan (KTH)',
                'deskripsi' => 'Dialog pemberdayaan kelembagaan kelompok tani hutan dalam mengelola perhutanan sosial skema Hutan Kemasyarakatan (HKm).',
                'kategori' => 'Birokrat, Bisnis, Akademisi',
                'status' => 'disetujui',
                'catatan_penolakan' => null,
                'deadline' => Carbon::now()->addDays(60),
                'images' => [
                    'https://images.unsplash.com/photo-1511497584788-87676104235f?w=800&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'judul' => 'Studi Lapangan Audit Sertifikasi Ekolabel FSC & PEFC Hutan Alam',
                'deskripsi' => 'Kegiatan simulasi audit verifikasi kepatuhan kelestarian pemanenan kayu ramah lingkungan (Reduced Impact Logging) standar internasional.',
                'kategori' => 'Profesional, Bisnis',
                'status' => 'ditolak',
                'catatan_penolakan' => 'Kapasitas partisipan terlalu minim untuk skala studi lapangan dan surat izin lokasi belum diterbitkan.',
                'deadline' => Carbon::now()->addDays(18),
                'images' => [
                    'https://images.unsplash.com/photo-1448375240586-882707db888b?w=800&auto=format&fit=crop&q=80',
                ],
            ],
        ];

        foreach ($dummyList as $item) {
            $images = $item['images'];
            unset($item['images']);

            $item['id_user'] = $userId;
            $item['created_at'] = Carbon::now();
            $item['updated_at'] = Carbon::now();

            $pengajuan = PengajuanAktivitas::create($item);

            foreach ($images as $index => $imgUrl) {
                GambarPengajuanAktivitas::create([
                    'id_pengajuan_aktivitas' => $pengajuan->id,
                    'gambar' => $imgUrl,
                    'utama' => $index === 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            if ($item['status'] === 'disetujui') {
                $pengajuan->status = 'menunggu';
                $pengajuan->save();
                app(\App\Services\PersetujuanAktivitasService::class)->approve($pengajuan->id);
            }
        }
    }
}
