<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Aktivitas;
use App\Models\PendaftaranAktivitas;

class PendaftaranAktivitasSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. Tarik Data User dari Seeder UserDummy
        // ==========================================
        $fauzan = User::where('nim', 'J0403231085')->first();
        $farhan = User::where('nim', 'J0403231075')->first();

        if (!$fauzan || !$farhan) {
            $this->command->error('User Fauzan & Farhan tidak ditemukan! Pastikan UserDummy sudah dijalankan.');
            return;
        }

        // ==========================================
        // 2. Tarik Data Aktivitas dari AktivitasSeeder
        // ==========================================
        $lomba = Aktivitas::where('judul', 'Lomba Pengembangan Aplikasi')->first();
        $pelatihan = Aktivitas::where('judul', 'Pelatihan Data Science')->first();

        if (!$lomba || !$pelatihan) {
            $this->command->error('Aktivitas tidak ditemukan! Pastikan AktivitasSeeder sudah dijalankan.');
            return;
        }

        // ==========================================
        // 3. BIKIN DATA 1: Status "Menunggu"
        // ==========================================
        $timSatu = PendaftaranAktivitas::create([
            'id_aktivitas'      => $lomba->id,
            'nama_tim'          => 'Tim Biasa Aja',
            'bukti_pendaftaran' => 'bukti/dummy-bukti-1.pdf', // Pura-puranya ini path file PDF
            'status'            => 'Menunggu',
            // catatan_penolakan sengaja kosong
        ]);

        // Masukin Fauzan dan Farhan ke Tim 1
        $timSatu->peserta()->sync([$fauzan->id, $farhan->id]);


        // ==========================================
        // 4. BIKIN DATA 2: Status "Disetujui"
        // ==========================================
        $timDua = PendaftaranAktivitas::create([
            'id_aktivitas'      => $pelatihan->id,
            'nama_tim'          => 'Data Miners IPB',
            'bukti_pendaftaran' => 'bukti/dummy-bukti-2.pdf',
            'status'            => 'Disetujui',
        ]);

        // Masukin Fauzan aja ke Tim 2
        $timDua->peserta()->sync([$fauzan->id]);


        // ==========================================
        // 5. BIKIN DATA 3: Status "Ditolak" 
        // ==========================================
        $timTiga = PendaftaranAktivitas::create([
            'id_aktivitas'      => $lomba->id,
            'nama_tim'          => 'Tim Coba Coba',
            'bukti_pendaftaran' => 'bukti/dummy-bukti-3.pdf',
            'catatan_penolakan' => 'Dokumen bukti pendaftaran buram dan tidak terbaca. Mohon upload ulang dokumen yang lebih jelas.',
            'status'            => 'Ditolak',
        ]);

        // Masukin Farhan aja ke Tim 3
        $timTiga->peserta()->sync([$farhan->id]);
    }
}