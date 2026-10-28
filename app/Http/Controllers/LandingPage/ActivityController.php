<?php

namespace App\Http\Controllers\LandingPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\AktivitasService;

class ActivityController extends Controller
{
    //
    protected $aktivitasService;

    public function __construct(AktivitasService $aktivitasService)
    {
        $this->aktivitasService = $aktivitasService;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 8);

        // Hanya izinkan sorting deadline valid ('terdekat', 'terjauh', 'terlewat')
        if (isset($filters['sort']) && !in_array($filters['sort'], ['terdekat', 'terjauh', 'terlewat'])) {
            unset($filters['sort']);
        }

        // Ambil data aktivitas tanpa filter userId (public catalog)
        $aktivitasData = $this->aktivitasService->getPaginationAktivitas($filters, $perPage);

        $safeFilters = $request->only(['search', 'jenis', 'kategori', 'sort', 'per_page']);
        if (isset($safeFilters['sort']) && !in_array($safeFilters['sort'], ['terdekat', 'terjauh', 'terlewat'])) {
            $safeFilters['sort'] = null;
        }

        return Inertia::render('landing_page/activity/Activities', [
            'aktivitasData' => $aktivitasData,
            'filters'       => $safeFilters,
        ]);
    }

    public function show($id) {
        $aktivitas=$this->aktivitasService->getAktivitasById($id);

        return Inertia::render('landing_page/activity/Show', [
            'aktivitasDetail'=>$aktivitas
        ]);
    }
}
