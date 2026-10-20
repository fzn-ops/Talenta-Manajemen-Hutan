<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Services\PersetujuanAktivitasService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PersetujuanAktivitasController extends Controller
{
    protected $persetujuanService;

    public function __construct(PersetujuanAktivitasService $persetujuanService)
    {
        $this->persetujuanService = $persetujuanService;
    }

    /**
     * Tampilkan halaman persetujuan aktivitas dengan pagination, search, dan filter.
     */
    public function index(Request $request)
    {
        $filters = [
            'search'    => $request->input('search'),
            'status'    => $request->input('status'),
            'sort'      => $request->input('sort', 'updated_at'),
            'direction' => $request->input('direction', 'desc'),
        ];

        $perPage = $request->input('per_page', 10);

        $pengajuanList = $this->persetujuanService->getPaginatedPengajuan($filters, $perPage);

        return Inertia::render('dashboard/admin/ActivityApproval', [
            'pengajuanData' => $pengajuanList,
            'filters'       => $filters,
        ]);
    }

    /**
     * Setujui pengajuan aktivitas:
     * Otomatis membuat aktivitas baru di tabel aktivitas agar langsung muncul di list aktivitas.
     */
    public function approve($id)
    {
        $this->persetujuanService->approve($id);

        return redirect()->back()->with('success', 'Pengajuan aktivitas berhasil disetujui dan langsung ditambahkan ke list aktivitas!');
    }

    /**
     * Tolak pengajuan aktivitas beserta catatan penolakan.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:1000',
        ]);

        $this->persetujuanService->reject($id, $request->input('catatan'));

        return redirect()->back()->with('success', 'Pengajuan aktivitas berhasil ditolak.');
    }

    /**
     * Hapus pengajuan aktivitas.
     */
    public function destroy($id)
    {
        $this->persetujuanService->delete($id);

        return redirect()->back()->with('success', 'Pengajuan aktivitas berhasil dihapus!');
    }
}
