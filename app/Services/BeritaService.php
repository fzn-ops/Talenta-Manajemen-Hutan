<?php

namespace App\Services;

use App\Models\Berita;
use Illuminate\Support\Facades\Storage;

class BeritaService
{
    // 1. READ (Pagination & Search)
    public function getPaginatedBerita($search, $bulan = null, $sort = null, $direction = 'desc', $perPage = 10)
    {
        return Berita::when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            })
            ->when($bulan, function ($query, $bulan) {
                $parts = explode('-', $bulan);
                if (count($parts) === 2) {
                    $query->whereYear('created_at', $parts[0])
                          ->whereMonth('created_at', $parts[1]);
                }
            })
            ->when($sort, function ($query) use ($sort, $direction) {
                $query->orderBy($sort, $direction ?? 'desc');
            }, function ($query) {
                $query->latest(); // Default kalau lagi nggak nge-sort
            })
            ->paginate($perPage)
            ->withQueryString();
    }

    // 2. CREATE
    public function createBerita(array $data)
    {
        // Handle upload foto kalau ada
        if (isset($data['foto']) && $data['foto'] instanceof \Illuminate\Http\UploadedFile) {
            $fileName = time() . '_' . uniqid() . '.' . $data['foto']->getClientOriginalExtension();
            $path = $data['foto']->storeAs('berita', $fileName, 'public');
            $data['foto'] = 'storage/' . $path; // Simpan path ke DB
        }

        return Berita::create($data);
    }

    // 3. UPDATE
    public function updateBerita($id, array $data)
    {
        $berita = Berita::findOrFail($id);

        if (isset($data['foto']) && $data['foto'] instanceof \Illuminate\Http\UploadedFile) {
            // Hapus foto lama dari storage kalau ada
            if ($berita->foto) {
                $oldPath = str_replace('storage/', '', $berita->foto);
                Storage::disk('public')->delete($oldPath);
            }

            // Upload foto baru
            $fileName = time() . '_' . uniqid() . '.' . $data['foto']->getClientOriginalExtension();
            $path = $data['foto']->storeAs('berita', $fileName, 'public');
            $data['foto'] = 'storage/' . $path;
        } else {
            // Kalau gak ada upload file baru, hapus 'foto' dari array biar foto lama gak tertimpa null
            unset($data['foto']);
        }

        $berita->update($data);
        return $berita;
    }

    // 4. DELETE
    public function deleteBerita($id)
    {
        $berita = Berita::findOrFail($id);

        // Bakar foto fisiknya dari folder storage
        if ($berita->foto) {
            $path = str_replace('storage/', '', $berita->foto);
            Storage::disk('public')->delete($path);
        }

        // Hapus datanya dari DB
        $berita->delete();
        return true;
    }
}