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

    public function index(){
        
    }

    public function show($id) {
        $aktivitas=$this->aktivitasService->getAktivitasById($id);

        return Inertia::render('landing_page/activity/Show', [
            'aktivitasDetail'=>$aktivitas
        ]);
    }
}
