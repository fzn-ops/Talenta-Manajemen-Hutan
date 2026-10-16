<?php

namespace App\Services;

use App\Models\Aktivitas;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\GambarAktivitas;

class AktivitasService
{
    
    public function getAllAktivitas($status = null)
    {
        $query = Aktivitas::with('gambarAktivitas')->orderBy('created_at', 'desc');

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        return $query->get();
    }
    /**
     * Ambil semua aktivitas dengan fitur Search, Filter, dan Pagination.
     */
    public function getPaginationAktivitas(array $filters = [], $perPage = 10)
    {
        $query = Aktivitas::with('gambarAktivitas');

        if (!empty($filters['sort']) && !empty($filters['direction'])) {
            $allowedSorts = ['id', 'judul', 'deadline', 'created_at'];
            $sortColumn = in_array($filters['sort'], $allowedSorts) ? $filters['sort'] : 'id';
            $direction = strtolower($filters['direction']) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortColumn, $direction);
        } else {
            // Urutan default kalau baru buka halaman pertama kali
            $query->orderBy('id', 'desc'); 
        }

        // 1. Fitur Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // 2. Filter Jenis
        if (!empty($filters['jenis'])) {
            $query->where('jenis', $filters['jenis']);
        }

        // 3. Filter Status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', (bool) $filters['status']);
        }
        // 4. Filter Kategori (Array JSON) - Logika "AND" (Harus punya semua kategori yang dicentang)
        if (!empty($filters['kategori'])) {
            // Pecah string "Birokrat,Bisnis" menjadi array
            $kategoriArray = explode(',', $filters['kategori']);
            foreach ($kategoriArray as $kat) {
                $query->whereJsonContains('kategori', trim($kat));
            }
        }

        // 5. Filter Waktu / Deadline (Mencari tanggal spesifik: YYYY-MM-DD)
        if (!empty($filters['waktu'])) {
            $query->whereDate('deadline', $filters['waktu']);
        }
        return $query->paginate($perPage)->withQueryString(); 
    }

    public function createAktivitas(array $data)
    {
        return DB::transaction(function () use ($data) {
            
            // Ambil array gambar
            $gambarFiles = $data['gambar'] ?? [];
            unset($data['gambar']); // Buang dari array aktivitas utama
            
            // Simpan data aktivitas utama
            $aktivitas = Aktivitas::create($data);

            // Proses Upload Gambar
            if (!empty($gambarFiles)) {
                $gambarRecords = [];
                
                foreach ($gambarFiles as $index => $file) {
                    // Pastikan objek yang masuk benar-benar instance dari UploadedFile
                    if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                        
                        // Buat nama file unik
                        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        
                        // SIMPAN KE STORAGE: Gunakan disk 'public' secara eksplisit
                        // Ini akan masuk ke storage/app/public/aktivitas/namafile.jpg
                        $path = $file->storeAs('aktivitas', $fileName, 'public');
                        
                        // Path publik untuk disimpan ke database: 'storage/aktivitas/namafile.jpg'
                        $publicPath = 'storage/' . $path;

                        $gambarRecords[] = [
                            'id_aktivitas' => $aktivitas->id,
                            'gambar'       => $publicPath,
                            'utama'        => $index === 0 ? true : false,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ];
                    }
                }
                if (!empty($gambarRecords)) {
                    GambarAktivitas::insert($gambarRecords);
                }
            }
            return $aktivitas;
        });
    }

    /**
     * Update data aktivitas dasar.
     */
public function updateAktivitas($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            
            $aktivitas = Aktivitas::findOrFail($id);
            
            // 1. Pisahkan data gambar dari data utama
            $gambarFiles = $data['gambar'] ?? [];
            $deletedImages = $data['deleted_images'] ?? [];
            
            unset($data['gambar']);
            unset($data['deleted_images']);

            // 2. Update kolom-kolom aktivitas utama
            $aktivitas->update($data);

            // 3. HAPUS GAMBAR LAMA
            if (!empty($deletedImages)) {
                // Cari gambar di database yang ID-nya ada di array deletedImages
                $gambarLama = GambarAktivitas::whereIn('id', $deletedImages)->get();
                
                foreach ($gambarLama as $gl) {
                    // Buang tulisan 'storage/' biar ketemu nama foldernya, lalu hapus dari disk public
                    $path = str_replace('storage/', '', $gl->gambar); 
                    Storage::disk('public')->delete($path);
                    
                    // Hapus baris datanya dari tabel gambar_aktivitas
                    $gl->delete();
                }
            }

            // 4. UPLOAD GAMBAR BARU (Kalau user nambahin gambar)
            if (!empty($gambarFiles)) {
                $gambarRecords = [];
                
                // Cek apakah aktivitas ini masih punya gambar utama (utama = 1)
                $hasUtama = GambarAktivitas::where('id_aktivitas', $id)->where('utama', true)->exists();

                foreach ($gambarFiles as $index => $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                        
                        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        // Simpan ke storage/app/public/aktivitas
                        $path = $file->storeAs('aktivitas', $fileName, 'public'); 
                        
                        $publicPath = 'storage/' . $path;

                        $gambarRecords[] = [
                            'id_aktivitas' => $aktivitas->id,
                            'gambar'       => $publicPath,
                            'utama'        => (!$hasUtama && $index === 0) ? true : false, 
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ];
                    }
                }
                if (!empty($gambarRecords)) {
                    GambarAktivitas::insert($gambarRecords);
                }
            }

            return $aktivitas;
        });
    }

    /**
     * Hapus aktivitas beserta gambar dari storage dan database.
     */
public function deleteAktivitas($id)
    {
        return DB::transaction(function () use ($id) {
            $aktivitas = Aktivitas::with('gambarAktivitas')->findOrFail($id);

            // 1. Hapus semua file fisik gambar yang terkait dari storage
            foreach ($aktivitas->gambarAktivitas as $gambar) {
                $path = str_replace('storage/', '', $gambar->gambar);
                Storage::disk('public')->delete($path);
            }

            // 2. Hapus relasi gambar di database (jika cascade belum aktif)
            $aktivitas->gambarAktivitas()->delete();

            // 3. Hapus data aktivitas utama
            $aktivitas->delete();

            return true;
        });
    }
}