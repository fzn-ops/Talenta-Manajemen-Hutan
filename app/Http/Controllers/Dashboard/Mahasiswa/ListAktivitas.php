<?php

namespace App\Http\Controllers\Dashboard\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PendaftaranAktivitasService;
use App\Services\AktivitasService;
use Inertia\Inertia; 
use App\Models\User;

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

        // Hanya izinkan sorting deadline valid untuk mahasiswa ('terdekat', 'terjauh', 'terlewat')
        if (isset($filters['sort']) && !in_array($filters['sort'], ['terdekat', 'terjauh', 'terlewat'])) {
            unset($filters['sort']);
        }

        $aktivitasData = $this->AktivitasService->getPaginationAktivitas($filters, $perPage, $userId);
        
        $pendaftaranData = $this->PendaftaranAktivitasService->getDataPaginationMahasiswa($userId, $filters, $perPage);

        $safeFilters = $request->only(['search', 'jenis', 'status', 'kategori', 'waktu', 'sort', 'per_page']);
        if (isset($safeFilters['sort']) && !in_array($safeFilters['sort'], ['terdekat', 'terjauh', 'terlewat'])) {
            $safeFilters['sort'] = null;
        }

        return Inertia::render('dashboard/mahasiswa/ActivityList', [
            'aktivitasData'   => $aktivitasData,
            'pendaftaranData' => $pendaftaranData,
            'filters'         => $safeFilters
        ]);
    }

    public function pendaftaran($id, Request $request) 
    {
        $userId = $request->user()->id;
        $aktivitas = $this->AktivitasService->getAktivitasById($id);
        $pendaftaranDetail = $this->PendaftaranAktivitasService->getDetailPendaftaranMahasiswa($id, $userId);
        $mahasiswaList = User::select('id', 'nama', 'nim')
                                ->where('role', '!=', 'admin')                        
                                ->get();

        // 3. Lempar ke file Vue!
        return Inertia::render('dashboard/mahasiswa/ActivityRegister', [
            'aktivitas'         => $aktivitas,
            'pendaftaranDetail' => $pendaftaranDetail,
            'mahasiswaList'     => $mahasiswaList,
        ]);
    }

    // ==================================================
    // 2. FUNGSI MAHASISWA SUBMIT PENDAFTARAN (CREATE)
    // ==================================================

    public function store(Request $request)
    {
        // 1. Validasi bawaan dari Vue (namanya tetep 'bukti')
        $request->validate([
            'id_aktivitas' => 'required',
            'nama_tim'     => 'required',
            'peserta_ids'  => 'required|array',
            'bukti'        => 'required|file|image|max:10240', 
        ]);
    
        $pathBukti = null;
        if ($request->hasFile('bukti')) {
            $pathBukti = $request->file('bukti')->store('bukti_pendaftaran', 'public');
        }
    
        $data = [
            'id_aktivitas'      => $request->id_aktivitas,
            'nama_tim'          => $request->nama_tim,
            'peserta_ids'       => $request->peserta_ids,
            'bukti_pendaftaran' => $pathBukti, 
        ];
    
        $this->PendaftaranAktivitasService->createData($data);
    
        return redirect()->back();
    }

    // ==================================================
    // 3. FUNGSI UPDATE PENDAFTARAN
    // ==================================================
public function update(Request $request, $id)
    {
        // 1. Validasi namanya pakai 'bukti', BUKAN 'bukti_pendaftaran'
        $validatedData = $request->validate([
            'nama_tim'      => 'nullable|string|max:255',
            'bukti'         => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'peserta_ids'   => 'nullable|array', 
            'peserta_ids.*' => 'exists:users,id'
        ]);

        // 2. Tangkap filenya pakai nama 'bukti'
        if ($request->hasFile('bukti')) {
            $path = $request->file('bukti')->store('bukti_pendaftaran', 'public');
            $validatedData['bukti_pendaftaran'] = $path;
        }

        $validatedData['status'] = 'Menunggu';

        unset($validatedData['bukti']);

        $this->PendaftaranAktivitasService->updateData($id, $validatedData);

        return redirect()->back()->with('success', 'Data pendaftaran berhasil diperbarui!');
    }

    // ==================================================
    // 4. FUNGSI DELETE (BATALIN PENDAFTARAN)
    // ==================================================
    public function destroy($id)
    {
        $this->PendaftaranAktivitasService->delete($id);

        return redirect()->back()->with('success', 'Pendaftaran berhasil dibatalkan!');
    }
}