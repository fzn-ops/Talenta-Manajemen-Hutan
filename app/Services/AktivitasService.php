<?php

namespace App\Services;

use App\Models\Aktivitas;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        $query = Aktivitas::with('gambarAktivitas')->orderBy('created_at', 'desc');

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

        // 4. Filter Kategori
        if (!empty($filters['kategori'])) {
            $query->whereJsonContains('kategori', $filters['kategori']);
        }

        // 5. Filter Bulan Deadline
        if (!empty($filters['bulan'])) {
            $parts = explode('-', $filters['bulan']);
            if (count($parts) === 2) {
                $query->whereYear('deadline', $parts[0])
                      ->whereMonth('deadline', $parts[1]);
            }
        }
        return $query->paginate($perPage)->withQueryString(); 
    }

    public function createAktivitas(array $data)
    {
        return DB::transaction(function () use ($data) {
            $aktivitas = Aktivitas::create([
                'judul'      => $data['judul'],
                'deskripsi'  => $data['deskripsi'],
                'kategori'   => $data['kategori'], 
                'partisipan' => $data['partisipan'] ?? 0,
                'jenis'      => $data['jenis'] ?? 'umum', 
                'status'     => $data['status'] ?? true,  
                'deadline'   => $data['deadline'],
            ]);

            if (isset($data['gambar']) && is_array($data['gambar'])) {
                foreach ($data['gambar'] as $index => $file) {
                    $path = $file->store('aktivitas', 'public');
                    $aktivitas->gambarAktivitas()->create([
                        'gambar' => $path, 
                        'utama'  => $index === 0 ? true : false, 
                    ]);
                }
            }

            return $aktivitas->load('gambarAktivitas');
        });
    }

    /**
     * Update data aktivitas dasar.
     */
    public function updateAktivitas($id, array $data)
    {
        $aktivitas = Aktivitas::findOrFail($id);
        
        $aktivitas->update([
            'judul'      => $data['judul'] ?? $aktivitas->judul,
            'deskripsi'  => $data['deskripsi'] ?? $aktivitas->deskripsi,
            'kategori'   => $data['kategori'] ?? $aktivitas->kategori,
            'partisipan' => $data['partisipan'] ?? $aktivitas->partisipan,
            'jenis'      => $data['jenis'] ?? $aktivitas->jenis,
            'status'     => $data['status'] ?? $aktivitas->status,
            'deadline'   => $data['deadline'] ?? $aktivitas->deadline,
        ]);

        return $aktivitas;
    }

    /**
     * Hapus aktivitas beserta gambar dari storage dan database.
     */
    public function deleteAktivitas($id)
    {
        $aktivitas = Aktivitas::with('gambarAktivitas')->findOrFail($id);
        
        if ($aktivitas->gambarAktivitas->isNotEmpty()) {
            foreach ($aktivitas->gambarAktivitas as $item) {
                if (Storage::disk('public')->exists($item->gambar)) {
                    Storage::disk('public')->delete($item->gambar);
                }
            }
        }

        return $aktivitas->delete();
    }
}