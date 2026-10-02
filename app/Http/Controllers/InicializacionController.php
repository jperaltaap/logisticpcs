<?php

namespace App\Http\Controllers;

use App\Services\SystemInitializationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InicializacionController extends Controller
{
    public function __construct(
        protected SystemInitializationService $initializationService
    ) {}

    /**
     * Ventana dedicada del Asistente de Inicialización (Progress Steps) para el Administrador.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Los usuarios no administradores no pueden configurar el sistema; se les redirige a la ventana de espera
        if ($user && $user->rol !== 'ADMINISTRADOR') {
            return redirect()->route('inicializacion.espera');
        }

        $status = $this->initializationService->getStatus();

        return view('inicializacion.index', compact('status'));
    }

    /**
     * Ventana de espera informativa para usuarios no administradores mientras el sistema se inicializa.
     */
    public function espera(Request $request): View|RedirectResponse
    {
        // Si el sistema ya fue inicializado por el administrador, redirigir a su dashboard correspondiente
        if ($this->initializationService->isInitialized()) {
            return redirect()->route('dashboard')
                ->with('success', 'El sistema ha sido inicializado correctamente. Ya puede utilizar sus funciones.');
        }

        $status = $this->initializationService->getStatus();

        return view('inicializacion.espera', compact('status'));
    }
}
