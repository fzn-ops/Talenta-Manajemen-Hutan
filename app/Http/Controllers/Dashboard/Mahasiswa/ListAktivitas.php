<?php

namespace App\Http\Controllers\Dashboard\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PendaftaranAktivitasService;
use App\Services\AktivitasService;
use Inertia\Inertia; 

class ListAktivitas extends Controller
{
    protected $PendaftaranAktivitasService;
    protected $AktivitasService;

    public function __construct(PendaftaranAktivitasService $PendaftaranAktivitasService, AktivitasService $AktivitasService) {
        $this->PendaftaranAktivitasService = $PendaftaranAktivitasService;
        $this->AktivitasService = $AktivitasService;
    }

    // ==================================================
    // 1. FUNGSI TAMPIL HALAMAN
    // ==================================================
    public function index(Request $request)
    {
        $filters = $request->all(); 
        $perPage = $request->get('per_page', 8); 
        $userId = $request->user()->id; 

        $aktivitasData = $this->AktivitasService->getPaginationAktivitas($filters, $perPage, $userId);
        
        $pendaftaranData = $this->PendaftaranAktivitasService->getDataPaginationMahasiswa($userId, $filters, $perPage);

        return Inertia::render('dashboard/mahasiswa/ActivityList', [
            'aktivitasData'   => $aktivitasData,
            'pendaftaranData' => $pendaftaranData,
            'filters'         => $request->only(['search', 'jenis', 'status', 'kategori', 'waktu', 'sort', 'per_page'])
        ]);
    }

    public function pendaftaran($id, Request $request) 
    {
        $userId = $request->user()->id;
        $aktivitas = $this->AktivitasService->getAktivitasById($id);
        $pendaftaranDetail = $this->PendaftaranAktivitasService->getDetailPendaftaranMahasiswa($id, $userId);

        // 3. Lempar ke file Vue!
        return Inertia::render('dashboard/mahasiswa/ActivityRegister', [
            'aktivitas'         => $aktivitas,
            'pendaftaranDetail' => $pendaftaranDetail
        ]);
    }

    // ==================================================
    // 2. FUNGSI MAHASISWA SUBMIT PENDAFTARAN (CREATE)
    // ==================================================
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_aktivitas'      => 'required|exists:aktivitas,id',
            'nama_tim'          => 'nullable|string|max:255',
            'bukti_pendaftaran' => 'nullable|image|mimes:jpg,jpeg,png|max:10240', 
            'peserta_ids'       => 'required|array', 
            'peserta_ids.*'     => 'exists:users,id' 
        ]);

        if ($request->hasFile('bukti_pendaftaran')) {
            $path = $request->file('bukti_pendaftaran')->store('bukti_pendaftaran', 'public');
            $validatedData['bukti_pendaftaran'] = $path;
        }
        
        $this->PendaftaranAktivitasService->createData($validatedData);

        return redirect()->back()->with('success', 'Berhasil mendaftar aktivitas! Silakan tunggu persetujuan Admin.');
    }

    // ==================================================
    // 3. FUNGSI UPDATE PENDAFTARAN
    // ==================================================
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama_tim'          => 'nullable|string|max:255',
            'bukti_pendaftaran' => 'nullable|image|mimes:jpg,jpeg,png|max:10240', 
            'peserta_ids'       => 'nullable|array', 
            'peserta_ids.*'     => 'exists:users,id'
        ]);

        if ($request->hasFile('bukti_pendaftaran')) {
            $path = $request->file('bukti_pendaftaran')->store('bukti_pendaftaran', 'public');
            $validatedData['bukti_pendaftaran'] = $path;
        }

        $this->PendaftaranAktivitasService->updateData($id, $validatedData);

        return redirect()->back()->with('success', 'Data pendaftaran berhasil diperbarui!');
    }

    // ==================================================
    // 4. FUNGSI DELETE (BATALIN PENDAFTARAN)
    // ==================================================
    public function destroy($id)
    {
        $this->PendaftaranAktivitasService->deleteData($id);

        return redirect()->back()->with('success', 'Pendaftaran berhasil dibatalkan!');
    }
}