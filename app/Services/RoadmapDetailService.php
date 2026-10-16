<?php

namespace App\Services;

use App\Models\Roadmap;
use App\Models\BulanRoadmap;
use App\Models\MateriRoadmap;
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
                'tipe'             => 1,
                'judul'            => 'Pengantar Modul',
                'deskripsi'        => '',
                'kartu_konten'     => [],
                'maksimal_tugas'   => 0,
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
                    return [
                        'id'             => $materi->id,
                        'type'           => $materi->tipe ? 'Materi' : 'Tugas',
                        'topik'          => $materi->topik, // "Materi 1", "Tugas 1" (sidebar label)
                        'judul'          => $materi->judul, // Judul panjang konten materi
                        'title'          => $materi->topik, // for sidebar navigation compatibility
                        'topic'          => $materi->judul, // for content heading compatibility
                        'description'    => $materi->deskripsi ?? '',
                        'cards'          => $materi->kartu_konten ?? [],
                        'maksimal_tugas' => $materi->maksimal_tugas ?? 0,
                        'file'           => null,
                        'fileName'       => '',
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
            'tipe'             => 1, // Materi
            'judul'            => 'Pengantar Modul Baru',
            'deskripsi'        => '',
            'kartu_konten'     => [],
            'maksimal_tugas'   => 0,
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

        $tipe = ($data['type'] ?? 'Materi') === 'Tugas' ? 0 : 1;
        $topik = trim($data['topik'] ?? $data['title'] ?? 'Materi');
        $judul = trim($data['judul'] ?? 'Topik ' . $topik);

        $cards = [];
        if ($tipe === 0) {
            $cards[] = [
                'id'       => (string) round(microtime(true) * 1000),
                'type'     => 'task_submission',
                'content'  => 'Ikuti Kegiatan Terkait ' . $judul,
                'fileName' => '',
                'isFixed'  => true,
            ];
        }

        return MateriRoadmap::create([
            'id_bulan_roadmap' => $bulan->id,
            'topik'            => $topik,
            'tipe'             => $tipe,
            'judul'            => $judul,
            'deskripsi'        => '',
            'kartu_konten'     => $cards,
            'maksimal_tugas'   => $tipe === 0 ? 1 : 0,
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
            }

            $processedCards[] = [
                'id'       => $card['id'] ?? (string) uniqid(),
                'type'     => $type,
                'content'  => $content,
                'fileName' => $fileName,
                'isFixed'  => $isFixed,
            ];
        }

        // If Tugas (tipe == 0), ensure task_submission card exists
        if (!$materi->tipe) {
            $hasTaskSub = false;
            foreach ($processedCards as &$c) {
                if ($c['type'] === 'task_submission') {
                    $hasTaskSub = true;
                    $c['isFixed'] = true;
                }
            }
            if (!$hasTaskSub) {
                $processedCards[] = [
                    'id'       => (string) round(microtime(true) * 1000),
                    'type'     => 'task_submission',
                    'content'  => 'Ikuti Kegiatan Terkait ' . ($judul ?: $materi->topik),
                    'fileName' => '',
                    'isFixed'  => true,
                ];
            }
        }

        $materi->update([
            'judul'        => $judul,
            'kartu_konten' => $processedCards,
            'deskripsi'    => $firstDescription,
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
}