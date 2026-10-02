<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ingreso\StoreIngresoRequest;
use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Ingreso;
use App\Models\IngresoDetalle;
use App\Models\InventarioStock;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use App\Services\KardexService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IngresoController extends Controller
{
    public function __construct(
        protected KardexService $kardexService
    ) {}

    /**
     * Display a listing of goods receipts (ingresos de almacén).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        $proyectoActivoId = session('proyecto_activo_id');
        $effectiveProyectoId = $request->input('proyecto_id') ?: $proyectoActivoId;

        $query = Ingreso::with(['ubicacion', 'proyecto', 'usuario', 'detalles.articulo'])
            ->withCount('detalles');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('codigo_ingreso', 'like', "%{$search}%")
                    ->orWhere('proveedor', 'like', "%{$search}%")
                    ->orWhere('numero_comprobante', 'like', "%{$search}%")
                    ->orWhereHas('detalles.articulo', function ($qArt) use ($search) {
                        $qArt->where('descripcion', 'like', "%{$search}%")
                            ->orWhere('codigo_sku', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('tipo_ingreso')) {
            $query->where('tipo_ingreso', $request->input('tipo_ingreso'));
        }

        if ($request->filled('ubicacion_id')) {
            $query->where('ubicacion_id', $request->input('ubicacion_id'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_ingreso', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_ingreso', '<=', $request->input('fecha_hasta'));
        }

        if ($effectiveProyectoId) {
            $query->where(function ($q) use ($effectiveProyectoId) {
                $q->where('proyecto_id', $effectiveProyectoId)
                    ->orWhereNull('proyecto_id');
            });
        } elseif ($permitidos !== null) {
            $query->where(function ($q) use ($permitidos) {
                $q->whereIn('proyecto_id', $permitidos)
                    ->orWhereNull('proyecto_id');
            });
        }

        $ingresos = $query->orderByDesc('fecha_ingreso')->orderByDesc('id')->paginate(15)->withQueryString();

        $ubicaciones = Ubicacion::where('estado', 'ACTIVO')
            ->when($effectiveProyectoId, function ($q) use ($effectiveProyectoId) {
                $q->where(function ($sub) use ($effectiveProyectoId) {
                    $sub->where('proyecto_id', $effectiveProyectoId)
                        ->orWhereNull('proyecto_id');
                });
            })
            ->orderByRaw('CASE WHEN tipo = "ALMACEN_CENTRAL" THEN 1 WHEN tipo = "CENTRO_ACOPIO" THEN 2 ELSE 3 END')
            ->orderBy('nombre')
            ->get();

        $proyectos = $user ? $user->proyectosPermitidosQuery()->orderBy('nombre')->get() : Proyecto::orderBy('nombre')->get();

        return view('ingresos.index', compact('ingresos', 'ubicaciones', 'proyectos'));
    }

    /**
     * Show the form for registering a new intake of goods.
     */
    public function create(): View
    {
        $user = auth()->user();
        $proyectoActivoId = session('proyecto_activo_id');

        $proyectos = $user
            ? $user->proyectosPermitidosQuery()->whereIn('estado', ['ACTIVO', 'PLANIFICACION', 'EN_EJECUCION'])->orderBy('nombre')->get()
            : Proyecto::whereIn('estado', ['ACTIVO', 'PLANIFICACION', 'EN_EJECUCION'])->orderBy('nombre')->get();

        $ubicaciones = Ubicacion::where('estado', 'ACTIVO')
            ->when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
                $q->where(function ($sub) use ($proyectoActivoId) {
                    $sub->where('proyecto_id', $proyectoActivoId)
                        ->orWhereNull('proyecto_id');
                });
            })
            ->orderByRaw('CASE WHEN tipo = "ALMACEN_CENTRAL" THEN 1 WHEN tipo = "CENTRO_ACOPIO" THEN 2 ELSE 3 END')
            ->orderBy('nombre')
            ->get();

        $categorias = Categoria::orderBy('nombre')->get();

        // Sugerencia de código automático
        $year = date('Y');
        $ultimo = Ingreso::whereYear('created_at', $year)->orderByDesc('id')->first();
        $nextNum = 1;
        if ($ultimo && preg_match('/-(\d+)$/', $ultimo->codigo_ingreso, $m)) {
            $nextNum = ((int) $m[1]) + 1;
        }
        $codigoSugerido = sprintf('ING-%s-%05d', $year, $nextNum);

        return view('ingresos.create', compact('ubicaciones', 'proyectos', 'categorias', 'codigoSugerido'));
    }

    /**
     * Real-time search for catalog articles.
     */
    public function buscarArticulo(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        $ubicacionId = $request->input('ubicacion_id');
        $proyectoId = $request->input('proyecto_id') ?: session('proyecto_activo_id');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $articulos = Articulo::with('categoria')
            ->where('estado', 'ACTIVO')
            ->where(function ($query) use ($q) {
                $query->where('codigo_sku', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%")
                    ->orWhere('marca', 'like', "%{$q}%")
                    ->orWhere('modelo', 'like', "%{$q}%");
            })
            ->when($proyectoId, function ($query) use ($proyectoId) {
                $query->where(function ($sub) use ($proyectoId) {
                    $sub->where('proyecto_id', $proyectoId)
                        ->orWhereNull('proyecto_id');
                });
            })
            ->limit(20)
            ->get();

        $stockMap = [];
        if ($ubicacionId && $articulos->isNotEmpty()) {
            $stockMap = InventarioStock::where('ubicacion_id', $ubicacionId)
                ->whereIn('articulo_id', $articulos->pluck('id'))
                ->pluck('cantidad_actual', 'articulo_id')
                ->toArray();
        }

        $results = $articulos->map(function (Articulo $art) use ($stockMap) {
            return [
                'id' => $art->id,
                'codigo_sku' => $art->codigo_sku,
                'descripcion' => $art->descripcion,
                'marca' => $art->marca ?? '',
                'modelo' => $art->modelo ?? '',
                'unidad_medida' => $art->unidad_medida,
                'tipo_articulo' => $art->tipo_articulo,
                'control_serie' => (bool) $art->control_serie,
                'es_instalable' => (bool) $art->es_instalable,
                'categoria' => $art->categoria?->nombre ?? 'General',
                'stock_actual' => (float) ($stockMap[$art->id] ?? 0.00),
            ];
        });

        return response()->json($results);
    }

    /**
     * Fast-create a new article directly from intake modal.
     */
    public function crearArticuloRapido(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigo_sku' => ['nullable', 'string', 'max:50', 'unique:articulos,codigo_sku'],
            'descripcion' => ['required', 'string', 'max:200'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'tipo_articulo' => ['required', 'in:EPP,HERRAMIENTA,EQUIPO,MATERIAL,CONSUMIBLE,OFICINA'],
            'unidad_medida' => ['required', 'string', 'max:20'],
            'control_serie' => ['nullable', 'boolean'],
            'es_instalable' => ['nullable', 'boolean'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'stock_minimo' => ['nullable', 'numeric', 'min:0'],
            'vida_util_meses' => ['nullable', 'integer', 'min:1'],
            'ubicacion_id' => ['nullable', 'exists:ubicaciones,id'],
        ]);

        $controlSerie = ! empty($validated['control_serie']);
        $esInstalable = $controlSerie && (! empty($validated['es_instalable']) || $validated['tipo_articulo'] === 'CONSUMIBLE');
        $esActivoSeriado = $controlSerie && ! $esInstalable;

        // Auto-generate SKU if omitted
        $sku = $validated['codigo_sku'] ?? null;
        if (empty($sku)) {
            $prefix = match ($validated['tipo_articulo']) {
                'EPP' => 'EPP',
                'HERRAMIENTA' => 'HER',
                'EQUIPO' => 'EQP',
                'MATERIAL' => 'MAT',
                'CONSUMIBLE' => 'CON',
                default => 'ART',
            };
            $ultimoId = (Articulo::max('id') ?? 0) + 1;
            $sku = sprintf('%s-%05d', $prefix, $ultimoId);
        }

        $articulo = Articulo::create([
            'categoria_id' => $validated['categoria_id'],
            'proyecto_id' => session('proyecto_activo_id'),
            'codigo_sku' => strtoupper($sku),
            'descripcion' => $validated['descripcion'],
            'marca' => $validated['marca'] ?? null,
            'modelo' => $validated['modelo'] ?? null,
            'unidad_medida' => strtoupper($validated['unidad_medida']),
            'tipo_articulo' => $validated['tipo_articulo'],
            'control_serie' => $controlSerie,
            'es_instalable' => $esInstalable,
            'stock_minimo' => $esActivoSeriado ? 0.00 : ($validated['stock_minimo'] ?? 0.00),
            'vida_util_meses' => $validated['vida_util_meses'] ?? ($controlSerie ? 36 : null),
            'estado' => 'ACTIVO',
        ]);

        $articulo->load('categoria');

        $stockActual = 0.00;
        if (! empty($validated['ubicacion_id'])) {
            $stockActual = (float) (InventarioStock::where('articulo_id', $articulo->id)
                ->where('ubicacion_id', $validated['ubicacion_id'])
                ->value('cantidad_actual') ?? 0.00);
        }

        return response()->json([
            'success' => true,
            'message' => "Artículo {$articulo->codigo_sku} registrado con éxito en el catálogo.",
            'articulo' => [
                'id' => $articulo->id,
                'codigo_sku' => $articulo->codigo_sku,
                'descripcion' => $articulo->descripcion,
                'marca' => $articulo->marca ?? '',
                'modelo' => $articulo->modelo ?? '',
                'unidad_medida' => $articulo->unidad_medida,
                'tipo_articulo' => $articulo->tipo_articulo,
                'control_serie' => (bool) $articulo->control_serie,
                'es_instalable' => (bool) $articulo->es_instalable,
                'categoria' => $articulo->categoria?->nombre ?? 'General',
                'stock_actual' => $stockActual,
            ],
        ]);
    }

    /**
     * Store intake in database and update inventory & serial assets.
     */
    public function store(StoreIngresoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = auth()->user();

        try {
            $ingreso = DB::transaction(function () use ($data, $user) {
                $year = date('Y');
                $ultimo = Ingreso::whereYear('created_at', $year)->lockForUpdate()->orderByDesc('id')->first();
                $nextNum = 1;
                if ($ultimo && preg_match('/-(\d+)$/', $ultimo->codigo_ingreso, $m)) {
                    $nextNum = ((int) $m[1]) + 1;
                }
                $codigoIngreso = sprintf('ING-%s-%05d', $year, $nextNum);

                $proyectoId = $data['proyecto_id'] ?? session('proyecto_activo_id');

                $ingreso = Ingreso::create([
                    'codigo_ingreso' => $codigoIngreso,
                    'tipo_ingreso' => $data['tipo_ingreso'],
                    'ubicacion_id' => $data['ubicacion_id'],
                    'proyecto_id' => $proyectoId,
                    'proveedor' => $data['proveedor'] ?? null,
                    'numero_comprobante' => $data['numero_comprobante'] ?? null,
                    'fecha_ingreso' => $data['fecha_ingreso'],
                    'usuario_id' => $user->id,
                    'observaciones' => $data['observaciones'] ?? null,
                ]);

                // Next global internal code counter for Activos
                $ultimoActivoId = Activo::max('id') ?? 0;

                foreach ($data['items'] as $itemData) {
                    $articulo = Articulo::findOrFail($itemData['articulo_id']);
                    $cantidad = (int) round((float) $itemData['cantidad']);

                    // Crear detalle del ingreso
                    $detalle = IngresoDetalle::create([
                        'ingreso_id' => $ingreso->id,
                        'articulo_id' => $articulo->id,
                        'cantidad' => $cantidad,
                        'costo_unitario' => null,
                        'observaciones' => $itemData['observaciones'] ?? null,
                    ]);

                    // Determinar tipo de movimiento para Kardex
                    $tipoKardex = match ($ingreso->tipo_ingreso) {
                        'COMPRA_NUEVA' => 'INGRESO_COMPRA',
                        'AJUSTE_SOBRANTE' => 'AJUSTE_SOBRANTE',
                        'TRANSFERENCIA_INGRESO' => 'TRANSFERENCIA_INGRESO',
                        'DONACION_TRASPASO' => 'INGRESO_COMPRA',
                        default => 'INGRESO_COMPRA',
                    };

                    $motivo = "Ingreso {$ingreso->codigo_ingreso} ({$ingreso->tipo_ingreso})";
                    if ($ingreso->proveedor) {
                        $motivo .= " - Prov: {$ingreso->proveedor}";
                    }
                    if ($ingreso->numero_comprobante) {
                        $motivo .= " - Doc: {$ingreso->numero_comprobante}";
                    }

                    // Actualizar inventario físico y registrar en Kardex
                    $this->kardexService->registrarMovimiento(
                        articuloId: $articulo->id,
                        ubicacionId: $ingreso->ubicacion_id,
                        tipoMovimiento: $tipoKardex,
                        cantidad: $cantidad,
                        usuarioId: $user->id,
                        despachoId: null,
                        motivo: $motivo,
                        ingresoId: $ingreso->id,
                        proyectoId: $ingreso->proyecto_id
                    );

                    // Si es seriado, registrar cada serie como Activo
                    if ($articulo->control_serie) {
                        $series = $itemData['series'] ?? [];
                        // Filtrar vacíos
                        $series = array_values(array_filter(array_map('trim', (array) $series)));

                        if (count($series) !== (int) $cantidad) {
                            throw new Exception("El artículo seriado '{$articulo->descripcion}' tiene cantidad {$cantidad}, pero se proporcionaron ".count($series).' números de serie.');
                        }

                        // Verificar que no existan duplicados dentro de la misma lista
                        if (count($series) !== count(array_unique($series))) {
                            throw new Exception("Existen números de serie duplicados en la lista del artículo '{$articulo->descripcion}'.");
                        }

                        foreach ($series as $serie) {
                            // Validar que la serie no exista previamente en el mismo artículo
                            $existeSerie = Activo::where('articulo_id', $articulo->id)
                                ->where('numero_serie', $serie)
                                ->exists();

                            if ($existeSerie) {
                                throw new Exception("El número de serie '{$serie}' ya se encuentra registrado para el artículo '{$articulo->descripcion}'.");
                            }

                            $ultimoActivoId++;
                            $codigoInterno = sprintf('ACT-%05d', $ultimoActivoId);

                            Activo::create([
                                'articulo_id' => $articulo->id,
                                'ingreso_id' => $ingreso->id,
                                'codigo_interno' => $codigoInterno,
                                'numero_serie' => $serie,
                                'ubicacion_actual_id' => $ingreso->ubicacion_id,
                                'proyecto_actual_id' => $ingreso->proyecto_id,
                                'estado_operativo' => 'OPERATIVO',
                                'condicion_prestamo' => 'DISPONIBLE',
                                'fecha_ingreso' => $ingreso->fecha_ingreso,
                                'observaciones' => "Ingreso {$ingreso->codigo_ingreso}".($ingreso->proveedor ? " | Proveedor: {$ingreso->proveedor}" : ''),
                            ]);
                        }
                    }
                }

                return $ingreso;
            });

            return redirect()->route('ingresos.show', $ingreso)
                ->with('success', "Ingreso {$ingreso->codigo_ingreso} registrado exitosamente. Se actualizó el stock y Kardex de almacén.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Error al procesar el ingreso de almacén: '.$e->getMessage());
        }
    }

    /**
     * Display the specified goods receipt voucher.
     */
    public function show(Ingreso $ingreso): View
    {
        $user = auth()->user();
        $permitidos = $user?->getProyectosPermitidosIds();
        if ($permitidos !== null && $ingreso->proyecto_id && ! in_array($ingreso->proyecto_id, $permitidos, true)) {
            abort(403, 'Acceso denegado: No tienes autorización para visualizar este ingreso.');
        }

        $ingreso->load([
            'ubicacion',
            'proyecto',
            'usuario',
            'detalles.articulo.categoria',
            'activos.articulo',
        ]);

        return view('ingresos.show', compact('ingreso'));
    }
}
