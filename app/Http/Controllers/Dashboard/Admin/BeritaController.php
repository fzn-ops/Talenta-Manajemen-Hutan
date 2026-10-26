<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BeritaService;
use Inertia\Inertia;

class BeritaController extends Controller
{
    protected $beritaService;

    public function __construct(BeritaService $beritaService)
    {
        $this->beritaService = $beritaService;
    }

    public function index(Request $request)
    {
        // 1. Tangkap semua parameter dari Vue
        $search = $request->input('search');
        $bulan = $request->input('bulan');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $perPage = $request->input('per_page', 10);

        // 2. Lempar semuanya ke Service lu (Pastikan urutannya sesuai sama yang di Service ya!)
        $beritaData = $this->beritaService->getPaginatedBerita(
            $search, 
            $bulan, 
            $sort, 
            $direction, 
            $perPage
        );

        // 3. Kirim balik datanya ke Vue, termasuk semua filternya biar inputan user nggak kereset
        return Inertia::render('dashboard/admin/NewsList', [
            'beritaData' => $beritaData,
            'filters'    => [
                'search'    => $search,
                'bulan'     => $bulan,
                'sort'      => $sort,
                'direction' => $direction
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'foto'      => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'created_at' => 'required|date', 
        ]);

        $this->beritaService->createBerita($validated);

        return redirect()->back()->with('success', 'Berita berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'foto'      => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'created_at' => 'required|date',
        ]);

        $this->beritaService->updateBerita($id, $validated);

        return redirect()->back()->with('success', 'Berita berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $this->beritaService->deleteBerita($id);
        return redirect()->back()->with('success', 'Berita berhasil dihapus!');
    }
}
