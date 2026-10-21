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
        $query = PengajuanAktivitas::with(['user', 'gambarPengajuanAktivitas']);

        // 1. Sorting
        $sortColumn = $filters['sort'] ?? 'updated_at';
        $direction = strtolower($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'judul', 'deadline', 'created_at', 'updated_at', 'status'];

        if (!in_array($sortColumn, $allowedSorts)) {
            $sortColumn = 'updated_at';
        }
        $query->orderBy($sortColumn, $direction);

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
            if ($status === 'disetujui' || $status === 'diterima') {
                $query->whereIn('status', ['disetujui', 'diterima']);
            } else {
                $query->where('status', $status);
            }
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

            // Jika sebelumnya sudah disetujui, jangan gandakan
            if ($pengajuan->status === 'disetujui') {
                $existing = Aktivitas::where('judul', $pengajuan->judul)->first();
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
                'judul'            => $pengajuan->judul,
                'deskripsi'        => $pengajuan->deskripsi,
                'kategori'         => $kategoriArray,
                'partisipan'       => $pengajuan->partisipan ?? 0,
                'jenis'            => 'umum',
                'status'           => 1, // Status aktif agar langsung tampil di List Aktivitas
                'deadline'         => $pengajuan->deadline,
                'link_pendaftaran' => $pengajuan->link_pendaftaran,
            ]);

            // Update status pengajuan menjadi disetujui
            $pengajuan->update([
                'status' => 'disetujui',
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
                            $ctx = stream_context_create([
                                'http' => ['timeout' => 3],
                                'https' => ['timeout' => 3, 'verify_peer' => false, 'verify_peer_name' => false],
                                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                            ]);
                            $fileContents = @file_get_contents($gbr->gambar, false, $ctx);
                            if ($fileContents !== false) {
                                $newFileName = time() . '_' . uniqid() . '.jpg';
                                Storage::disk('public')->put('aktivitas/' . $newFileName, $fileContents);
                                $targetPath = 'storage/aktivitas/' . $newFileName;
                            }
                        } catch (\Throwable $e) {
                            $targetPath = $gbr->gambar;
                        }
                    } else {
                        // B. Jika berupa path file lokal di disk public
                        $cleanPath = ltrim(preg_replace('#^/?storage/#', '', $gbr->gambar), '/');
                        if (Storage::disk('public')->exists($cleanPath)) {
                            $ext = pathinfo($cleanPath, PATHINFO_EXTENSION) ?: 'jpg';
                            $newFileName = time() . '_' . uniqid() . '.' . $ext;
                            $newRawPath = 'aktivitas/' . $newFileName;
                            Storage::disk('public')->copy($cleanPath, $newRawPath);
                            $targetPath = 'storage/' . $newRawPath;
                        } else {
                            $targetPath = str_starts_with($cleanPath, 'storage/') ? $cleanPath : 'storage/' . $cleanPath;
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
     * Pengajuan yang sudah disetujui tidak dapat ditolak kembali.
     */
    public function reject(int $id, ?string $catatan = null)
    {
        return DB::transaction(function () use ($id, $catatan) {
            $pengajuan = PengajuanAktivitas::findOrFail($id);

            if ($pengajuan->status === 'disetujui') {
                abort(422, 'Pengajuan yang sudah disetujui tidak dapat ditolak.');
            }

            $pengajuan->update([
                'status'            => 'ditolak',
                'catatan_penolakan' => $catatan,
            ]);

            return $pengajuan;
        });
    }

    /**
     * Hapus data pengajuan aktivitas beserta file gambar fisiknya.
     */
    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $pengajuan = PengajuanAktivitas::with('gambarPengajuanAktivitas')->findOrFail($id);

            foreach ($pengajuan->gambarPengajuanAktivitas as $gbr) {
                if (!empty($gbr->gambar) && !str_starts_with($gbr->gambar, 'http')) {
                    $clean = ltrim(preg_replace('#^/?storage/#', '', $gbr->gambar), '/');
                    if (Storage::disk('public')->exists($clean)) {
                        Storage::disk('public')->delete($clean);
                    }
                }
            }

            $pengajuan->gambarPengajuanAktivitas()->delete();
            $pengajuan->delete();

            return true;
        });
    }
}
