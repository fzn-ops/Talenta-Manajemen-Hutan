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

    public function index(){

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
