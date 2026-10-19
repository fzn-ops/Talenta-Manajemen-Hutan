<?php

namespace App\Services;

use App\Models\PengajuanAktivitas;
use App\Models\Aktivitas;
use App\Models\GambarAktivitas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PersetujuanAktivitasService
{
    /**
     * Ambil data pengajuan aktivitas dengan search, filter, sorting, dan pagination.
     */
    public function getPaginatedPengajuan(array $filters = [], int $perPage = 10)
    {
        $query = PengajuanAktivitas::with(['user', 'gambarPengajuanAktivitas', 'aktivitas']);

        // 1. Sorting
        if (!empty($filters['sort']) && !empty($filters['direction'])) {
            $allowedSorts = ['id', 'judul', 'deadline', 'created_at', 'status'];
            $sortColumn = in_array($filters['sort'], $allowedSorts) ? $filters['sort'] : 'id';
            $direction = strtolower($filters['direction']) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortColumn, $direction);
        } else {
            $query->orderBy('id', 'asc');
        }

        // 2. Pencarian (Search)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        // 3. Filter Status
        if (!empty($filters['status'])) {
            $status = strtolower($filters['status']);
            $query->where('status', $status);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Setujui pengajuan aktivitas:
     * 1. Ubah status pengajuan menjadi 'disetujui' dan tautkan id_aktivitas
     * 2. Buat record baru di tabel `aktivitas` dengan status aktif (1)
     * 3. Salin/unduh gambar ke storage lokal `storage/aktivitas/` dan hubungkan ke tabel `gambar_aktivitas`
     * Sehingga langsung muncul di list aktivitas admin dan public dengan gambar yang valid.
     */
    public function approve(int $id)
    {
        return DB::transaction(function () use ($id) {
            $pengajuan = PengajuanAktivitas::with('gambarPengajuanAktivitas')->findOrFail($id);

            // Jika sebelumnya sudah disetujui dan id_aktivitas masih ada, jangan gandakan
            if ($pengajuan->status === 'disetujui' && $pengajuan->id_aktivitas) {
                $existing = Aktivitas::find($pengajuan->id_aktivitas);
                if ($existing) {
                    return $existing;
                }
            }

            // Normalisasi kategori ke bentuk array
            $kategoriArray = [];
            if (is_array($pengajuan->kategori)) {
                $kategoriArray = $pengajuan->kategori;
            } elseif (!empty($pengajuan->kategori)) {
                $kategoriArray = array_values(array_filter(array_map('trim', explode(',', $pengajuan->kategori))));
            }

            // Simpan ke tabel aktivitas utama
            $aktivitas = Aktivitas::create([
                'judul'      => $pengajuan->judul,
                'deskripsi'  => $pengajuan->deskripsi,
                'kategori'   => $kategoriArray,
                'partisipan' => $pengajuan->partisipan ?? 0,
                'jenis'      => 'umum',
                'status'     => 1, // Status aktif agar langsung tampil di List Aktivitas
                'deadline'   => $pengajuan->deadline,
            ]);

            // Update status pengajuan dan hubungkan ke aktivitas yang baru dibuat
            $pengajuan->update([
                'status'       => 'disetujui',
                'id_aktivitas' => $aktivitas->id,
            ]);

            // Pastikan direktori aktivitas di storage public tersedia
            if (!Storage::disk('public')->exists('aktivitas')) {
                Storage::disk('public')->makeDirectory('aktivitas');
            }

            // Salin atau unduh gambar terkait jika ada
            if ($pengajuan->gambarPengajuanAktivitas && $pengajuan->gambarPengajuanAktivitas->isNotEmpty()) {
                $gambarRecords = [];
                $hasUtama = false;

                foreach ($pengajuan->gambarPengajuanAktivitas as $index => $gbr) {
                    $targetPath = $gbr->gambar;

                    // A. Jika berupa URL eksternal (misal dari seeder Unsplash)
                    if (filter_var($gbr->gambar, FILTER_VALIDATE_URL)) {
                        try {
                            $fileContents = @file_get_contents($gbr->gambar);
                            if ($fileContents !== false) {
                                $newFileName = time() . '_' . uniqid() . '.jpg';
                                Storage::disk('public')->put('aktivitas/' . $newFileName, $fileContents);
                                $targetPath = 'storage/aktivitas/' . $newFileName;
                            }
                        } catch (\Throwable $e) {
                            // Jika unduh gagal, gunakan target URL langsung
                            $targetPath = $gbr->gambar;
                        }
                    } else {
                        // B. Jika berupa path file lokal di disk public
                        $rawPath = str_replace('storage/', '', $gbr->gambar);
                        if (Storage::disk('public')->exists($rawPath)) {
                            $ext = pathinfo($rawPath, PATHINFO_EXTENSION) ?: 'jpg';
                            $newFileName = time() . '_' . uniqid() . '.' . $ext;
                            $newRawPath = 'aktivitas/' . $newFileName;
                            Storage::disk('public')->copy($rawPath, $newRawPath);
                            $targetPath = 'storage/' . $newRawPath;
                        }
                    }

                    $isUtama = (bool) $gbr->utama;
                    if ($isUtama) {
                        $hasUtama = true;
                    } elseif ($index === 0 && !$hasUtama) {
                        $isUtama = true;
                        $hasUtama = true;
                    }

                    $gambarRecords[] = [
                        'id_aktivitas' => $aktivitas->id,
                        'gambar'       => $targetPath,
                        'utama'        => $isUtama,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }

                if (!empty($gambarRecords)) {
                    GambarAktivitas::insert($gambarRecords);
                }
            }

            return $aktivitas;
        });
    }

    /**
     * Tolak pengajuan aktivitas beserta catatan penolakan.
     * Jika aktivitas ini sebelumnya sudah disetujui (dan masuk ke list aktivitas),
     * aksi tolak ini akan otomatis mencabut/menghapus record aktivitas terkait dari list aktivitas
     * sehingga jika admin salah menyetujui, hal tersebut TIDAK FATAL.
     */
    public function reject(int $id, ?string $catatan = null)
    {
        return DB::transaction(function () use ($id, $catatan) {
            $pengajuan = PengajuanAktivitas::findOrFail($id);

            // Jika sebelumnya sudah disetujui dan ada aktivitas terkait, hapus aktivitas tersebut
            if ($pengajuan->id_aktivitas) {
                $linkedAktivitas = Aktivitas::with('gambarAktivitas')->find($pengajuan->id_aktivitas);
                if ($linkedAktivitas) {
                    // Hapus file fisik gambar terkait jika ada di disk public
                    foreach ($linkedAktivitas->gambarAktivitas as $gAktivitas) {
                        $rawPath = str_replace('storage/', '', $gAktivitas->gambar);
                        if (Storage::disk('public')->exists($rawPath)) {
                            Storage::disk('public')->delete($rawPath);
                        }
                    }
                    $linkedAktivitas->gambarAktivitas()->delete();
                    $linkedAktivitas->delete();
                }
            }

            $pengajuan->update([
                'status'            => 'ditolak',
                'catatan_penolakan' => $catatan,
                'id_aktivitas'      => null,
            ]);

            return $pengajuan;
        });
    }
}
