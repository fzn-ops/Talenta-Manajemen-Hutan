<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Services\AktivitasService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AktivitasController extends Controller
{
    //
    protected $aktivitasService;

    public function __construct(AktivitasService $aktivitasService)
    {
        $this->aktivitasService = $aktivitasService;
    }

    public function index(Request $request){
        $filters = [
            'search'=> $request->input('search'),
            'jenis'=> $request->input('jenis'),
            'bulan'=> $request->input('bulan'), // Format: 'YYYY-MM'
            'kategori'=> $request->input('kategori'),
            'status'=> $request->input('status'),
        ];

        $aktivitasList = $this->aktivitasService->getPaginationAktivitas($filters, 12);

        return Inertia::render('listAktivitasAdmin',[
            'aktivitasData' => $aktivitasList, 
            'filters'       => $filters
        ]);
    }
}
