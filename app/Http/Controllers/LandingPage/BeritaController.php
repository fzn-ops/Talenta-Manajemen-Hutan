<?php

namespace App\Http\Controllers\LandingPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BeritaService;
use Inertia\Inertia;

class BeritaController extends Controller
{
    //
    protected $beritaService;

    public function __construct(BeritaService $beritaService)
    {
        $this->beritaService = $beritaService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $date = $request->input('date'); // 'terlama' or null/'' (terbaru)
        $direction = ($date === 'terlama') ? 'asc' : 'desc';
        $perPage = (int) $request->input('per_page', 6);

        $news = $this->beritaService->getPaginatedBerita(
            $search,
            null, // bulan
            'created_at',
            $direction,
            $perPage
        );

        return Inertia::render('landing_page/news/News', [
            'news' => $news,
            'filters' => [
                'search' => $search,
                'date' => $date,
                'per_page' => $perPage,
            ]
        ]);
    }

    public function show($id){
        
        $beritaDetail=$this->beritaService->getBeritaById($id);
        $relatedNews=$this->beritaService->getRelatedNews($id);

        return Inertia::render('landing_page/news/Show',[
            'beritaDetail'=>$beritaDetail,
            'relatedNews'=>$relatedNews
        ]);

    }
}
