<?php

namespace App\Services;

use App\Models\Roadmap;
use App\Models\BulanRoadmap;
use App\Models\MateriRoadmap;
use App\Models\Pencapaian;
use App\Models\HasilAktivitas;
use App\Models\PendaftaranAktivitas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RoadmapDetailService
{
    /**
     * Get roadmap and all its months + materials/tasks
     */
    public function getRoadmapWithDetails($roadmapId): array
    {
        $roadmap = Roadmap::with([
            'bulanRoadmap' => function ($query) {
                $query->orderBy('bulan_ke', 'asc');
            },
            'bulanRoadmap.materiRoadmap' => function ($query) {
                $query->orderBy('id', 'asc');
            }
        ])->findOrFail($roadmapId);

        // Ensure at least 1 month and 1 material exists
        if ($roadmap->bulanRoadmap->isEmpty()) {
            $bulan = BulanRoadmap::create([
                'id_roadmap' => $roadmap->id,
                'bulan_ke'   => 1,
            ]);

            MateriRoadmap::create([
                'id_bulan_roadmap' => $bulan->id,
                'topik'            => 'Materi 1',
                'tipe'             => 0, // 0 = Materi, 1 = Tugas
                'judul'            => 'Pengantar Modul',
                'deskripsi'        => '',
                'kartu_konten'     => [],
                'jumlah_aktivitas' => 0,
            ]);

            $roadmap->load([
                'bulanRoadmap' => function ($query) {
                    $query->orderBy('bulan_ke', 'asc');
                },
                'bulanRoadmap.materiRoadmap' => function ($query) {
                    $query->orderBy('id', 'asc');
                }
            ]);
        }

        $formattedRoadmap = [
            'id'           => $roadmap->id,
            'category'     => ucfirst($roadmap->kategori ?? 'Umum'),
            'kategori'     => $roadmap->kategori,
            'title'        => $roadmap->nama_roadmap,
            'nama_roadmap' => $roadmap->nama_roadmap,
            'subTitle'     => 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
            'partisipan'   => $roadmap->partisipan ?? 0,
            'gambar'       => $roadmap->gambar,
        ];

        $formattedMonths = $roadmap->bulanRoadmap->values()->map(function ($bulan, $idx) {
            return [
                'id'       => $bulan->id,
                'name'     => 'Bulan ' . ($idx + 1),
                'bulan_ke' => $bulan->bulan_ke,
                'items'    => $bulan->materiRoadmap->values()->map(function ($materi) {
                    $isTask = (bool) $materi->tipe; // 1 = Tugas, 0 = Materi
                    $cards = $materi->kartu_konten ?? [];
                    $jumlahAktivitas = $isTask ? max(1, (int) ($materi->jumlah_aktivitas ?? $materi->maksimal_tugas ?? 1)) : 0;
                    if ($isTask) {
                        foreach ($cards as &$c) {
                            if (($c['type'] ?? '') === 'task_submission') {
                                if (!isset($c['minTasks']) || (int) $c['minTasks'] <= 0) {
                                    $c['minTasks'] = $jumlahAktivitas;
                                }
                            }
                        }
                    }

                    return [
                        'id'               => $materi->id,
                        'type'             => $materi->tipe ? 'Tugas' : 'Materi',
                        'topik'            => $materi->topik, // "Materi 1", "Tugas 1" (sidebar label)
                        'judul'            => $materi->judul, // Judul panjang konten materi
                        'title'            => $materi->topik, // for sidebar navigation compatibility
                        'topic'            => $materi->judul, // for content heading compatibility
                        'description'      => $materi->deskripsi ?? '',
                        'cards'            => $cards,
                        'jumlah_aktivitas' => $jumlahAktivitas,
                        'maksimal_tugas'   => $jumlahAktivitas,
                        'file'             => null,
                        'fileName'         => '',
                    ];
                })->all(),
            ];
        })->all();

        return [
            'roadmap' => $formattedRoadmap,
            'months'  => $formattedMonths,
        ];
    }

    /**
     * Create a new month for a roadmap
     */
    public function createBulanRoadmap($roadmapId): BulanRoadmap
    {
        $count = BulanRoadmap::where('id_roadmap', $roadmapId)->count();
        if ($count >= 12) {
            throw ValidationException::withMessages([
                'bulan' => 'Anda hanya dapat menambahkan maksimal 12 bulan.',
            ]);
        }

        $maxBulanKe = BulanRoadmap::where('id_roadmap', $roadmapId)->max('bulan_ke') ?? 0;
        $nextBulanKe = $maxBulanKe + 1;

        $bulan = BulanRoadmap::create([
            'id_roadmap' => $roadmapId,
            'bulan_ke'   => $nextBulanKe,
        ]);

        // Automatically create initial Materi 1
        MateriRoadmap::create([
            'id_bulan_roadmap' => $bulan->id,
            'topik'            => 'Materi 1',
            'tipe'             => 0, // 0 = Materi, 1 = Tugas
            'judul'            => 'Pengantar Modul Baru',
            'deskripsi'        => '',
            'kartu_konten'     => [],
            'jumlah_aktivitas' => 0,
        ]);

        return $bulan;
    }

    /**
     * Delete a month from a roadmap
     */
    public function deleteBulanRoadmap($roadmapId, $bulanId): void
    {
        $count = BulanRoadmap::where('id_roadmap', $roadmapId)->count();
        if ($count <= 1) {
            throw ValidationException::withMessages([
                'bulan' => 'Minimal harus terdapat 1 bulan pada roadmap.',
            ]);
        }

        $bulan = BulanRoadmap::where('id', $bulanId)
            ->where('id_roadmap', $roadmapId)
            ->firstOrFail();

        // Delete associated materials and the month
        MateriRoadmap::where('id_bulan_roadmap', $bulan->id)->delete();
        $bulan->delete();

        // Renumber remaining months sequentially
        $remainingMonths = BulanRoadmap::where('id_roadmap', $roadmapId)
            ->orderBy('bulan_ke', 'asc')
            ->get();

        foreach ($remainingMonths as $idx => $m) {
            $m->update(['bulan_ke' => $idx + 1]);
        }
    }

    /**
     * Create a new Materi or Tugas in a month
     */
    public function createMateriRoadmap($roadmapId, $bulanId, array $data): MateriRoadmap
    {
        $bulan = BulanRoadmap::where('id', $bulanId)
            ->where('id_roadmap', $roadmapId)
            ->firstOrFail();

        $tipe = ($data['type'] ?? 'Materi') === 'Tugas' ? 1 : 0;
        $topik = trim($data['topik'] ?? $data['title'] ?? 'Materi');
        $judul = trim($data['judul'] ?? 'Topik ' . $topik);

        $cards = [];
        if ($tipe === 1) {
            $cards[] = [
                'id'       => (string) round(microtime(true) * 1000),
                'type'     => 'task_submission',
                'content'  => 'Ikuti Kegiatan Terkait ' . $judul,
                'fileName' => '',
                'isFixed'  => true,
                'minTasks' => 1,
            ];
        }

        return MateriRoadmap::create([
            'id_bulan_roadmap' => $bulan->id,
            'topik'            => $topik,
            'tipe'             => $tipe,
            'judul'            => $judul,
            'deskripsi'        => '',
            'kartu_konten'     => $cards,
            'jumlah_aktivitas' => $tipe === 1 ? 1 : null,
        ]);
    }

    /**
     * Rename the navigation item label (topik) of a Materi or Tugas
     */
    public function updateMateriTitle($materiId, string $newTitle): MateriRoadmap
    {
        $materi = MateriRoadmap::findOrFail($materiId);
        $materi->update([
            'topik' => trim($newTitle),
        ]);

        return $materi;
    }

    /**
     * Update long title (judul) and content cards of a Materi or Tugas
     */
    public function updateMateriContent($materiId, array $data): MateriRoadmap
    {
        $materi = MateriRoadmap::findOrFail($materiId);

        $judul = trim($data['judul'] ?? $data['topic'] ?? '');
        $rawCards = $data['cards'] ?? [];

        if (empty($rawCards)) {
            throw ValidationException::withMessages([
                'cards' => 'Materi harus memiliki minimal 1 konten.',
            ]);
        }

        $processedCards = [];
        $firstDescription = '';

        foreach ($rawCards as $card) {
            $type = $card['type'] ?? 'text';
            $content = $card['content'] ?? '';
            $fileName = $card['fileName'] ?? '';
            $isFixed = !empty($card['isFixed']);

            // Validate card content not empty
            if ($type === 'text') {
                $trimmed = trim(strip_tags($content));
                if (empty($trimmed) && empty($content)) {
                    throw ValidationException::withMessages([
                        'cards' => 'Terdapat kartu teks yang masih kosong.',
                    ]);
                }
                if (empty($firstDescription)) {
                    $firstDescription = $content;
                }
            } elseif (in_array($type, ['image', 'pdf'])) {
                if (empty($content)) {
                    throw ValidationException::withMessages([
                        'cards' => 'File gambar/PDF belum diunggah.',
                    ]);
                }

                // If content is base64, save to public storage
                if (preg_match('/^data:([a-zA-Z0-9]+\/[a-zA-Z0-9\-\+\.]+);base64,(.+)$/', $content, $matches)) {
                    $mimeType = $matches[1];
                    $base64Data = base64_decode($matches[2]);
                    
                    $extension = 'bin';
                    if (str_contains($mimeType, 'jpeg') || str_contains($mimeType, 'jpg')) {
                        $extension = 'jpg';
                    } elseif (str_contains($mimeType, 'png')) {
                        $extension = 'png';
                    } elseif (str_contains($mimeType, 'pdf')) {
                        $extension = 'pdf';
                    }

                    $cleanName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
                    $storedFileName = ($cleanName ?: 'material') . '_' . uniqid() . '.' . $extension;
                    $path = 'roadmap_materials/' . $storedFileName;

                    Storage::disk('public')->put($path, $base64Data);
                    $content = '/storage/' . $path;
                }
            } elseif ($type === 'video') {
                if (empty(trim($content))) {
                    throw ValidationException::withMessages([
                        'cards' => 'Tautan video YouTube belum diisi.',
                    ]);
                }
            } elseif ($type === 'task_submission') {
                if (empty(trim($content))) {
                    $content = 'Ikuti Kegiatan Terkait ' . ($judul ?: $materi->judul);
                }
                $minTasks = isset($card['minTasks']) ? max(1, (int) $card['minTasks']) : ($materi->jumlah_aktivitas ?: ($materi->maksimal_tugas ?: 1));
            }

            $cardItem = [
                'id'       => $card['id'] ?? (string) uniqid(),
                'type'     => $type,
                'content'  => $content,
                'fileName' => $fileName,
                'isFixed'  => $isFixed,
            ];

            if ($type === 'task_submission') {
                $cardItem['minTasks'] = $minTasks ?? 1;
            }

            $processedCards[] = $cardItem;
        }

        // If Tugas (tipe == 1), ensure task_submission card exists
        if ($materi->tipe) {
            $hasTaskSub = false;
            foreach ($processedCards as &$c) {
                if ($c['type'] === 'task_submission') {
                    $hasTaskSub = true;
                    $c['isFixed'] = true;
                    if (!isset($c['minTasks']) || (int) $c['minTasks'] <= 0) {
                        $c['minTasks'] = $materi->jumlah_aktivitas ?: ($materi->maksimal_tugas ?: 1);
                    }
                }
            }
            if (!$hasTaskSub) {
                $processedCards[] = [
                    'id'       => (string) round(microtime(true) * 1000),
                    'type'     => 'task_submission',
                    'content'  => 'Ikuti Kegiatan Terkait ' . ($judul ?: $materi->topik),
                    'fileName' => '',
                    'isFixed'  => true,
                    'minTasks' => $materi->jumlah_aktivitas ?: ($materi->maksimal_tugas ?: 1),
                ];
            }
        }

        $jumlahAktivitas = $materi->jumlah_aktivitas ?: ($materi->maksimal_tugas ?: 1);
        foreach ($processedCards as $pc) {
            if ($pc['type'] === 'task_submission' && isset($pc['minTasks'])) {
                $jumlahAktivitas = (int) $pc['minTasks'];
                break;
            }
        }

        $materi->update([
            'judul'            => $judul,
            'kartu_konten'     => $processedCards,
            'deskripsi'        => $firstDescription,
            'jumlah_aktivitas' => $jumlahAktivitas,
        ]);

        return $materi;
    }

    /**
     * Delete a Materi or Tugas from a month
     */
    public function deleteMateriRoadmap($materiId): void
    {
        $materi = MateriRoadmap::findOrFail($materiId);

        $count = MateriRoadmap::where('id_bulan_roadmap', $materi->id_bulan_roadmap)->count();
        if ($count <= 1) {
            throw ValidationException::withMessages([
                'materi' => 'Minimal harus terdapat 1 item (Materi/Tugas) di dalam bulan ini.',
            ]);
        }

        $materi->delete();
    }

    // =========================================================================
    // MAHASISWA SERVICES: DETAIL, PENCAPAIAN, HASIL AKTIVITAS, & FILTER KEGIATAN
    // =========================================================================

    /**
     * Ambil detail roadmap untuk mahasiswa beserta status penyelesaian materi,
     * submission tugas, progress counter, dan daftar aktivitas yang eligible untuk tugas.
     */
    public function getMahasiswaRoadmapDetail(int $roadmapId, int $userId): array
    {
        $roadmap = Roadmap::with([
            'bulanRoadmap' => function ($query) {
                $query->orderBy('bulan_ke', 'asc');
            },
            'bulanRoadmap.materiRoadmap' => function ($query) {
                $query->orderBy('id', 'asc');
            }
        ])->findOrFail($roadmapId);

        // Kumpulkan semua ID materi dari seluruh bulan pada roadmap ini
        $allMateriIds = $roadmap->bulanRoadmap->flatMap(function ($bulan) {
            return $bulan->materiRoadmap->pluck('id');
        })->all();

        // 1. Auto-sinkron tugas yang telah disetujui (HasilAktivitas status = disetujui) ke tabel Pencapaian
        $approvedTaskIds = HasilAktivitas::where('id_user', $userId)
            ->whereIn('id_materi_roadmap', $allMateriIds)
            ->where(DB::raw('LOWER(status)'), 'disetujui')
            ->pluck('id_materi_roadmap')
            ->unique()
            ->all();

        foreach ($approvedTaskIds as $tId) {
            Pencapaian::firstOrCreate([
                'id_user'           => $userId,
                'id_materi_roadmap' => $tId,
            ]);
        }

        // 2. Ambil semua ID materi yang telah diselesaikan user (Pencapaian)
        $completedMateriIds = Pencapaian::where('id_user', $userId)
            ->whereIn('id_materi_roadmap', $allMateriIds)
            ->pluck('id_materi_roadmap')
            ->toArray();
        $completedSet = array_flip($completedMateriIds);

        // 3. Ambil semua submission hasil aktivitas user untuk materi-materi tugas pada roadmap ini
        $userHasilList = HasilAktivitas::where('id_user', $userId)
            ->whereIn('id_materi_roadmap', $allMateriIds)
            ->with(['pendaftaranAktivitas.activity.gambarAktivitas'])
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('id_materi_roadmap');

        $formattedRoadmap = [
            'id'           => $roadmap->id,
            'category'     => ucfirst($roadmap->kategori ?? 'Umum'),
            'kategori'     => $roadmap->kategori,
            'title'        => $roadmap->nama_roadmap,
            'nama_roadmap' => $roadmap->nama_roadmap,
            'subTitle'     => 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
            'partisipan'   => $roadmap->partisipan ?? 0,
            'gambar'       => $roadmap->gambar ? (str_starts_with($roadmap->gambar, 'http') ? $roadmap->gambar : '/storage/' . ltrim($roadmap->gambar, '/')) : '',
        ];

        $formattedMonths = $roadmap->bulanRoadmap->values()->map(function ($bulan, $idx) use ($completedSet, $userHasilList) {
            return [
                'id'       => $bulan->id,
                'name'     => 'Bulan ' . ($idx + 1),
                'bulan_ke' => $bulan->bulan_ke,
                'items'    => $bulan->materiRoadmap->values()->map(function ($materi) use ($completedSet, $userHasilList) {
                    $isCompleted = isset($completedSet[$materi->id]);
                    $isTask = (bool) $materi->tipe; // 1 = Tugas, 0 = Materi

                    // Format submission activities untuk Tugas
                    $activities = [];
                    $taskStatus = 'belum';

                    if ($isTask) {
                        $materiHasil = $userHasilList->get($materi->id, collect());

                        // Cek status keseluruhan tugas dari submission yang ada
                        $hasApproved = $materiHasil->contains(fn($h) => strtolower($h->status) === 'disetujui');
                        $hasPending  = $materiHasil->contains(fn($h) => strtolower($h->status) === 'menunggu');
                        $hasRejected = $materiHasil->contains(fn($h) => strtolower($h->status) === 'ditolak');

                        if ($hasApproved || $isCompleted) {
                            $taskStatus = 'diterima';
                        } elseif ($hasPending) {
                            $taskStatus = 'menunggu';
                        } elseif ($hasRejected) {
                            $taskStatus = 'ditolak';
                        }

                        $activities = $materiHasil->map(function ($h) {
                            $activityTitle = $h->pendaftaranAktivitas?->activity?->judul ?? 'Aktivitas yang Diikuti';
                            $mediaUrl = $h->media ? (str_starts_with($h->media, 'http') ? $h->media : '/storage/' . ltrim($h->media, '/')) : '';
                            $fileName = $h->media ? basename($h->media) : '';

                            return [
                                'id'                       => $h->id,
                                'id_hasil'                 => $h->id,
                                'id_pendaftaran_aktivitas' => $h->id_pendaftaran_aktivitas,
                                'judul_kegiatan'           => $activityTitle,
                                'notes'                    => $h->deskripsi ?? '',
                                'fileName'                 => $fileName,
                                'mediaUrl'                 => $mediaUrl,
                                'status'                   => strtolower($h->status),
                                'catatan_penolakan'        => $h->catatan_penolakan ?? '',
                                'isSubmitted'              => true,
                                'expanded'                 => true,
                            ];
                        })->values()->all();
                    }

                    $cards = $materi->kartu_konten ?? [];
                    $jumlahAktivitas = $isTask ? max(1, (int) ($materi->jumlah_aktivitas ?? $materi->maksimal_tugas ?? 1)) : 0;
                    if ($isTask) {
                        foreach ($cards as &$c) {
                            if (($c['type'] ?? '') === 'task_submission') {
                                if (!isset($c['minTasks']) || (int) $c['minTasks'] <= 0) {
                                    $c['minTasks'] = $jumlahAktivitas;
                                }
                            }
                        }
                    }

                    return [
                        'id'               => $materi->id,
                        'type'             => $materi->tipe ? 'Tugas' : 'Materi',
                        'topik'            => $materi->topik,
                        'judul'            => $materi->judul,
                        'title'            => $materi->topik,
                        'topic'            => $materi->judul,
                        'description'      => $materi->deskripsi ?? '',
                        'cards'            => $cards,
                        'jumlah_aktivitas' => $jumlahAktivitas,
                        'maksimal_tugas'   => $jumlahAktivitas,
                        'isCompleted'      => $isCompleted,
                        'status'           => $taskStatus,
                        'activities'       => $activities,
                    ];
                })->all(),
            ];
        })->all();

        // 3. Ambil daftar aktivitas yang telah diikuti mahasiswa yang lolos filter tag kategori roadmap
        $eligibleActivities = $this->getEligibleActivitiesForRoadmap($userId, $roadmap->kategori);

        $totalTasks = count($allMateriIds);
        $completedTasks = count($completedMateriIds);

        return [
            'roadmap'            => $formattedRoadmap,
            'months'             => $formattedMonths,
            'completedTasks'     => $completedTasks,
            'totalTasks'         => $totalTasks > 0 ? $totalTasks : 1,
            'eligibleActivities' => $eligibleActivities,
        ];
    }

    /**
     * Dapatkan daftar kegiatan yang pernah diikuti mahasiswa dan memiliki tag kategori
     * yang cocok dengan kategori roadmap saat ini.
     */
    public function getEligibleActivitiesForRoadmap(int $userId, ?string $roadmapCategory): array
    {
        $pendaftaranList = PendaftaranAktivitas::with(['activity.gambarAktivitas'])
            ->whereHas('peserta', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->where(DB::raw('LOWER(status)'), 'disetujui')
            ->orderBy('id', 'desc')
            ->get();

        $eligible = [];
        $roadmapCat = $roadmapCategory ?? '';

        foreach ($pendaftaranList as $pendaftaran) {
            $activity = $pendaftaran->activity;
            if (!$activity) continue;

            // Periksa apakah tag kategori aktivitas memiliki salah satu yang cocok dengan roadmap
            if ($this->isCategoryMatched($roadmapCat, $activity->kategori)) {
                $firstGambar = $activity->gambarAktivitas->first()?->gambar;
                $gambarUrl = $firstGambar ? (str_starts_with($firstGambar, 'http') ? $firstGambar : '/storage/' . ltrim($firstGambar, '/')) : '';

                $eligible[] = [
                    'id'                       => $pendaftaran->id,
                    'id_pendaftaran_aktivitas' => $pendaftaran->id,
                    'id_aktivitas'             => $activity->id,
                    'judul'                    => $activity->judul,
                    'kategori'                 => is_array($activity->kategori) ? $activity->kategori : [$activity->kategori],
                    'nama_tim'                 => $pendaftaran->nama_tim,
                    'deadline'                 => $activity->deadline ? date('d/m/Y', strtotime($activity->deadline)) : '-',
                    'status_pendaftaran'       => $pendaftaran->status,
                    'gambar'                   => $gambarUrl,
                ];
            }
        }

        return $eligible;
    }

    /**
     * Helper untuk memeriksa apakah salah satu kategori aktivitas cocok dengan kategori roadmap.
     * Mendukung alias seperti Birokrasi <-> Birokrat, Profesional <-> Professional (case-insensitive).
     */
    public function isCategoryMatched(string $roadmapCategory, $activityCategories): bool
    {
        $normalize = function (string $cat) {
            $c = strtolower(trim($cat));
            if ($c === 'birokrat') $c = 'birokrasi';
            if ($c === 'professional') $c = 'profesional';
            return $c;
        };

        $target = $normalize($roadmapCategory);

        if (is_string($activityCategories)) {
            $decoded = json_decode($activityCategories, true);
            if (is_array($decoded)) {
                $activityCategories = $decoded;
            } else {
                $activityCategories = array_map('trim', explode(',', $activityCategories));
            }
        }

        if (!is_array($activityCategories)) {
            return false;
        }

        foreach ($activityCategories as $cat) {
            if ($normalize((string)$cat) === $target) {
                return true;
            }
        }

        return false;
    }

    /**
     * Toggle status pencapaian untuk materi (tipe = 1)
     */
    public function togglePencapaian(int $userId, int $materiId): array
    {
        $materi = MateriRoadmap::findOrFail($materiId);

        $existing = Pencapaian::where('id_user', $userId)
            ->where('id_materi_roadmap', $materiId)
            ->first();

        if ($existing) {
            $existing->delete();
            $isCompleted = false;
        } else {
            Pencapaian::create([
                'id_user'           => $userId,
                'id_materi_roadmap' => $materiId,
            ]);
            $isCompleted = true;
        }

        return [
            'isCompleted' => $isCompleted,
            'materiId'    => $materiId,
        ];
    }

    /**
     * Submit bukti hasil aktivitas untuk tugas (tipe = 0)
     */
    public function submitHasilAktivitas(int $userId, int $materiId, array $data, $file = null): HasilAktivitas
    {
        $materi = MateriRoadmap::findOrFail($materiId);

        return DB::transaction(function () use ($userId, $materi, $data, $file) {
            $pathMedia = null;

            if ($file && $file instanceof \Illuminate\Http\UploadedFile) {
                $pathMedia = $file->store('hasil_aktivitas', 'public');
            }

            $hasil = HasilAktivitas::create([
                'id_user'                  => $userId,
                'id_materi_roadmap'        => $materi->id,
                'id_pendaftaran_aktivitas' => $data['id_pendaftaran_aktivitas'] ?? null,
                'deskripsi'                => $data['notes'] ?? $data['deskripsi'] ?? '',
                'media'                    => $pathMedia,
                'status'                   => 'menunggu',
            ]);

            return $hasil;
        });
    }

    /**
     * Hapus submission hasil aktivitas milik mahasiswa
     */
    public function deleteHasilAktivitas(int $userId, int $hasilId): bool
    {
        return DB::transaction(function () use ($userId, $hasilId) {
            $hasil = HasilAktivitas::where('id', $hasilId)
                ->where('id_user', $userId)
                ->firstOrFail();

            if (!empty($hasil->media)) {
                $clean = ltrim(preg_replace('#^/?storage/#', '', $hasil->media), '/');
                if (Storage::disk('public')->exists($clean)) {
                    Storage::disk('public')->delete($clean);
                }
            }

            $materiId = $hasil->id_materi_roadmap;
            $hasil->delete();

            // Cek apakah masih ada hasil aktivitas lain yang disetujui untuk materi ini
            if ($materiId) {
                $hasOtherApproved = HasilAktivitas::where('id_user', $userId)
                    ->where('id_materi_roadmap', $materiId)
                    ->where('status', 'disetujui')
                    ->exists();

                if (!$hasOtherApproved) {
                    Pencapaian::where('id_user', $userId)
                        ->where('id_materi_roadmap', $materiId)
                        ->delete();
                }
            }

            return true;
        });
    }
}