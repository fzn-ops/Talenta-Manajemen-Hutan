<?php

namespace App\Http\Controllers\Dashboard\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\RoadmapSevice;
use App\Services\RoadmapDetailService;
use Barryvdh\DomPDF\Facade\Pdf;

class RoadmapController extends Controller
{
    protected RoadmapSevice $roadmapService;
    protected RoadmapDetailService $detailService;

    public function __construct(RoadmapSevice $roadmapService, RoadmapDetailService $detailService)
    {
        $this->roadmapService = $roadmapService;
        $this->detailService = $detailService;
    }

    /**
     * Halaman Utama Roadmap Mahasiswa
     */
    public function index(Request $request): Response
    {
        $userId = (int) Auth::id();
        $filters = $request->only(['search', 'kategori']);

        $roadmaps = $this->roadmapService->getMahasiswaRoadmaps($userId, $filters);
        $availableRoadmaps = $this->roadmapService->getAvailableRoadmapsForUser($userId, $request->all());

        return Inertia::render('dashboard/mahasiswa/Roadmap', [
            'roadmaps'          => $roadmaps,
            'availableRoadmaps' => $availableRoadmaps,
            'filters'           => $filters,
        ]);
    }

    /**
     * Mendaftarkan/mengikuti roadmap dari modal
     */
    public function enroll(Request $request)
    {
        $request->validate([
            'roadmap_ids'   => 'required|array|min:1',
            'roadmap_ids.*' => 'exists:roadmap,id',
        ]);

        $userId = (int) Auth::id();
        $this->roadmapService->enrollRoadmaps($userId, $request->input('roadmap_ids', []));

        return redirect()->back()->with('success', 'Roadmap berhasil ditambahkan ke daftar Anda.');
    }

    /**
     * Halaman Detail Roadmap Mahasiswa
     */
    public function show(Request $request, $id): Response
    {
        $userId = (int) Auth::id();

        // Update timestamp terakhir diakses/dikunjungi oleh mahasiswa
        \App\Models\PendaftaranRoadmap::where('id_user', $userId)
            ->where('id_roadmap', (int) $id)
            ->update(['updated_at' => now()]);

        $detailData = $this->detailService->getMahasiswaRoadmapDetail((int) $id, $userId);

        return Inertia::render('dashboard/mahasiswa/RoadmapDetail', [
            'roadmapId'          => (int) $id,
            'roadmap'            => $detailData['roadmap'],
            'months'             => $detailData['months'],
            'completedTasks'     => $detailData['completedTasks'],
            'totalTasks'         => $detailData['totalTasks'],
            'eligibleActivities' => $detailData['eligibleActivities'],
        ]);
    }

    /**
     * Toggle status pencapaian untuk materi pembelajaran (Materi)
     */
    public function toggleMateri(Request $request, $materiId)
    {
        $userId = (int) Auth::id();
        $result = $this->detailService->togglePencapaian($userId, (int) $materiId);

        $msg = !empty($result['isCompleted']) 
            ? 'Materi berhasil ditandai selesai.' 
            : 'Status materi diperbarui menjadi belum selesai.';

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Submit bukti hasil aktivitas untuk tugas roadmap (Tugas)
     */
    public function storeHasilAktivitas(Request $request, $materiId)
    {
        $request->validate([
            'id_pendaftaran_aktivitas' => 'required|exists:pendaftaran_aktivitas,id',
            'notes'                    => 'nullable|string',
            'media'                    => 'nullable|file|max:20480',
        ]);

        $userId = (int) Auth::id();
        $this->detailService->submitHasilAktivitas(
            $userId,
            (int) $materiId,
            $request->only(['id_pendaftaran_aktivitas', 'notes', 'deskripsi']),
            $request->file('media')
        );

        return redirect()->back()->with('success', 'Bukti kegiatan berhasil dikirim untuk tugas ini.');
    }

    /**
     * Hapus submission hasil aktivitas tugas
     */
    public function destroyHasilAktivitas(Request $request, $hasilId)
    {
        $userId = (int) Auth::id();
        $this->detailService->deleteHasilAktivitas($userId, (int) $hasilId);

        return redirect()->back()->with('success', 'Kegiatan berhasil dihapus dari tugas.');
    }

    /**
     * Tampilkan preview sertifikat roadmap dalam format PDF (inline stream)
     */
    public function previewCertificate(Request $request, $id)
    {
        $userId = (int) Auth::id();
        $certData = $this->detailService->getCertificateData((int) $id, $userId);

        if (!$certData['isCompleted']) {
            abort(403, 'Sertifikat belum dapat diakses. Anda harus menyelesaikan seluruh tugas dan materi roadmap terlebih dahulu.');
        }

        $pdf = Pdf::loadView('certificates.roadmap', $certData)
            ->setPaper('a4', 'landscape')
            ->setOption([
                'isRemoteEnabled' => true,
                'defaultFont'     => 'Helvetica',
            ]);

        $fileName = 'Sertifikat_' . Str::slug($certData['roadmap']->nama_roadmap) . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Unduh sertifikat roadmap dalam format PDF (attachment download)
     */
    public function downloadCertificate(Request $request, $id)
    {
        $userId = (int) Auth::id();
        $certData = $this->detailService->getCertificateData((int) $id, $userId);

        if (!$certData['isCompleted']) {
            abort(403, 'Sertifikat belum dapat diunduh. Anda harus menyelesaikan seluruh tugas dan materi roadmap terlebih dahulu.');
        }

        $pdf = Pdf::loadView('certificates.roadmap', $certData)
            ->setPaper('a4', 'landscape')
            ->setOption([
                'isRemoteEnabled' => true,
                'defaultFont'     => 'Helvetica',
            ]);

        $studentName = $certData['user']->nama ?? $certData['user']->name ?? 'Mahasiswa';
        $fileName = 'Sertifikat_' . Str::slug($certData['roadmap']->nama_roadmap) . '_' . Str::slug($studentName) . '.pdf';

        return $pdf->download($fileName);
    }
}
