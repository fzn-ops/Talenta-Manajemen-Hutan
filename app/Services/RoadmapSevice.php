<?php

namespace App\Services;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Roadmap;
use App\Models\BulanRoadmap;
use App\Models\MateriRoadmap;

class RoadmapSevice {
    public function getPaginationRoadmap(array $filters=[], $perPage=10){
        $query = Roadmap::query();

        // 1. Fitur Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama_roadmap', 'like', "%{$search}%");
            });
        }

        // 2. Filter Kategori (VERSI BENER BUAT STRING/VARCHAR)
        if (!empty($filters['kategori'])) {
            // Pecah "Profesional,Akademik" jadi array dan bersihin spasinya
            $kategoriArray = array_map('trim', explode(',', $filters['kategori']));
        
            // Langsung cari yang kategorinya ada di dalam daftar array tersebut
            $query->whereIn('kategori', $kategoriArray);
        }

            return $query->paginate($perPage)->withQueryString();
    }

    public function getCategoryCounts() 
    {
        return Roadmap::select('kategori', DB::raw('count(*) as total'))
            ->groupBy('kategori')
            ->pluck('total', 'kategori');
    }

public function createRoadmap(array $data)
    {
        return DB::transaction(function () use ($data) {
            
            // A. Simpan Gambar kalau ada
            $pathGambar = null;
            if (isset($data['gambar']) && $data['gambar'] instanceof \Illuminate\Http\UploadedFile) {
                $pathGambar = $data['gambar']->store('roadmap_images', 'public');
            }

            // B. Buat Roadmap-nya
            $roadmap = Roadmap::create([
                'nama_roadmap'      => $data['nama_roadmap'],
                'gambar'     => $pathGambar,
                'kategori'   => $data['kategori'] ?? null, 
                'partisipan' => $data['partisipan'] ?? 0,
            ]);

            // C. OTOMATIS Buat 1 Bulan (Bulan ke-1)
            $bulan = $roadmap->bulanRoadmap()->create([
                'bulan_ke' => 1
            ]);

            // D. OTOMATIS Buat 1 Materi awal sebagai "Draft/Pengantar"
            MateriRoadmap::create([
                'id_bulan_roadmap' => $bulan->id,
                'tipe'             => 1,         
                'topik'            => 'Pengantar',   
                'judul'            => 'Pendahuluan ' . $roadmap->judul,
                'kartu_konten'     => null,
                'maksimal_tugas'   => 0,
                'deskripsi'        => '<p>Materi awal untuk roadmap ini. Silakan diedit melalui menu Materi.</p>',
            ]);

            return $roadmap;
        });
    }


    // ==========================================
    // 2. UPDATE ROADMAP
    // ==========================================
    public function updateRoadmap($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $roadmap = Roadmap::findOrFail($id);

            // A. Cek apakah user upload gambar baru
            if (isset($data['gambar']) && $data['gambar'] instanceof \Illuminate\Http\UploadedFile) {
                // Hapus gambar lama di storage biar nggak nyampah
                if ($roadmap->gambar) {
                    Storage::disk('public')->delete($roadmap->gambar);
                }
                // Simpan gambar baru ke array data
                $data['gambar'] = $data['gambar']->store('roadmap_images', 'public');
            } else {
                unset($data['gambar']); 
            }

            $roadmap->update($data);

            return $roadmap;
        });
    }


    // ==========================================
    // 3. DELETE ROADMAP
    // ==========================================
    public function deleteRoadmap($id)
    {
        return DB::transaction(function () use ($id) {
            $roadmap = Roadmap::with('bulanRoadmap.materiRoadmap')->findOrFail($id);

            if ($roadmap->gambar) {
                Storage::disk('public')->delete($roadmap->gambar);
            }

            foreach ($roadmap->bulanRoadmap as $bulan) {
                $bulan->materiRoadmap()->delete(); 
            }
        
            $roadmap->bulanRoadmap()->delete(); 

            return $roadmap->delete();
        });
    }
    
}