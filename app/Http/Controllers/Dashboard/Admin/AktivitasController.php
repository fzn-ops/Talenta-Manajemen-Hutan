<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Services\AktivitasService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AktivitasController extends Controller
{
    //
    protected $aktivitasService;

    public function __construct(AktivitasService $aktivitasService)
    {
        $this->aktivitasService = $aktivitasService;
    }

    public function index(Request $request){
        $filters = [
            'search'=> $request->input('search'),
            'jenis'=> $request->input('jenis'),
            'bulan'=> $request->input('bulan'),
            'kategori'=> $request->input('kategori'),
            'status'=> $request->input('status'),
            'waktu'     => $request->input('waktu'),
            'sort'      => $request->input('sort', 'updated_at'),
            'direction' => $request->input('direction', 'desc'),
        ];
        
        $perPage = $request->input('per_page', 10);
        
        $aktivitasList = $this->aktivitasService->getPaginationAktivitas($filters, $perPage);

        return Inertia::render('dashboard/admin/ActivityList',[
            'aktivitasData' => $aktivitasList, 
            'filters'       => $filters
        ]);
    }

    /**
     * CREATE: Simpan Aktivitas Baru
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'judul'            => 'required|string|max:255',
            'deskripsi'        => 'required|string',
            'kategori'         => 'required|array', // Wajib array karena kita pakai JSON
            'partisipan'       => 'nullable|integer',
            'jenis'            => 'nullable|in:umum,lomba',
            'status'           => 'nullable|boolean',
            'deadline'         => 'required|date',
            'link_pendaftaran' => 'required|url|max:2048',
            'gambar'           => 'nullable|array|max:3', // Misal maksimal 3 gambar sesuai sisaSlot lu
            'gambar.*'         => 'image|mimes:jpeg,png,jpg|max:10240',
        ], [
            'link_pendaftaran.required' => 'Link pendaftaran wajib diisi.',
            'link_pendaftaran.url'      => 'Link pendaftaran harus berupa URL/tautan yang valid (contoh: https://...).',
        ]);

        $validated['partisipan'] = $validated['partisipan'] ?? 0;
        $validated['jenis']      = $validated['jenis'] ?? 'umum';
        $validated['status']     = $validated['status'] ?? 0;

        // 2. Lempar ke Service untuk disimpan ke DB & Upload File
        $this->aktivitasService->createAktivitas($validated);

        // 3. Kembali ke halaman sebelumnya dengan pesan sukses (Inertia)
        return redirect()->back()->with('success', 'Aktivitas berhasil ditambahkan!');
    }

    /**
     * UPDATE: Perbarui Aktivitas
     */
    public function update(Request $request, $id)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'judul'            => 'required|string|max:255',
            'deskripsi'        => 'required|string',
            'kategori'         => 'required|array',
            'partisipan'       => 'nullable|integer',
            'jenis'            => 'nullable|in:umum,lomba',
            'status'           => 'nullable',
            'deadline'         => 'required|date',
            'link_pendaftaran' => 'required|url|max:2048',
            'gambar'           => 'nullable|array|max:3',
            'gambar.*'         => 'image|mimes:jpeg,png,jpg|max:10240',
            'deleted_images'   => 'nullable|array',
            'deleted_images.*' => 'integer', 
        ], [
            'link_pendaftaran.required' => 'Link pendaftaran wajib diisi.',
            'link_pendaftaran.url'      => 'Link pendaftaran harus berupa URL/tautan yang valid (contoh: https://...).',
        ]);

        // 2. Default Values (Biar MySQL gak error 1364)
        $validated['partisipan'] = $validated['partisipan'] ?? 0;
        $validated['jenis']      = $validated['jenis'] ?? 'umum';
        $validated['status']     = $validated['status'] ?? 0;

        // 3. Lempar data (termasuk gambar & deleted_images) ke Service
        $this->aktivitasService->updateAktivitas($id, $validated);

        return redirect()->back()->with('success', 'Aktivitas berhasil diperbarui!');
    }

    /**
     * DELETE: Hapus Aktivitas
     */
    public function destroy($id)
    {
        $this->aktivitasService->deleteAktivitas($id);
        
        return redirect()->back()->with('success', 'Aktivitas berhasil dihapus!');
    }
}
