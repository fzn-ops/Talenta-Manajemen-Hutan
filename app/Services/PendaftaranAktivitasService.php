<?php

namespace App\Services;

use App\Models\PendaftaranAktivitas;

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
            } else {
                $query->orderBy('id', $direction);
            }
        } else {
            $query->orderBy('id', 'asc');
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
}
