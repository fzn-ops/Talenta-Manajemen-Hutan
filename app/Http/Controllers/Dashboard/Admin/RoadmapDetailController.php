<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Services\RoadmapDetailService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoadmapDetailController extends Controller
{
    protected RoadmapDetailService $roadmapDetailService;

    public function __construct(RoadmapDetailService $roadmapDetailService)
    {
        $this->roadmapDetailService = $roadmapDetailService;
    }

    /**
     * Display roadmap detail page with all months and items
     */
    public function show($id)
    {
        $data = $this->roadmapDetailService->getRoadmapWithDetails($id);

        return Inertia::render('dashboard/admin/RoadmapDetail', [
            'roadmap' => $data['roadmap'],
            'months'  => $data['months'],
        ]);
    }

    /**
     * Add a new month to the roadmap
     */
    public function storeBulan($roadmapId)
    {
        $bulan = $this->roadmapDetailService->createBulanRoadmap($roadmapId);

        return redirect()->back()->with([
            'success'    => 'Tab Bulan berhasil ditambahkan.',
            'newMonthId' => $bulan->id,
        ]);
    }

    /**
     * Delete a month from the roadmap
     */
    public function destroyBulan($roadmapId, $bulanId)
    {
        $this->roadmapDetailService->deleteBulanRoadmap($roadmapId, $bulanId);

        return redirect()->back()->with('success', 'Tab Bulan berhasil dihapus.');
    }

    /**
     * Create a new Materi or Tugas in a month
     */
    public function storeMateri(Request $request, $roadmapId, $bulanId)
    {
        $validated = $request->validate([
            'type'  => 'required|in:Materi,Tugas',
            'title' => 'required|string|max:65',
        ]);

        $materi = $this->roadmapDetailService->createMateriRoadmap($roadmapId, $bulanId, $validated);

        return redirect()->back()->with([
            'success'   => '"' . $materi->topik . '" berhasil ditambahkan.',
            'newItemId' => $materi->id,
        ]);
    }

    /**
     * Update title (topik) of a Materi or Tugas in sidebar navigation
     */
    public function updateMateriTitle(Request $request, $materiId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:65',
        ]);

        $materi = $this->roadmapDetailService->updateMateriTitle($materiId, $validated['title']);

        return redirect()->back()->with('success', 'Nama item materi berhasil diperbarui.');
    }

    /**
     * Update long title (judul) and cards of a Materi or Tugas
     */
    public function updateMateriContent(Request $request, $materiId)
    {
        $validated = $request->validate([
            'topic'   => 'nullable|string|max:150',
            'judul'   => 'nullable|string|max:150',
            'cards'   => 'required|array|min:1',
            'cards.*' => 'nullable',
        ]);

        $materi = $this->roadmapDetailService->updateMateriContent($materiId, $validated);

        return redirect()->back()->with('success', 'Konten "' . $materi->judul . '" berhasil disimpan.');
    }

    /**
     * Delete a Materi or Tugas
     */
    public function destroyMateri($materiId)
    {
        $this->roadmapDetailService->deleteMateriRoadmap($materiId);

        return redirect()->back()->with('success', 'Item berhasil dihapus.');
    }

    /**
     * Get roadmap participants list with their progress (AJAX JSON)
     */
    public function getParticipants($roadmapId)
    {
        $data = $this->roadmapDetailService->getRoadmapParticipants((int) $roadmapId);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
