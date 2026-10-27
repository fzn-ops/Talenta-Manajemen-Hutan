<?php

namespace App\Http\Controllers\LandingPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AktivitasService;
use App\Services\BeritaService;
use Inertia\Inertia;

class LandingController extends Controller
{
    //
    protected $aktivitasService;
    protected $beritaService;

    public function __construct(AktivitasService $aktivitasService, BeritaService $beritaService)
    {
        $this->aktivitasService = $aktivitasService;
        $this->beritaService = $beritaService;
    }



    public function index(Request $request){

        $filters = [
            'jenis'=> $request->input('jenis'),
        ];

        $aktivitasData = $this->aktivitasService->getPaginationAktivitas($filters,7);
        $beritaData = $this->beritaService->getLandingBerita(4);

        return Inertia::render('landing_page/Home',[
            'aktivitasData'=>$aktivitasData,
            'beritaData'=>$beritaData
        ]);
    }
}
