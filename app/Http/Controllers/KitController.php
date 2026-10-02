<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kit\StoreKitRequest;
use App\Http\Requests\Kit\UpdateKitRequest;
use App\Models\Activo;
use App\Models\Articulo;
use App\Models\InventarioStock;
use App\Models\Kit;
use App\Models\Ubicacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Kit::withCount('componentes')->with(['proyecto', 'ubicacion', 'componentes.articulo']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('codigo_kit', 'like', "%{$search}%")
                    ->orWhere('nombre_kit', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo_kit')) {
            $query->where('tipo_kit', $request->input('tipo_kit'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('ubicacion_id')) {
            $query->where('ubicacion_id', $request->integer('ubicacion_id'));
        }

        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();

        $proyectoActivoId = session('proyecto_activo_id');
        if ($proyectoActivoId) {
            $query->where('proyecto_id', $proyectoActivoId);
        } elseif ($permitidos !== null) {
            $query->whereIn('proyecto_id', $permitidos);
        }

        $ubicaciones = Ubicacion::where('estado', 'ACTIVO')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->orderBy('nombre')
            ->get();

        $todosKits = (clone $query)->orderBy('nombre_kit')->get();
        $disponibilidadMap = [];
        $resumenKits = [
            'total' => $todosKits->count(),
            'completos' => 0,
            'incompletos' => 0,
            'armables_total' => 0,
        ];

        foreach ($todosKits as $k) {
            $eval = $k->evaluarDisponibilidadEnAlmacen($k->ubicacion_id);
            $disponibilidadMap[$k->id] = $eval;
            if ($eval['disponible']) {
                $resumenKits['completos']++;
            } else {
                $resumenKits['incompletos']++;
            }
            $resumenKits['armables_total'] += $eval['kits_armables'];
        }

        if ($request->filled('disponibilidad')) {
            $filtroDisp = $request->input('disponibilidad');
            $idsFiltrados = collect($disponibilidadMap)
                ->filter(fn (array $eval) => $filtroDisp === 'COMPLETO' ? $eval['disponible'] : ! $eval['disponible'])
                ->keys()
                ->all();
            $query->whereIn('id', $idsFiltrados ?: [0]);
        }

        $kits = $query->orderBy('nombre_kit')->paginate(12)->withQueryString();

        return view('kits.index', compact('kits', 'ubicaciones', 'disponibilidadMap', 'resumenKits'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        $proyectoActivoId = session('proyecto_activo_id');

        $ubicaciones = Ubicacion::where('estado', 'ACTIVO')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->orderBy('nombre')
            ->get();

        $articulos = Articulo::where('estado', 'ACTIVO')
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when(! $proyectoActivoId && $permitidos !== null, fn ($q) => $q->where(function ($sub) use ($permitidos) {
                $sub->whereIn('proyecto_id', $permitidos)->orWhereNull('proyecto_id');
            }))
            ->orderBy('descripcion')
            ->get();

        $articulosPorAlmacen = $this->construirArticulosPorAlmacen($ubicaciones);

        // Suggested kit code
        $ultimoKit = Kit::orderByDesc('id')->first();
        $nextId = $ultimoKit ? ($ultimoKit->id + 1) : 1;
        $codigoSugerido = sprintf('KIT-%03d', $nextId);

        return view('kits.create', compact('articulos', 'ubicaciones', 'articulosPorAlmacen', 'codigoSugerido'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKitRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $ubicacion = ! empty($data['ubicacion_id']) ? Ubicacion::find($data['ubicacion_id']) : null;
        if (empty($data['proyecto_id'])) {
            $data['proyecto_id'] = $ubicacion?->proyecto_id ?? session('proyecto_activo_id');
        }
        $componentes = $data['componentes'] ?? [];
        unset($data['componentes']);

        DB::transaction(function () use ($data, $componentes, &$kit) {
            $kit = Kit::create($data);

            if (! empty($componentes)) {
                $syncData = [];
                foreach ($componentes as $comp) {
                    if (! empty($comp['articulo_id']) && ! empty($comp['cantidad'])) {
                        $syncData[$comp['articulo_id']] = ['cantidad' => (int) $comp['cantidad']];
                    }
                }
                $kit->articulos()->sync($syncData);
            }
        });

        $ubicacionMsg = $ubicacion ? " en el almacén {$ubicacion->nombre}" : '';

        return redirect()->route('kits.show', $kit)
            ->with('success', "Kit {$kit->nombre_kit} creado exitosamente{$ubicacionMsg}.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Kit $kit): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $kit->proyecto_id && ! in_array($kit->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para visualizar este kit.');
        }

        $kit->load(['ubicacion', 'proyecto', 'articulos.categoria', 'componentes.articulo.categoria']);
        $disponibilidad = $kit->evaluarDisponibilidadEnAlmacen($kit->ubicacion_id);

        return view('kits.show', compact('kit', 'disponibilidad'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kit $kit): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $kit->proyecto_id && ! in_array($kit->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para editar este kit.');
        }

        $kit->load(['componentes.articulo', 'ubicacion']);
        $proyectoId = $kit->proyecto_id ?? session('proyecto_activo_id');

        $ubicaciones = Ubicacion::where('estado', 'ACTIVO')
            ->when($proyectoId, fn ($q) => $q->where('proyecto_id', $proyectoId))
            ->when(! $proyectoId && $permitidos !== null, fn ($q) => $q->whereIn('proyecto_id', $permitidos))
            ->orderBy('nombre')
            ->get();

        if ($kit->ubicacion && ! $ubicaciones->contains('id', $kit->ubicacion_id)) {
            $ubicaciones->push($kit->ubicacion);
        }

        $articulos = Articulo::where('estado', 'ACTIVO')
            ->when($proyectoId, fn ($q, $pId) => $q->where('proyecto_id', $pId))
            ->orderBy('descripcion')
            ->get();

        $articulosPorAlmacen = $this->construirArticulosPorAlmacen($ubicaciones);

        return view('kits.edit', compact('kit', 'articulos', 'ubicaciones', 'articulosPorAlmacen'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKitRequest $request, Kit $kit): RedirectResponse
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $kit->proyecto_id && ! in_array($kit->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para modificar este kit.');
        }

        $data = $request->validated();
        if (! empty($data['ubicacion_id'])) {
            $ubicacion = Ubicacion::find($data['ubicacion_id']);
            if ($ubicacion?->proyecto_id) {
                $data['proyecto_id'] = $ubicacion->proyecto_id;
            }
        }
        $componentes = $data['componentes'] ?? [];
        unset($data['componentes']);

        DB::transaction(function () use ($data, $componentes, $kit) {
            $kit->update($data);

            $syncData = [];
            foreach ($componentes as $comp) {
                if (! empty($comp['articulo_id']) && ! empty($comp['cantidad'])) {
                    $syncData[$comp['articulo_id']] = ['cantidad' => (int) $comp['cantidad']];
                }
            }
            $kit->articulos()->sync($syncData);
        });

        return redirect()->route('kits.show', $kit)
            ->with('success', "Kit {$kit->nombre_kit} actualizado con éxito.");
    }

    /**
     * Desvincula un componente del kit para permitir su préstamo o traslado independiente
     * sin perjudicar la disponibilidad ni composición operativa del kit restante.
     */
    public function desvincularComponente(Request $request, Kit $kit, Articulo $articulo): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $kit->proyecto_id && ! in_array($kit->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para modificar este kit.');
        }

        $eliminados = $kit->componentes()->where('articulo_id', $articulo->id)->delete();

        $mensaje = $eliminados > 0
            ? "El ítem «{$articulo->codigo_sku} - {$articulo->descripcion}» fue desvinculado del kit «{$kit->codigo_kit}» para permitir su movimiento independiente sin perjudicar la composición del kit."
            : "El ítem «{$articulo->codigo_sku}» ya no formaba parte del kit «{$kit->codigo_kit}».";

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $mensaje,
                'kit_id' => $kit->id,
                'articulo_id' => $articulo->id,
            ]);
        }

        if ($request->boolean('ir_a_despacho')) {
            return redirect()->route('despachos.create', [
                'ubicacion_origen_id' => $kit->ubicacion_id,
                'articulo_id' => $articulo->id,
                'tipo_movimiento' => $request->input('tipo_movimiento', 'SALIDA_PRESTAMO'),
            ])->with('success', $mensaje);
        }

        return redirect()->route('kits.show', $kit)
            ->with('success', $mensaje);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kit $kit): RedirectResponse
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $kit->proyecto_id && ! in_array($kit->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para eliminar este kit.');
        }

        if ($kit->despachoDetalles()->exists()) {
            return back()->with('error', 'No se puede eliminar el kit porque ha sido despachado en operaciones previas.');
        }

        $nombre = $kit->nombre_kit;
        $kit->componentes()->delete();
        $kit->delete();

        return redirect()->route('kits.index')
            ->with('success', "Kit {$nombre} eliminado correctamente.");
    }

    /**
     * Construye el mapa de artículos disponibles agrupados por almacén (ubicacion_id).
     *
     * @param  Collection<int, Ubicacion>  $ubicaciones
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function construirArticulosPorAlmacen(Collection $ubicaciones): array
    {
        $ubicacionIds = $ubicaciones->pluck('id')->all();
        if (empty($ubicacionIds)) {
            return [];
        }

        $stocks = InventarioStock::with('articulo')
            ->whereIn('ubicacion_id', $ubicacionIds)
            ->where('cantidad_actual', '>', 0)
            ->get();

        $activosConteo = Activo::select('ubicacion_actual_id', 'articulo_id', DB::raw('COUNT(*) as total'))
            ->whereIn('ubicacion_actual_id', $ubicacionIds)
            ->where('estado_operativo', 'OPERATIVO')
            ->where('condicion_prestamo', 'DISPONIBLE')
            ->groupBy('ubicacion_actual_id', 'articulo_id')
            ->get();

        $mapa = [];
        foreach ($ubicacionIds as $uId) {
            $mapa[$uId] = [];
        }

        foreach ($stocks as $st) {
            $art = $st->articulo;
            if (! $art || $art->estado !== 'ACTIVO') {
                continue;
            }
            $mapa[$st->ubicacion_id][$art->id] = [
                'id' => $art->id,
                'codigo_sku' => $art->codigo_sku,
                'descripcion' => $art->descripcion,
                'unidad_medida' => $art->unidad_medida,
                'control_serie' => (bool) $art->control_serie,
                'stock_disponible' => (int) round((float) $st->cantidad_actual),
            ];
        }

        foreach ($activosConteo as $row) {
            $uId = (int) $row->ubicacion_actual_id;
            $aId = (int) $row->articulo_id;
            if (isset($mapa[$uId][$aId])) {
                $mapa[$uId][$aId]['stock_disponible'] = max($mapa[$uId][$aId]['stock_disponible'], (int) $row->total);
            } else {
                $art = Articulo::find($aId);
                if ($art && $art->estado === 'ACTIVO') {
                    $mapa[$uId][$aId] = [
                        'id' => $art->id,
                        'codigo_sku' => $art->codigo_sku,
                        'descripcion' => $art->descripcion,
                        'unidad_medida' => $art->unidad_medida,
                        'control_serie' => (bool) $art->control_serie,
                        'stock_disponible' => (int) $row->total,
                    ];
                }
            }
        }

        foreach ($mapa as $uId => $items) {
            $lista = array_values($items);
            usort($lista, fn ($a, $b) => strcmp($a['descripcion'], $b['descripcion']));
            $mapa[$uId] = $lista;
        }

        return $mapa;
    }
}
