<?php

namespace App\Services;

use App\Models\PendaftaranAktivitas;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PendaftaranAktivitasService {
    
    public function getDataPaginationAdmin($filters = [], $perPage = 10)
    {
        $query = PendaftaranAktivitas::with(['peserta', 'activity']);

        // 1. Filter Pencarian Teks (Nama Tim, Judul Form, atau Judul Aktivitas)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama_tim', 'like', "%{$search}%")
                  ->orWhereHas('activity', function($qAktivitas) use ($search) {
                      $qAktivitas->where('judul', 'like', "%{$search}%");
                  });
            });
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['sort']) && !empty($filters['direction'])) {
            $direction = strtolower($filters['direction']) === 'desc' ? 'desc' : 'asc';
            if ($filters['sort'] === 'nama_tim') {
                $query->orderBy('nama_tim', $direction);
            } elseif (in_array($filters['sort'], ['id', 'created_at', 'updated_at', 'status'])) {
                $query->orderBy($filters['sort'], $direction);
            } else {
                $query->orderBy('updated_at', $direction);
            }
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    // 2. Fungsi pas Admin klik tombol "Setujui"
    public function Approval($id)
    {
        $pendaftaran = PendaftaranAktivitas::findOrFail($id);
        
        $pendaftaran->update([
            'status' => 'Disetujui'
        ]);

        $pendaftaran->activity()->increment('partisipan');

        return $pendaftaran;
    }

    // 3. Fungsi pas Admin klik tombol "Tolak" (Beserta alasannya)
    public function Rejected($id, $catatanPenolakan)
    {
        $pendaftaran = PendaftaranAktivitas::findOrFail($id);
        
        $pendaftaran->update([
            'status' => 'Ditolak', 
            'catatan_penolakan' => $catatanPenolakan 
        ]);

        return $pendaftaran;
    }

    //Mahasiswa 
public function getDataPaginationMahasiswa($userId, array $filters = [], $perPage = 10)
    {
        // 1. Tarik data pendaftaran milik mahasiswa ini
        $query = PendaftaranAktivitas::with(['activity.gambarAktivitas'])
            ->whereHas('peserta', function($q) use ($userId) {
                $q->where('users.id', $userId);
            });

        // Filter 1: Search (Nyari judul atau deskripsi di tabel aktivitas)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('activity', function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Filter 2: Jenis (Lomba / Umum) - INI YANG BIKIN ERROR LU TADI!
        if (!empty($filters['jenis'])) {
            $jenis = $filters['jenis'];
            $query->whereHas('activity', function($q) use ($jenis) {
                $q->where('jenis', $jenis);
            });
        }

        // Filter 3: Kategori (Profesional, Bisnis, dll)
        if (!empty($filters['kategori'])) {
            $kategoriArray = explode(',', $filters['kategori']);
            $query->whereHas('activity', function($q) use ($kategoriArray) {
                foreach ($kategoriArray as $kat) {
                    $q->whereJsonContains('kategori', trim($kat));
                }
            });
        }

        // Filter 4: Status Pendaftaran (Menunggu, Diterima, Ditolak)
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        // Sorting Default Terbaru di atas
        $query->orderBy('created_at', 'desc');
        
        return $query->paginate($perPage)->withQueryString();
    }

    public function getDetailPendaftaranMahasiswa($idAktivitas, $userId)
    {
        // Cari apakah mahasiswa ini (berdasarkan $userId) pernah daftar di $idAktivitas ini
        return PendaftaranAktivitas::with('peserta')
            ->where('id_aktivitas', $idAktivitas)
            ->whereHas('peserta', function($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->first();
    }

    // 4. Fungsi Buat Nambah Data Baru (Create)
    public function createData(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                // Set default status (Pake huruf kapital biar rapi di UI)
                if (empty($data['status'])) {
                    $data['status'] = 'Menunggu'; 
                }

                // 1. Insert ke tabel pendaftaran_aktivitas secara spesifik
                $pendaftaran = PendaftaranAktivitas::create([
                    'id_aktivitas'      => $data['id_aktivitas'],
                    'nama_tim'          => $data['nama_tim'] ?? null,
                    'bukti_pendaftaran' => $data['bukti_pendaftaran'] ?? null,
                    'catatan_penolakan' => $data['catatan_penolakan'] ?? null,
                    'status'            => $data['status'],
                ]);

                // 2. Insert ke tabel pivot (pendaftaran_peserta)
                if (!empty($data['peserta_ids'])) {
                    $pendaftaran->peserta()->attach($data['peserta_ids']);
                }

                return $pendaftaran;
            });
        } catch (Exception $e) {
            Log::error('Gagal Create Pendaftaran: ' . $e->getMessage());
            throw $e;
        }
    }

    // 5. Fungsi Buat Ngubah Data (Update)
    public function updateData($id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $pendaftaran = PendaftaranAktivitas::findOrFail($id);

                // 1. Kalau ada update file bukti pendaftaran baru, hapus file fisik yang lama
                if (isset($data['bukti_pendaftaran']) && $pendaftaran->bukti_pendaftaran) {
                    Storage::disk('public')->delete($pendaftaran->bukti_pendaftaran);
                }

                $pesertaIds = $data['peserta_ids'] ?? null;
                unset($data['peserta_ids']); 

                // 2. Update data utama (sekarang array $data udah bersih dari peserta_ids)
                $pendaftaran->update($data);

                // 3. Update relasi peserta (Sync otomatis hapus yang lama & insert yang baru)
                if ($pesertaIds !== null) {
                    $pendaftaran->peserta()->sync($pesertaIds);
                }

                return $pendaftaran;
            });
        } catch (Exception $e) {
            Log::error('Gagal Update Pendaftaran ID ' . $id . ': ' . $e->getMessage());
            throw $e;
        }
    }

    // 6. Fungsi Buat Ngapus Data (Delete)
    public function delete($id)
    {
        $pendaftaran = PendaftaranAktivitas::findOrFail($id);

        if (!empty($pendaftaran->bukti_pendaftaran) && !str_starts_with($pendaftaran->bukti_pendaftaran, 'http')) {
            $cleanPath = ltrim(preg_replace('#^/?storage/#', '', $pendaftaran->bukti_pendaftaran), '/');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($cleanPath);
            }
        }

        // Hapus relasi pivot peserta jika ada
        $pendaftaran->peserta()->detach();
        $pendaftaran->delete();

        return true;
    }
}
