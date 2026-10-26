<?php

namespace App\Http\Controllers\Dashboard\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\HasilAktivitas;
use App\Models\Pencapaian;
use App\Models\PendaftaranAktivitas;
use App\Models\PendaftaranRoadmap;
use App\Models\Roadmap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman utama Dashboard Mahasiswa dengan data aktual dari database.
     */
    public function index(Request $request): Response
    {
        $userId = (int) Auth::id();

        // =========================================================================
        // 1. STATS: COUNT AKTIVITAS YANG DIIKUTI DENGAN STATUS ACCEPTED/DISETUJUI
        // =========================================================================
        $acceptedRegistrations = PendaftaranAktivitas::with('activity')
            ->whereHas('peserta', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->whereIn(DB::raw('LOWER(status)'), ['disetujui', 'accepted'])
            ->get();

        $distinctActivities = $acceptedRegistrations->pluck('activity')->filter()->unique('id');

        $normalizeCategory = function (string $cat): string {
            $c = strtolower(trim($cat));
            if ($c === 'birokrat') $c = 'birokrasi';
            if ($c === 'professional') $c = 'profesional';
            if ($c === 'akademik') $c = 'akademisi';
            return $c;
        };

        $profesionalCount = 0;
        $bisnisCount = 0;
        $birokratCount = 0;
        $akademisiCount = 0;

        foreach ($distinctActivities as $activity) {
            $rawKategori = $activity->kategori;
            $catArray = [];

            if (is_array($rawKategori)) {
                $catArray = $rawKategori;
            } elseif (is_string($rawKategori)) {
                $decoded = json_decode($rawKategori, true);
                if (is_array($decoded)) {
                    $catArray = $decoded;
                } else {
                    $catArray = array_map('trim', explode(',', $rawKategori));
                }
            }

            $normalizedList = array_map($normalizeCategory, $catArray);

            if (in_array('profesional', $normalizedList, true)) {
                $profesionalCount++;
            }
            if (in_array('bisnis', $normalizedList, true)) {
                $bisnisCount++;
            }
            if (in_array('birokrasi', $normalizedList, true)) {
                $birokratCount++;
            }
            if (in_array('akademisi', $normalizedList, true)) {
                $akademisiCount++;
            }
        }

        $stats = [
            'profesional'   => $profesionalCount,
            'bisnis'        => $bisnisCount,
            'birokrat'      => $birokratCount,
            'akademisi'     => $akademisiCount,
            'totalKegiatan' => $distinctActivities->count(),
        ];

        // =========================================================================
        // 2. KARTU ROADMAP YANG DIIKUTI: LAST VISIT / TERBARU (MAX 3)
        // =========================================================================
        $enrolledPivots = PendaftaranRoadmap::where('id_user', $userId)
            ->with(['roadmap.bulanRoadmap.materiRoadmap'])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(3)
            ->get();

        // Auto-sinkron tugas yang telah disetujui (HasilAktivitas status = disetujui) ke Pencapaian
        $allApprovedMateriIds = HasilAktivitas::where('id_user', $userId)
            ->where(DB::raw('LOWER(status)'), 'disetujui')
            ->pluck('id_materi_roadmap')
            ->unique()
            ->filter()
            ->all();

        foreach ($allApprovedMateriIds as $materiId) {
            Pencapaian::firstOrCreate([
                'id_user'           => $userId,
                'id_materi_roadmap' => $materiId,
            ]);
        }

        $userPencapaianIds = Pencapaian::where('id_user', $userId)
            ->pluck('id_materi_roadmap')
            ->flip()
            ->all();

        $enrolledRoadmaps = [];
        foreach ($enrolledPivots as $pivot) {
            $roadmap = $pivot->roadmap;
            if (!$roadmap) continue;

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

            $thumbnailUrl = '';
            if ($roadmap->gambar) {
                $thumbnailUrl = str_starts_with($roadmap->gambar, 'http')
                    ? $roadmap->gambar
                    : '/storage/' . ltrim($roadmap->gambar, '/');
            }

            $enrolledRoadmaps[] = [
                'id'               => $roadmap->id,
                'title'            => $roadmap->nama_roadmap,
                'category'         => $roadmap->kategori,
                'categories_label' => 'Profesional | Bisnis | Birokrat | Akademisi',
                'task_count'       => $totalTasks > 0 ? $totalTasks : 1,
                'completed_tasks'  => $completedTasks,
                'thumbnail'        => $thumbnailUrl,
                'updated_at'       => $roadmap->updated_at ? $roadmap->updated_at->format('d/m/Y') : date('d/m/Y'),
                'is_enrolled'      => true,
            ];
        }

        // =========================================================================
        // 3. KARTU AKTIVITAS TERBARU (MAX 4)
        // Tampilkan 4 aktivitas terbaru dari seluruh kategori dan tipe
        // =========================================================================
        $latestActivities = Aktivitas::with('gambarAktivitas')
            ->where('status', 1)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(4)
            ->get();

        if ($latestActivities->isEmpty()) {
            $latestActivities = Aktivitas::with('gambarAktivitas')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->take(4)
                ->get();
        }

        $latestActivities = $latestActivities
            ->map(function ($act) {
                $firstGambar = $act->gambarAktivitas->firstWhere('utama', 1)?->gambar
                    ?? $act->gambarAktivitas->first()?->gambar;

                $gambarUrl = '';
                if ($firstGambar) {
                    if (str_starts_with($firstGambar, 'http')) {
                        $gambarUrl = $firstGambar;
                    } else {
                        $clean = preg_replace('#^/?(public/|storage/)+#', '', $firstGambar);
                        $gambarUrl = '/storage/' . $clean;
                    }
                }

                $rawKategori = $act->kategori;
                $catArray = [];
                if (is_array($rawKategori)) {
                    $catArray = $rawKategori;
                } elseif (is_string($rawKategori)) {
                    $decoded = json_decode($rawKategori, true);
                    $catArray = is_array($decoded) ? $decoded : array_map('trim', explode(',', $rawKategori));
                }

                return [
                    'id'        => $act->id,
                    'judul'     => $act->judul,
                    'deskripsi' => $act->deskripsi,
                    'deadline'  => $act->deadline,
                    'peserta'   => $act->partisipan ?? 0,
                    'gambar'    => $gambarUrl,
                    'jenis'     => ucfirst($act->jenis ?? 'Umum'),
                    'tags'      => $catArray,
                    'kategori'  => $catArray,
                ];
            })
            ->all();

        return Inertia::render('dashboard/mahasiswa/Dashboard', [
            'stats'      => $stats,
            'roadmaps'   => $enrolledRoadmaps,
            'activities' => $latestActivities,
        ]);
    }
}
