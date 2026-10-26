<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Services\KarirService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KarirController extends Controller
{
    protected $karirService;

    public function __construct(KarirService $karirService)
    {
        $this->karirService = $karirService;
    }

    /**
     * READ: Menampilkan daftar karir dengan filter, pencarian, dan pagination.
     */
    public function index(Request $request)
    {
        $filters = [
            'search'    => $request->input('search'),
            'kategori'  => $request->input('kategori'),
            'waktu'     => $request->input('waktu'),
            'sort'      => $request->input('sort', 'created_at'),
            'direction' => $request->input('direction', 'desc'),
            'per_page'  => (int) $request->input('per_page', 10),
            'page'      => (int) $request->input('page', 1),
        ];

        $karirList = $this->karirService->getPaginationKarir($filters, $filters['per_page']);

        return Inertia::render('dashboard/admin/CareerList', [
            'karirData' => $karirList,
            'filters'   => $filters,
        ]);
    }

    /**
     * CREATE: Simpan data karir baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'posisi'        => 'required|string|max:255',
            'instansi'      => 'nullable|string|max:255',
            'nama_instansi' => 'nullable|string|max:255',
            'deskripsi'     => 'required|string',
            'kualifikasi'   => 'required|string',
            'tautan'        => 'required|string|max:255',
            'deadline'      => 'required|date',
            'logo'          => 'required|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        if (empty($validated['nama_instansi']) && !empty($validated['instansi'])) {
            $validated['nama_instansi'] = $validated['instansi'];
        }

        $this->karirService->createKarir($validated);

        return redirect()->back()->with('success', 'Data karir berhasil ditambahkan!');
    }

    /**
     * UPDATE: Perbarui data karir yang sudah ada.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'posisi'        => 'required|string|max:255',
            'instansi'      => 'nullable|string|max:255',
            'nama_instansi' => 'nullable|string|max:255',
            'deskripsi'     => 'required|string',
            'kualifikasi'   => 'required|string',
            'tautan'        => 'required|string|max:255',
            'deadline'      => 'required|date',
            'logo'          => 'nullable',
        ]);

        if ($request->hasFile('logo')) {
            $request->validate([
                'logo' => 'image|mimes:jpeg,png,jpg|max:10240',
            ]);
        }

        if (empty($validated['nama_instansi']) && !empty($validated['instansi'])) {
            $validated['nama_instansi'] = $validated['instansi'];
        }

        $this->karirService->updateKarir($id, $validated);

        return redirect()->back()->with('success', 'Data karir berhasil diperbarui!');
    }

    /**
     * DELETE: Hapus data karir.
     */
    public function destroy($id)
    {
        $this->karirService->deleteKarir($id);

        return redirect()->back()->with('success', 'Data karir berhasil dihapus!');
    }
}
