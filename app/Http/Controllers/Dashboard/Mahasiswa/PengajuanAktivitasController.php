<?php

namespace App\Http\Controllers\Dashboard\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\PengajuanAktivitasService;

class PengajuanAktivitasController extends Controller
{
    protected $pengajuanService;

    // Inject Service ke dalem Controller
    public function __construct(PengajuanAktivitasService $pengajuanService)
    {
        $this->pengajuanService = $pengajuanService;
    }

    // ==========================================
    // 1. READ: Nampilin Halaman Vue
    // ==========================================
    public function index(Request $request)
    {
        $pengajuanData = $this->pengajuanService->getPaginatedData($request);

        // Jangan lupa path Inertia-nya disesuain sama nama file/folder Vue lu ya!
        return Inertia::render('dashboard/mahasiswa/ActivitySubmission', [
            'pengajuanData' => $pengajuanData,
            'filters' => [
                'search'    => $request->input('search', ''),
                'status'    => $request->input('status', ''),
                'per_page'  => (int) ($request->input('per_page', 10)),
                'sort'      => $request->input('sort', $request->input('sort_by', 'updated_at')),
                'direction' => $request->input('direction', $request->input('sort_direction', 'desc')),
            ],
        ]);
    }

    // ==========================================
    // 2. CREATE: Simpan Data Baru dari Form
    // ==========================================
    public function store(Request $request)
    {
        // Validasi inputan biar data yang masuk bersih
        $request->validate([
            'judul'            => 'required|string|max:255',
            'deskripsi'        => 'required|string',
            'kategori'         => 'required|string|max:255',
            'jenis'            => 'required|in:umum,lomba',
            'deadline'         => 'required|date',
            'link_pendaftaran' => 'required|url|max:2048',
            'gambar.*'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048' // Maksimal 2MB per foto
        ], [
            'link_pendaftaran.required' => 'Link pendaftaran wajib diisi.',
            'link_pendaftaran.url'      => 'Link pendaftaran harus berupa URL/tautan yang valid (contoh: https://...).',
        ]);

        $this->pengajuanService->createPengajuan($request);

        return back()->with('success', 'Pengajuan aktivitas berhasil dikirim!');
    }

    // ==========================================
    // 3. UPDATE: Edit Data dari Form
    // ==========================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'judul'            => 'required|string|max:255',
            'deskripsi'        => 'required|string',
            'kategori'         => 'required|string|max:255',
            'jenis'            => 'required|in:umum,lomba',
            'deadline'         => 'required|date',
            'link_pendaftaran' => 'required|url|max:2048',
            'gambar_baru.*'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ], [
            'link_pendaftaran.required' => 'Link pendaftaran wajib diisi.',
            'link_pendaftaran.url'      => 'Link pendaftaran harus berupa URL/tautan yang valid (contoh: https://...).',
        ]);

        $this->pengajuanService->updatePengajuan($request, $id);

        return back()->with('success', 'Pengajuan aktivitas berhasil diperbarui!');
    }

    // ==========================================
    // 4. DELETE: Hapus Data
    // ==========================================
    public function destroy(Request $request, $id)
    {
        // Lempar ID pengajuan sekaligus ID user yang login (biar nggak sembarang hapus data orang)
        $this->pengajuanService->deletePengajuan($id, $request->user()->id);
        
        return back()->with('success', 'Data pengajuan dihapus.');
    }
}