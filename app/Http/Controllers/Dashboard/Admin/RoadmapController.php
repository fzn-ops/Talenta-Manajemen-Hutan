<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\RoadmapSevice;

class RoadmapController extends Controller
{
    protected $roadmapService;

    // Inject RoadmapService ke dalam Controller
    public function __construct(RoadmapSevice $roadmapService)
    {
        $this->roadmapService = $roadmapService;
    }

    // ==========================================
    // 1. TAMPILKAN HALAMAN & DATA LIST
    // ==========================================
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'kategori']);
        $perPage = $request->input('per_page', 10);

        $roadmapData = $this->roadmapService->getPaginationRoadmap($filters, $perPage);
        $categoryCounts = $this->roadmapService->getCategoryCounts();
        return Inertia::render('dashboard/admin/Roadmap', [
            'roadmapData' => $roadmapData,
            'categoryCounts'=> $categoryCounts, 
            'filters'     => $filters
        ]);
    }

    // ==========================================
    // 2. SIMPAN DATA BARU (CREATE)
    // ==========================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_roadmap' => 'required|string|max:255',
            'kategori'     => 'required|string|in:Profesional,Bisnis,Birokrasi,Akademisi',
            'gambar'       => 'nullable|image|mimes:jpeg,png,jpg|max:5120', 
        ]);

        $this->roadmapService->createRoadmap($validated);

        return redirect()->back()->with('success', 'Roadmap berhasil dibuat beserta bulan dan materi awalnya!');
    }

    // ==========================================
    // 3. UPDATE DATA (Khusus Roadmap)
    // ==========================================
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_roadmap' => 'required|string|max:255',
            'kategori'     => 'required|string|in:Profesional,Bisnis,Birokrasi,Akademisi',
            'gambar'       => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $this->roadmapService->updateRoadmap($id, $validated);

        return redirect()->back()->with('success', 'Data Roadmap berhasil diperbarui!');
    }

    // ==========================================
    // 4. HAPUS DATA (DELETE)
    // ==========================================
    public function destroy($id)
    {
        $this->roadmapService->deleteRoadmap($id);

        return redirect()->back()->with('success', 'Roadmap dan seluruh materinya berhasil dihapus!');
    }
}