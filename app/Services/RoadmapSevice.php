<?php

namespace App\Services;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Roadmap;
use App\Models\BulanRoadmap;
use App\Models\MateriRoadmap;

class RoadmapSevice {
    public function getPaginationRoadmap(array $filters = [], $perPage = 10)
    {
        $query = Roadmap::with(['bulanRoadmap.materiRoadmap'])->withCount('pendaftaranRoadmap as partisipan');

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

        $query->orderBy('id', 'desc');

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
            $validCategories = ['Profesional', 'Bisnis', 'Birokrasi', 'Akademisi'];
            $rawKategori = trim($data['kategori'] ?? 'Profesional');
            $kategori = 'Profesional';
            foreach ($validCategories as $validCat) {
                if (strcasecmp($validCat, $rawKategori) === 0) {
                    $kategori = $validCat;
                    break;
                }
            }

            $roadmap = Roadmap::create([
                'nama_roadmap' => $data['nama_roadmap'],
                'gambar'       => $pathGambar,
                'kategori'     => $kategori, 
            ]);

            // C. OTOMATIS Buat 1 Bulan (Bulan ke-1)
            $bulan = $roadmap->bulanRoadmap()->create([
                'bulan_ke' => 1
            ]);

            // D. OTOMATIS Buat 1 Materi awal sebagai "Draft/Pengantar"
            MateriRoadmap::create([
                'id_bulan_roadmap' => $bulan->id,
                'tipe'             => 0, // 0 = Materi, 1 = Tugas
                'topik'            => 'Materi 1',   
                'judul'            => 'Pengantar Modul',
                'kartu_konten'     => [
                    [
                        'id'       => (string) time(),
                        'type'     => 'text',
                        'content'  => '<p>Selamat datang di roadmap <strong>' . htmlspecialchars($roadmap->nama_roadmap) . '</strong>. Silakan pelajari materi dan selesaikan tugas-tugas yang telah disediakan.</p>',
                        'fileName' => '',
                    ]
                ],
                'jumlah_aktivitas' => 0,
                'deskripsi'        => 'Materi awal untuk roadmap ini. Silakan diedit melalui menu Materi.',
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

            if (!empty($data['kategori'])) {
                $validCategories = ['Profesional', 'Bisnis', 'Birokrasi', 'Akademisi'];
                $rawKategori = trim($data['kategori']);
                $kategori = 'Profesional';
                foreach ($validCategories as $validCat) {
                    if (strcasecmp($validCat, $rawKategori) === 0) {
                        $kategori = $validCat;
                        break;
                    }
                }
                $data['kategori'] = $kategori;
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

    // ==========================================
    // 4. MAHASISWA: ROADMAP LIST & PROGRESS
    // ==========================================
    public function getMahasiswaRoadmaps(int $userId, array $filters = []): array
    {
        $query = Roadmap::with([
            'bulanRoadmap.materiRoadmap',
            'pendaftaranRoadmap' => function ($q) use ($userId) {
                $q->where('id_user', $userId);
            }
        ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('nama_roadmap', 'like', "%{$search}%");
        }

        if (!empty($filters['kategori'])) {
            $kategoriArray = array_map('trim', explode(',', $filters['kategori']));
            $query->whereIn('kategori', $kategoriArray);
        }

        $roadmaps = $query->orderBy('id', 'asc')->get();

        // Auto-sinkron semua tugas yang disetujui (HasilAktivitas status = disetujui) ke tabel Pencapaian
        $allApprovedMateriIds = \App\Models\HasilAktivitas::where('id_user', $userId)
            ->where(DB::raw('LOWER(status)'), 'disetujui')
            ->pluck('id_materi_roadmap')
            ->unique()
            ->filter()
            ->all();

        foreach ($allApprovedMateriIds as $materiId) {
            \App\Models\Pencapaian::firstOrCreate([
                'id_user'           => $userId,
                'id_materi_roadmap' => $materiId,
            ]);
        }

        // Ambil semua id_materi_roadmap yang telah selesai oleh user ini
        $userPencapaianIds = \App\Models\Pencapaian::where('id_user', $userId)
            ->pluck('id_materi_roadmap')
            ->flip()
            ->all();

        return $roadmaps->map(function ($roadmap) use ($userPencapaianIds) {
            // Ambil semua ID materi dari semua bulan di roadmap ini
            $allMateriIds = $roadmap->bulanRoadmap->flatMap(function ($bulan) {
                return $bulan->materiRoadmap->pluck('id');
            })->all();

            $totalTasks = count($allMateriIds);
            $completedTasks = 0;

            foreach ($allMateriIds as $mId) {
                if (isset($userPencapaianIds[$mId])) {
                    $completedTasks++;
                }
            }

            $categoriesLabel = 'Profesional | Bisnis | Birokrat | Akademisi';

            return [
                'id'               => $roadmap->id,
                'title'            => $roadmap->nama_roadmap,
                'category'         => $roadmap->kategori,
                'categories_label' => $categoriesLabel,
                'task_count'       => $totalTasks > 0 ? $totalTasks : 1,
                'completed_tasks'  => $completedTasks,
                'thumbnail'        => $roadmap->gambar ? (str_starts_with($roadmap->gambar, 'http') ? $roadmap->gambar : '/storage/' . ltrim($roadmap->gambar, '/')) : '',
                'updated_at'       => $roadmap->updated_at ? $roadmap->updated_at->format('d/m/Y') : date('d/m/Y'),
                'is_enrolled'      => $roadmap->pendaftaranRoadmap->isNotEmpty(),
            ];
        })->all();
    }

    /**
     * Dapatkan daftar roadmap yang tersedia untuk modal Tambah Roadmap
     */
    public function getAvailableRoadmapsForUser(int $userId): array
    {
        $enrolledIds = \App\Models\PendaftaranRoadmap::where('id_user', $userId)->pluck('id_roadmap')->toArray();

        return Roadmap::with('bulanRoadmap.materiRoadmap')
            ->withCount('pendaftaranRoadmap as partisipan')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($r) use ($enrolledIds) {
                $totalTask = $r->bulanRoadmap->flatMap->materiRoadmap->count();
                return [
                    'id'          => $r->id,
                    'judul'       => $r->nama_roadmap,
                    'kategori'    => $r->kategori,
                    'totalTask'   => $totalTask,
                    'partisipan'  => $r->partisipan ?? 0,
                    'is_enrolled' => in_array($r->id, $enrolledIds),
                ];
            })
            ->all();
    }

    /**
     * Daftarkan mahasiswa ke roadmap
     */
    public function enrollRoadmaps(int $userId, array $roadmapIds): void
    {
        DB::transaction(function () use ($userId, $roadmapIds) {
            foreach ($roadmapIds as $roadmapId) {
                $exists = \App\Models\PendaftaranRoadmap::where('id_user', $userId)
                    ->where('id_roadmap', $roadmapId)
                    ->exists();

                if (!$exists) {
                    \App\Models\PendaftaranRoadmap::create([
                        'id_user'    => $userId,
                        'id_roadmap' => $roadmapId,
                    ]);
                }
            }
        });
    }
}