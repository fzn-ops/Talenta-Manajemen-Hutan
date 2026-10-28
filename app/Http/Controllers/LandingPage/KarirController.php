<?php

namespace App\Http\Controllers\LandingPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\KarirService;
use Inertia\Inertia;



class KarirController extends Controller
{
    //
    protected $karirService;

    public function __construct(KarirService $karirService)
    {
        $this->karirService = $karirService;
    }

    public function index(Request $request){
        $filters=[
            'search'=>$request->input('search'),
            'kategori'=>$request->input('kategori'),
            'sort'=>$request->input('sort')
        ];
        $karirData = $this->karirService->getPaginationKarir($filters,9);

        return Inertia::render('landing_page/career/Careers',[
            'karirData'=>$karirData
        ]);
    }

    public function show($id){

        $karirDetail = $this->karirService->getKarirById($id);

        return Inertia::render('landing_page/career/Show',[
            'karirDetail'=>$karirDetail
        ]);
    }
}
