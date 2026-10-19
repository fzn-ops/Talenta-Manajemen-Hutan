<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\HasilAktivitas;
use App\Models\PendaftaranAktivitas;
use App\Models\PengajuanAktivitas;
use App\Models\Roadmap;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ambil 5 pengajuan aktivitas teratas
        $pengajuanAktivitas = PengajuanAktivitas::with(['user', 'gambarPengajuanAktivitas', 'aktivitas'])
            ->orderBy('id', 'asc')
            ->take(5)
            ->get();

        // 2. Ambil 5 pendaftaran aktivitas teratas
        $pendaftaranAktivitas = PendaftaranAktivitas::with(['activity', 'peserta'])
            ->orderBy('id', 'asc')
            ->take(5)
            ->get();

        // 3. Ambil 5 hasil aktivitas teratas
        $hasilAktivitas = HasilAktivitas::with([
            'user',
            'materiRoadmap.bulanRoadmap.roadmap',
            'pendaftaranAktivitas.activity'
        ])
            ->orderBy('id', 'asc')
            ->take(5)
            ->get();

        // 4. Distribusi Peserta dari Roadmap / Kategori
        $roadmaps = Roadmap::select('kategori', 'partisipan')->get();
        $profCount = $roadmaps->whereIn('kategori', ['Profesional', 'Professional'])->sum('partisipan');
        $bisnisCount = $roadmaps->where('kategori', 'Bisnis')->sum('partisipan');
        $birokratCount = $roadmaps->whereIn('kategori', ['Birokrat', 'Birokrasi'])->sum('partisipan');
        $akademisiCount = $roadmaps->where('kategori', 'Akademisi')->sum('partisipan');

        $participantDistribution = [
            ['category' => 'Professional', 'count' => (int) ($profCount ?: 510), 'color' => '#7c72ff'],
            ['category' => 'Bisnis',       'count' => (int) ($bisnisCount ?: 270), 'color' => '#ff8b85'],
            ['category' => 'Birokrat',     'count' => (int) ($birokratCount ?: 120), 'color' => '#38bdf8'],
            ['category' => 'Akademisi',    'count' => (int) ($akademisiCount ?: 100), 'color' => '#fb923c'],
        ];

        return Inertia::render('dashboard/admin/Dashboard', [
            'pengajuanAktivitas'      => $pengajuanAktivitas,
            'pendaftaranAktivitas'    => $pendaftaranAktivitas,
            'hasilAktivitas'          => $hasilAktivitas,
            'participantDistribution' => $participantDistribution,
        ]);
    }
}
