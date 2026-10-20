<?php

namespace App\Services;

use App\Models\HasilAktivitas;
use Illuminate\Support\Facades\DB;

class HasilAktivitasService
{
    /**
     * Ambil data hasil aktivitas mahasiswa dengan search, filter, sorting, dan pagination.
     */
    public function getPaginatedHasil(array $filters = [], int $perPage = 10)
    {
        $query = HasilAktivitas::with([
            'user',
            'materiRoadmap.bulanRoadmap.roadmap',
            'pendaftaranAktivitas.activity'
        ]);

        // 1. Sorting
        if (!empty($filters['sort']) && !empty($filters['direction'])) {
            $direction = strtolower($filters['direction']) === 'asc' ? 'asc' : 'desc';

            switch ($filters['sort']) {
                case 'nama_mahasiswa':
                    $query->leftJoin('users', 'hasil_aktivitas.id_user', '=', 'users.id')
                          ->orderBy('users.nama', $direction)
                          ->select('hasil_aktivitas.*');
                    break;

                case 'nim':
                    $query->leftJoin('users', 'hasil_aktivitas.id_user', '=', 'users.id')
                          ->orderBy('users.nim', $direction)
                          ->select('hasil_aktivitas.*');
                    break;

                case 'nama_kegiatan':
                    $query->leftJoin('pendaftaran_aktivitas', 'hasil_aktivitas.id_pendaftaran_aktivitas', '=', 'pendaftaran_aktivitas.id')
                          ->leftJoin('aktivitas', 'pendaftaran_aktivitas.id_aktivitas', '=', 'aktivitas.id')
                          ->orderBy('aktivitas.judul', $direction)
                          ->select('hasil_aktivitas.*');
                    break;

                case 'status':
                    $query->orderBy('hasil_aktivitas.status', $direction);
                    break;

                case 'id':
                default:
                    $query->orderBy('hasil_aktivitas.id', $direction);
                    break;
            }
        } else {
            $query->orderBy('hasil_aktivitas.id', 'asc');
        }

        // 2. Pencarian (Search)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('nama', 'like', "%{$search}%")
                       ->orWhere('nim', 'like', "%{$search}%");
                })
                ->orWhereHas('pendaftaranAktivitas.activity', function ($aq) use ($search) {
                    $aq->where('judul', 'like', "%{$search}%");
                })
                ->orWhereHas('materiRoadmap', function ($mq) use ($search) {
                    $mq->where('judul', 'like', "%{$search}%");
                })
                ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // 3. Filter Status
        if (!empty($filters['status'])) {
            $status = strtolower($filters['status']);
            $query->where('hasil_aktivitas.status', $status);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Setujui hasil aktivitas mahasiswa.
     */
    public function approve(int $id)
    {
        return DB::transaction(function () use ($id) {
            $hasil = HasilAktivitas::findOrFail($id);

            $hasil->update([
                'status' => 'disetujui',
            ]);

            return $hasil;
        });
    }

    /**
     * Tolak hasil aktivitas mahasiswa dengan catatan penolakan.
     */
    public function reject(int $id, ?string $catatan = null)
    {
        return DB::transaction(function () use ($id, $catatan) {
            $hasil = HasilAktivitas::findOrFail($id);

            $hasil->update([
                'status'            => 'ditolak',
                'catatan_penolakan' => $catatan,
            ]);

            return $hasil;
        });
    }

    /**
     * Hapus data hasil aktivitas mahasiswa.
     */
    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $hasil = HasilAktivitas::findOrFail($id);

            if (!empty($hasil->media)) {
                $medias = is_array($hasil->media) ? $hasil->media : [$hasil->media];
                foreach ($medias as $file) {
                    if (is_string($file) && !str_starts_with($file, 'http')) {
                        $clean = ltrim(preg_replace('#^/?storage/#', '', $file), '/');
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($clean)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($clean);
                        }
                    }
                }
            }

            $hasil->delete();

            return true;
        });
    }
}
