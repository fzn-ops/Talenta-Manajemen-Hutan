<?php

namespace App\Services;

use App\Models\Karir;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KarirService
{
    /**
     * Ambil semua karir tanpa pagination.
     */

    public function getKarirById($id){
        return Karir::findOrFail($id);
    }

    public function getAllKarir()
    {
        return Karir::orderBy('created_at', 'desc')->get();
    }

    /**
     * Ambil data karir dengan filter, pencarian, pengurutan, dan pagination dari database.
     */
    public function getPaginationKarir(array $filters = [], $perPage = 10)
    {
        $query = Karir::query();

    if (!empty($filters['sort']) && $filters['sort'] === 'segera_berakhir') {
        $query->whereDate('deadline', '>=', now())
              ->orderBy('deadline', 'asc');
    } else {
        if (!empty($filters['sort']) && !empty($filters['direction'])) {
            $sortColumn = $filters['sort'];
            if ($sortColumn === 'instansi') {
                $sortColumn = 'nama_instansi';
            }
            $direction = strtolower($filters['direction']) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortColumn, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

        // 2. Pencarian (Search)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('posisi', 'like', "%{$search}%")
                  ->orWhere('nama_instansi', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('kualifikasi', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['kategori'])) {
            $kategoriArray = is_array($filters['kategori']) ? $filters['kategori'] : explode(',', $filters['kategori']);
            $kategoriArray = array_filter(array_map('trim', $kategoriArray));
            if (!empty($kategoriArray)) {
                $query->whereIn('kategori', $kategoriArray);
            }
        }

        // 4. Filter Waktu (Deadline)
        if (!empty($filters['waktu'])) {
            $query->whereDate('deadline', $filters['waktu']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Simpan karir baru ke database beserta file logo ke storage public.
     */
    public function createKarir(array $data)
    {
        return DB::transaction(function () use ($data) {
            $logoFile = $data['logo'] ?? null;
            unset($data['logo']);

            if ($logoFile instanceof \Illuminate\Http\UploadedFile && $logoFile->isValid()) {
                $fileName = time() . '_' . uniqid() . '.' . $logoFile->getClientOriginalExtension();
                $path = $logoFile->storeAs('karir', $fileName, 'public');
                $data['foto'] = 'storage/' . $path;
            } elseif (is_string($logoFile)) {
                $data['foto'] = $logoFile;
            } else {
                $data['foto'] = 'logo-default.jpg';
            }

            if (!empty($data['instansi']) && empty($data['nama_instansi'])) {
                $data['nama_instansi'] = $data['instansi'];
            }
            unset($data['instansi']);

            return Karir::create($data);
        });
    }

    /**
     * Perbarui data karir dan ganti file logo jika ada logo baru yang diunggah.
     */
    public function updateKarir($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $karir = Karir::findOrFail($id);

            $logoFile = $data['logo'] ?? null;
            unset($data['logo']);

            if ($logoFile instanceof \Illuminate\Http\UploadedFile && $logoFile->isValid()) {
                // Hapus foto lama jika tersimpan di disk public
                if (!empty($karir->foto) && str_starts_with($karir->foto, 'storage/')) {
                    $oldPath = str_replace('storage/', '', $karir->foto);
                    Storage::disk('public')->delete($oldPath);
                }

                $fileName = time() . '_' . uniqid() . '.' . $logoFile->getClientOriginalExtension();
                $path = $logoFile->storeAs('karir', $fileName, 'public');
                $data['foto'] = 'storage/' . $path;
            }

            if (!empty($data['instansi'])) {
                $data['nama_instansi'] = $data['instansi'];
            }
            unset($data['instansi']);

            $karir->update($data);

            return $karir;
        });
    }

    /**
     * Hapus karir dari database beserta file logo dari storage.
     */
    public function deleteKarir($id)
    {
        return DB::transaction(function () use ($id) {
            $karir = Karir::findOrFail($id);

            if (!empty($karir->foto) && str_starts_with($karir->foto, 'storage/')) {
                $path = str_replace('storage/', '', $karir->foto);
                Storage::disk('public')->delete($path);
            }

            $karir->delete();

            return true;
        });
    }
}
