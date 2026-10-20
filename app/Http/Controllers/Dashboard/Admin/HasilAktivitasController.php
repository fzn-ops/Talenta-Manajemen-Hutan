<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Services\HasilAktivitasService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HasilAktivitasController extends Controller
{
    protected $hasilService;

    public function __construct(HasilAktivitasService $hasilService)
    {
        $this->hasilService = $hasilService;
    }

    /**
     * Tampilkan data hasil aktivitas mahasiswa dengan search, filter, sorting, dan paginasi.
     */
    public function index(Request $request)
    {
        $filters = [
            'search'    => $request->input('search'),
            'status'    => $request->input('status'),
            'sort'      => $request->input('sort'),
            'direction' => $request->input('direction'),
        ];

        $perPage = $request->input('per_page', 10);

        $hasilData = $this->hasilService->getPaginatedHasil($filters, $perPage);

        return Inertia::render('dashboard/admin/ActivityResult', [
            'hasilData' => $hasilData,
            'filters'   => $filters,
        ]);
    }

    /**
     * Setujui hasil aktivitas mahasiswa.
     */
    public function approve($id)
    {
        $this->hasilService->approve($id);

        return redirect()->back()->with('success', 'Hasil aktivitas mahasiswa berhasil disetujui!');
    }

    /**
     * Tolak hasil aktivitas mahasiswa dengan catatan penolakan.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:1000',
        ]);

        $this->hasilService->reject($id, $request->input('catatan'));

        return redirect()->back()->with('success', 'Hasil aktivitas mahasiswa berhasil ditolak.');
    }

    /**
     * Hapus hasil aktivitas mahasiswa.
     */
    public function destroy($id)
    {
        $this->hasilService->delete($id);

        return redirect()->back()->with('success', 'Hasil aktivitas mahasiswa berhasil dihapus!');
    }
}
