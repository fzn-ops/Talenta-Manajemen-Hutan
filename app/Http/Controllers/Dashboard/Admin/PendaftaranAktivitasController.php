<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PendaftaranAktivitasService;
use Inertia\Inertia;

class PendaftaranAktivitasController extends Controller
{
    //
    protected $PendaftaranAktivitasService;

    public function __construct(PendaftaranAktivitasService $pendaftaranAktivitasService) {

        $this->PendaftaranAktivitasService = $pendaftaranAktivitasService;
    }

    public function index(Request $request){
        $filters = $request->only(['search','status']);
        $perPage = $request->input('per_page', 10);

        $pendaftaranData = $this->PendaftaranAktivitasService->getDataPaginationAdmin($filters, $perPage);

        return Inertia::render('dashboard/admin/ActivityRegister', [
            'pendaftaranData' => $pendaftaranData,
            'filters' => $filters
        ]);
    }

    // 2. Fungsi Tombol "Setujui"
    public function approve($id)
    {
        $this->PendaftaranAktivitasService->Approval($id);
        return redirect()->back()->with('success', 'Registrasi tim berhasil disetujui!');
    }

    // 3. Fungsi Tombol "Tolak"
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_penolakan' => 'required|string|max:500'
        ]);

        $this->PendaftaranAktivitasService->Rejected($id, $request->catatan_penolakan);
        return redirect()->back()->with('success', 'Registrasi tim berhasil ditolak!');
    }

    // 4. Fungsi Tombol "Hapus"
    public function destroy($id)
    {
        $this->PendaftaranAktivitasService->delete($id);
        return redirect()->back()->with('success', 'Data pendaftaran aktivitas berhasil dihapus!');
    }
}
