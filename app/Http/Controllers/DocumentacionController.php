<?php

namespace App\Http\Controllers;

use App\Models\EmpresaConfig;
use Illuminate\View\View;

class DocumentacionController extends Controller
{
    /**
     * Mostrar la documentación técnica, manual del sistema y guía de usuario con casos reales.
     */
    public function index(): View
    {
        $empresa = EmpresaConfig::instancia();

        return view('documentacion.index', compact('empresa'));
    }
}
