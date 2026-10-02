@extends('layouts.admin')

@section('title', 'Registrar Entrada / Ingreso de Almacén')
@section('page_title', 'Operaciones: Entrada / Ingreso de Almacén')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('ingresos.index') }}" class="text-decoration-none fw-semibold text-primary">Ingresos</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Nuevo Ingreso</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('ingresos.index') }}" class="btn btn-outline-secondary btn-sm px-3">
        <i class="bi bi-arrow-left me-1"></i> Volver al Historial
    </a>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <form action="{{ route('ingresos.store') }}" method="POST" id="form-ingreso">
        @csrf

        <div class="row g-4">
            <!-- 1. Cabecera del Ingreso -->
            <div class="col-lg-12">
                <div class="admin-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success bg-opacity-10 text-success p-2 rounded">
                                <i class="bi bi-box-arrow-in-down fs-5"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-0 text-heading">Datos Generales del Ingreso</h5>
                                <span class="small text-muted">Defina el almacén de destino, motivo y documentación de respaldo</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge badge-soft-primary font-monospace fs-6 px-3 py-2">
                                {{ $codigoSugerido }}
                            </span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="tipo_ingreso" class="form-label small fw-bold">Tipo de Ingreso <span class="text-danger">*</span></label>
                            <select name="tipo_ingreso" id="tipo_ingreso" class="form-select @error('tipo_ingreso') is-invalid @enderror" required>
                                <option value="COMPRA_NUEVA" {{ old('tipo_ingreso', 'COMPRA_NUEVA') == 'COMPRA_NUEVA' ? 'selected' : '' }}>COMPRA NUEVA</option>
                                <option value="AJUSTE_SOBRANTE" {{ old('tipo_ingreso') == 'AJUSTE_SOBRANTE' ? 'selected' : '' }}>AJUSTE SOBRANTE DE INVENTARIO</option>
                                <option value="TRANSFERENCIA_INGRESO" {{ old('tipo_ingreso') == 'TRANSFERENCIA_INGRESO' ? 'selected' : '' }}>TRANSFERENCIA ENTRE ALMACENES</option>
                                <option value="DONACION_TRASPASO" {{ old('tipo_ingreso') == 'DONACION_TRASPASO' ? 'selected' : '' }}>DONACIÓN / TRASPASO</option>
                            </select>
                            @error('tipo_ingreso')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="ubicacion_id" class="form-label small fw-bold">Almacén Destino (Físico) <span class="text-danger">*</span></label>
                            <select name="ubicacion_id" id="ubicacion_id" class="form-select @error('ubicacion_id') is-invalid @enderror" required>
                                <option value="">Seleccione almacén...</option>
                                @foreach($ubicaciones as $ubicacion)
                                    <option value="{{ $ubicacion->id }}" {{ old('ubicacion_id') == $ubicacion->id ? 'selected' : '' }}>
                                        {{ $ubicacion->nombre }} ({{ $ubicacion->tipo }})
                                    </option>
                                @endforeach
                            </select>
                            @error('ubicacion_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="proyecto_id" class="form-label small fw-bold">Proyecto Asociado</label>
                            <select name="proyecto_id" id="proyecto_id" class="form-select @error('proyecto_id') is-invalid @enderror">
                                <option value="">Sin proyecto específico (Almacén General)</option>
                                @foreach($proyectos as $proyecto)
                                    <option value="{{ $proyecto->id }}" {{ (old('proyecto_id', session('proyecto_activo_id')) == $proyecto->id) ? 'selected' : '' }}>
                                        [{{ $proyecto->codigo }}] {{ $proyecto->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proyecto_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="fecha_ingreso" class="form-label small fw-bold">Fecha de Ingreso <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_ingreso" id="fecha_ingreso" class="form-control @error('fecha_ingreso') is-invalid @enderror" value="{{ old('fecha_ingreso', date('Y-m-d')) }}" required>
                            @error('fecha_ingreso')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="proveedor" class="form-label small fw-bold">Proveedor / Origen</label>
                            <input type="text" name="proveedor" id="proveedor" class="form-control @error('proveedor') is-invalid @enderror" placeholder="Ej: FiberTech S.A.C., Cisco Systems, Ferretería..." value="{{ old('proveedor') }}">
                            @error('proveedor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="numero_comprobante" class="form-label small fw-bold">N° Comprobante / Guía / Factura</label>
                            <input type="text" name="numero_comprobante" id="numero_comprobante" class="form-control @error('numero_comprobante') is-invalid @enderror" placeholder="Ej: F001-003456 / GR-2026-98" value="{{ old('numero_comprobante') }}">
                            @error('numero_comprobante')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="observaciones" class="form-label small fw-bold">Observaciones Generales</label>
                            <input type="text" name="observaciones" id="observaciones" class="form-control @error('observaciones') is-invalid @enderror" placeholder="Detalles de la recepción, precintos, lote..." value="{{ old('observaciones') }}">
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Buscador en Tiempo Real & Catálogo -->
            <div class="col-lg-12">
                <div class="admin-card p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                        <div>
                            <h5 class="fw-bold mb-0 text-heading">
                                <i class="bi bi-search text-primary me-1"></i> Buscador de Artículos en Tiempo Real
                            </h5>
                            <span class="small text-muted">Escriba para verificar si el artículo ya existe y añadir cantidad, o regístrelo rápidamente</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-success btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoArticulo">
                                <i class="bi bi-plus-circle me-1"></i> + Nuevo Artículo en Catálogo
                            </button>
                        </div>
                    </div>

                    <div class="position-relative mb-3">
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white border-end-0 text-primary">
                                <i class="bi bi-search fs-5"></i>
                            </span>
                            <input type="text" id="input-buscar-articulo" class="form-control border-start-0 ps-0" placeholder="Buscar por código SKU, descripción, marca o modelo (ej: Switch, Patchcord, Conector, Taladro...)" autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="btn-limpiar-busqueda" style="display: none;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <!-- Dropdown de resultados de búsqueda en tiempo real -->
                        <div id="resultados-busqueda" class="position-absolute w-100 bg-white border rounded shadow-lg mt-1 p-2" style="z-index: 1050; max-height: 380px; overflow-y: auto; display: none;">
                            <div id="lista-resultados"></div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-0">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <div>
                            <strong>Control Serializado e Instalable:</strong> Para equipos como <em>Switches, Routers, ONTs, Antenas</em> que se instalan y quedan en obra, asegúrese de marcar la opción <strong>"Material Seriado Instalable"</strong> para distinguirlos de herramientas operativas en préstamo.
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Tabla Dinámica de Artículos a Ingresar -->
            <div class="col-lg-12">
                <div class="admin-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-bold mb-0 text-heading">
                                <i class="bi bi-table text-primary me-1"></i> Artículos / Bienes a Ingresar (<span id="contador-filas">0</span>)
                            </h5>
                            <span class="small text-muted">Ajuste la cantidad a ingresar; el sistema calculará en tiempo real el stock resultante</span>
                        </div>
                        <div id="badge-total-items" class="badge badge-soft-primary px-3 py-2 fs-6">
                            0 Unidades en Recepción
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="tabla-ingresos">
                            <thead class="table-light small text-uppercase text-muted">
                                <tr>
                                    <th style="width: 38%;">Artículo & Catálogo</th>
                                    <th class="text-center" style="width: 16%;">Control & Tipo</th>
                                    <th class="text-end" style="width: 14%;">Stock Actual</th>
                                    <th class="text-end" style="width: 16%;">Cant. a Ingresar <span class="text-danger">*</span></th>
                                    <th class="text-end" style="width: 10%;">Stock Final</th>
                                    <th class="text-center" style="width: 6%;">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-ingresos">
                                <tr id="fila-vacia">
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-box-arrow-in-down fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        No hay artículos agregados al ingreso todavía.
                                        <div class="small">Utilice el buscador superior para agregar materiales, consumibles o herramientas.</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-4">
                        <div class="text-muted small">
                            <i class="bi bi-shield-check text-success me-1"></i> Cada entrada actualizará de forma atómica el stock físico del almacén y generará su asiento en el Kardex.
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('ingresos.index') }}" class="btn btn-outline-secondary px-3">
                                Cancelar
                            </a>
                            <button type="submit" id="btn-guardar-ingreso" class="btn btn-success px-4 fw-bold shadow" disabled>
                                <i class="bi bi-check-circle me-1"></i> Guardar & Procesar Ingreso
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal para Pegado Masivo de Series -->
    <div class="modal fade" id="modalPegarSeries" tabindex="-1" aria-labelledby="modalPegarSeriesLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="modalPegarSeriesLabel">
                        <i class="bi bi-clipboard-pulse text-primary me-1"></i> Pegar Números de Serie en Bloque
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">
                        Pegue los números de serie desde Excel o un lector de código de barras, separados por saltos de línea, comas o espacios:
                    </p>
                    <textarea id="textarea-series-bloque" class="form-control font-monospace" rows="8" placeholder="SN1002345&#10;SN1002346&#10;SN1002347..."></textarea>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="small text-muted" id="contador-series-pegadas">0 series detectadas</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary btn-sm fw-bold" id="btn-aplicar-series-bloque">
                        <i class="bi bi-check2-all me-1"></i> Aplicar a los Campos
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear Nuevo Artículo Rápido en Catálogo -->
    <div class="modal fade" id="modalNuevoArticulo" tabindex="-1" aria-labelledby="modalNuevoArticuloLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-heading" id="modalNuevoArticuloLabel">
                        <i class="bi bi-plus-circle text-success me-1"></i> Registrar Nuevo Artículo en Catálogo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="form-crear-articulo-rapido">
                    <div class="modal-body">
                        <div id="alerta-error-modal" class="alert alert-danger py-2 small d-none"></div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Código SKU / Parte</label>
                                <input type="text" id="modal_codigo_sku" class="form-control form-control-sm text-uppercase" placeholder="Dejar vacío para autogenerar">
                                <div class="form-text small">Opcional. Se asignará uno correlativo si se omite.</div>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Descripción del Artículo / Material <span class="text-danger">*</span></label>
                                <input type="text" id="modal_descripcion" class="form-control form-control-sm" placeholder="Ej: Switch Cisco Catalyst 24 Puertos Gigabit PoE" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Categoría <span class="text-danger">*</span></label>
                                <select id="modal_categoria_id" class="form-select form-select-sm" required>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tipo de Artículo <span class="text-danger">*</span></label>
                                <select id="modal_tipo_articulo" class="form-select form-select-sm" required>
                                    <option value="MATERIAL" selected>MATERIAL</option>
                                    <option value="CONSUMIBLE">CONSUMIBLE</option>
                                    <option value="HERRAMIENTA">HERRAMIENTA</option>
                                    <option value="EQUIPO">EQUIPO</option>
                                    <option value="EPP">EPP</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Unidad de Medida <span class="text-danger">*</span></label>
                                <input type="text" id="modal_unidad_medida" class="form-control form-control-sm text-uppercase" value="UND" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Marca</label>
                                <input type="text" id="modal_marca" class="form-control form-control-sm" placeholder="Ej: Cisco, Mikrotik, Truper...">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Modelo</label>
                                <input type="text" id="modal_modelo" class="form-control form-control-sm" placeholder="Ej: WS-C2960X-24PS-L">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Stock Mínimo de Alerta</label>
                                <input type="number" step="1" min="0" id="modal_stock_minimo" class="form-control form-control-sm" value="2">
                            </div>

                            <div class="col-md-12">
                                <div class="p-3 border rounded bg-light bg-opacity-50">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="modal_control_serie" value="1">
                                        <label class="form-check-label fw-bold" for="modal_control_serie">
                                            Control por Número de Serie / QR Individual
                                        </label>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Active esta opción si cada unidad física tiene número de serie de fábrica y debe registrarse como Activo individual.
                                    </p>

                                    <div class="form-check form-switch pt-2 border-top">
                                        <input class="form-check-input" type="checkbox" role="switch" id="modal_es_instalable" value="1">
                                        <label class="form-check-label fw-semibold text-info-emphasis" for="modal_es_instalable">
                                            <i class="bi bi-hdd-network me-1"></i> Material Seriado Instalable en Obra (Consumible en Proyecto)
                                        </label>
                                        <div class="form-text small text-muted">
                                            Marque para routers, switches, ONTs, patch panels que se controlan por serie pero se quedan permanentemente instalados en el sitio del proyecto.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btn-submit-articulo-rapido" class="btn btn-success btn-sm fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Crear & Agregar al Ingreso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputBuscar = document.getElementById('input-buscar-articulo');
    const btnLimpiar = document.getElementById('btn-limpiar-busqueda');
    const cajaResultados = document.getElementById('resultados-busqueda');
    const listaResultados = document.getElementById('lista-resultados');
    const selectUbicacion = document.getElementById('ubicacion_id');
    const selectProyecto = document.getElementById('proyecto_id');
    const tbodyIngresos = document.getElementById('tbody-ingresos');
    const filaVacia = document.getElementById('fila-vacia');
    const contadorFilas = document.getElementById('contador-filas');
    const badgeTotalItems = document.getElementById('badge-total-items');
    const btnGuardar = document.getElementById('btn-guardar-ingreso');

    let debounceTimer = null;
    let itemsAgregados = new Map(); // id -> item data
    let filaIndexCounter = 0;
    let targetRowForBatchSerials = null;

    // Actualizar stock de los ítems en la tabla si cambia el almacén seleccionado
    selectUbicacion.addEventListener('change', function() {
        // Podríamos refrescar stock de filas ya agregadas
        actualizarTotales();
    });

    // 1. Buscador en tiempo real
    inputBuscar.addEventListener('input', function() {
        const query = this.value.trim();
        if (query.length > 0) {
            btnLimpiar.style.display = 'block';
        } else {
            btnLimpiar.style.display = 'none';
            cajaResultados.style.display = 'none';
            return;
        }

        if (query.length < 2) {
            cajaResultados.style.display = 'none';
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            ejecutarBusqueda(query);
        }, 220);
    });

    btnLimpiar.addEventListener('click', function() {
        inputBuscar.value = '';
        btnLimpiar.style.display = 'none';
        cajaResultados.style.display = 'none';
        inputBuscar.focus();
    });

    // Cerrar resultados al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!cajaResultados.contains(e.target) && e.target !== inputBuscar) {
            cajaResultados.style.display = 'none';
        }
    });

    function ejecutarBusqueda(query) {
        const ubicacionId = selectUbicacion.value;
        const proyectoId = selectProyecto.value;

        const url = new URL("{{ route('ingresos.buscar-articulo') }}", window.location.origin);
        url.searchParams.append('q', query);
        if (ubicacionId) url.searchParams.append('ubicacion_id', ubicacionId);
        if (proyectoId) url.searchParams.append('proyecto_id', proyectoId);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                renderizarResultados(data);
            })
            .catch(err => {
                console.error("Error al buscar artículos:", err);
            });
    }

    function renderizarResultados(articulos) {
        listaResultados.innerHTML = '';

        if (!articulos || articulos.length === 0) {
            listaResultados.innerHTML = `
                <div class="text-center py-3 text-muted">
                    <i class="bi bi-search me-1"></i> No se encontró ningún artículo para "<strong>${escapeHtml(inputBuscar.value)}</strong>".
                    <div class="mt-2">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="abrirModalConNombre('${escapeHtml(inputBuscar.value)}')">
                            <i class="bi bi-plus-circle me-1"></i> Registrar como Nuevo Artículo
                        </button>
                    </div>
                </div>
            `;
            cajaResultados.style.display = 'block';
            return;
        }

        articulos.forEach(art => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'd-flex justify-content-between align-items-center p-2 rounded hover-item border-bottom cursor-pointer';
            itemDiv.style.cursor = 'pointer';

            const yaAgregado = itemsAgregados.has(art.id);

            let badgesHtml = `<span class="badge badge-soft-secondary me-1">${art.tipo_articulo}</span>`;
            if (art.control_serie) {
                badgesHtml += `<span class="badge badge-soft-success me-1"><i class="bi bi-qr-code"></i> Seriado</span>`;
            }
            if (art.es_instalable) {
                badgesHtml += `<span class="badge badge-soft-info me-1"><i class="bi bi-hdd-network"></i> Instalable en Obra</span>`;
            }

            itemDiv.innerHTML = `
                <div class="me-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="font-monospace fw-bold text-primary">${art.codigo_sku}</span>
                        ${badgesHtml}
                    </div>
                    <div class="fw-semibold text-heading small mt-1">${escapeHtml(art.descripcion)}</div>
                    <div class="small text-muted">${art.marca ? art.marca + ' ' : ''}${art.modelo || ''}</div>
                </div>
                <div class="text-end">
                    <div class="small text-muted mb-1">Stock Actual:</div>
                    <span class="badge ${art.stock_actual > 0 ? 'bg-success' : 'bg-secondary'} bg-opacity-25 text-dark fw-bold px-2 py-1">
                        ${Math.round(parseFloat(art.stock_actual) || 0)} ${art.unidad_medida}
                    </span>
                    <div class="mt-1">
                        ${yaAgregado 
                            ? `<span class="badge bg-warning text-dark"><i class="bi bi-check2"></i> En lista</span>` 
                            : `<button type="button" class="btn btn-primary btn-sm py-0 px-2 fw-semibold"><i class="bi bi-plus"></i> Añadir</button>`}
                    </div>
                </div>
            `;

            itemDiv.addEventListener('click', function() {
                agregarArticuloATabla(art);
                cajaResultados.style.display = 'none';
                inputBuscar.value = '';
                btnLimpiar.style.display = 'none';
            });

            listaResultados.appendChild(itemDiv);
        });

        cajaResultados.style.display = 'block';
    }

    // 2. Agregar artículo a la tabla
    function agregarArticuloATabla(art, cantidadInicial = 1) {
        if (itemsAgregados.has(art.id)) {
            // Ya está en la tabla, enfocar su campo de cantidad e incrementar
            const rowId = itemsAgregados.get(art.id).rowId;
            const inputCant = document.querySelector(`input[name="items[${rowId}][cantidad]"]`);
            if (inputCant) {
                inputCant.value = Math.round(parseFloat(inputCant.value || 0)) + 1;
                inputCant.dispatchEvent(new Event('input'));
                inputCant.focus();
            }
            return;
        }

        const idx = filaIndexCounter++;
        itemsAgregados.set(art.id, {
            articulo: art,
            rowId: idx
        });

        if (filaVacia) filaVacia.style.display = 'none';

        const tr = document.createElement('tr');
        tr.id = `fila-item-${idx}`;
        tr.className = 'item-ingreso-row';

        let badgeControl = '';
        if (art.control_serie) {
            badgeControl += `<span class="badge badge-soft-success font-monospace mb-1 d-block"><i class="bi bi-qr-code"></i> Seriado</span>`;
        } else {
            badgeControl += `<span class="badge badge-soft-secondary mb-1 d-block"><i class="bi bi-box"></i> A Granel</span>`;
        }

        if (art.es_instalable) {
            badgeControl += `<span class="badge badge-soft-info font-monospace d-block" title="Material seriado instalable permanentemente en obra"><i class="bi bi-hdd-network"></i> Instalable</span>`;
        }

        tr.innerHTML = `
            <input type="hidden" name="items[${idx}][articulo_id]" value="${art.id}">
            
            <td>
                <div class="d-flex align-items-start gap-2">
                    <div>
                        <div class="font-monospace fw-bold text-primary">${art.codigo_sku}</div>
                        <div class="fw-semibold text-heading">${escapeHtml(art.descripcion)}</div>
                        <div class="small text-muted">${art.marca ? art.marca + ' ' : ''}${art.modelo || ''}</div>
                    </div>
                </div>
                ${art.control_serie ? `
                    <div class="mt-2 pt-2 border-top contenedor-series-row" id="contenedor-series-${idx}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-bold text-secondary">
                                <i class="bi bi-upc-scan me-1"></i> Números de Serie / QR (<span class="contador-series-num">0</span>/${cantidadInicial}):
                            </span>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small btn-pegar-series" data-row="${idx}">
                                <i class="bi bi-clipboard-pulse"></i> Pegar en bloque
                            </button>
                        </div>
                        <div class="lista-inputs-series row g-1"></div>
                    </div>
                ` : ''}
            </td>

            <td class="text-center">
                <span class="badge badge-soft-primary small mb-1">${art.tipo_articulo}</span>
                ${badgeControl}
            </td>

            <td class="text-end">
                <span class="fw-bold font-monospace text-secondary span-stock-actual" data-stock="${art.stock_actual}">
                    ${Math.round(parseFloat(art.stock_actual) || 0)}
                </span>
                <span class="small text-muted">${art.unidad_medida}</span>
            </td>

            <td>
                <div class="input-group input-group-sm">
                    <input type="number" 
                           step="1" 
                           min="1" 
                           name="items[${idx}][cantidad]" 
                           class="form-control text-end fw-bold input-cantidad" 
                           value="${Math.max(1, Math.round(parseFloat(cantidadInicial) || 1))}" 
                           required>
                    <span class="input-group-text small">${art.unidad_medida}</span>
                </div>
            </td>

            <td class="text-end">
                <span class="fw-bold font-monospace text-success span-stock-final">
                    ${Math.round((parseFloat(art.stock_actual) || 0) + (parseFloat(cantidadInicial) || 1))}
                </span>
                <span class="small text-muted">${art.unidad_medida}</span>
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar-fila" data-art-id="${art.id}" data-row="${idx}" title="Eliminar fila">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbodyIngresos.appendChild(tr);

        // Si es seriado, generar los inputs iniciales de serie
        if (art.control_serie) {
            generarInputsSeries(idx, cantidadInicial);
        }

        // Listener en input cantidad
        const inputCant = tr.querySelector('.input-cantidad');
        inputCant.addEventListener('input', function() {
            let val = Math.max(1, Math.round(parseFloat(this.value) || 0));
            this.value = val;
            if (art.control_serie) {
                generarInputsSeries(idx, val);
            }
            const stockActual = Math.round(parseFloat(art.stock_actual) || 0);
            const stockFinal = stockActual + val;
            tr.querySelector('.span-stock-final').textContent = stockFinal;
            actualizarTotales();
        });

        // Listener en botón eliminar
        tr.querySelector('.btn-eliminar-fila').addEventListener('click', function() {
            tr.remove();
            itemsAgregados.delete(art.id);
            if (itemsAgregados.size === 0 && filaVacia) {
                filaVacia.style.display = '';
            }
            actualizarTotales();
        });

        // Listener para pegar series
        if (art.control_serie) {
            tr.querySelector('.btn-pegar-series').addEventListener('click', function() {
                targetRowForBatchSerials = idx;
                document.getElementById('textarea-series-bloque').value = '';
                document.getElementById('contador-series-pegadas').textContent = '0 series detectadas';
                const modal = new bootstrap.Modal(document.getElementById('modalPegarSeries'));
                modal.show();
            });
        }

        actualizarTotales();
    }

    // 3. Generador de inputs individuales para números de serie
    function generarInputsSeries(rowIdx, cantidad) {
        const contenedor = document.getElementById(`contenedor-series-${rowIdx}`);
        if (!contenedor) return;

        const lista = contenedor.querySelector('.lista-inputs-series');
        const contadorSpan = contenedor.querySelector('.contador-series-num');
        const cant = parseInt(cantidad) || 0;

        // Guardar valores ya escritos para preservarlos
        const valoresExistentes = [];
        lista.querySelectorAll('input').forEach(inp => {
            if (inp.value.trim() !== '') valoresExistentes.push(inp.value.trim());
        });

        lista.innerHTML = '';
        for (let i = 0; i < cant; i++) {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4';
            const val = valoresExistentes[i] || '';
            col.innerHTML = `
                <div class="input-group input-group-sm">
                    <span class="input-group-text font-monospace small bg-light text-muted">#${i + 1}</span>
                    <input type="text" 
                           name="items[${rowIdx}][series][${i}]" 
                           class="form-control font-monospace input-serie-item" 
                           placeholder="SN / Código de Fábrica" 
                           value="${escapeHtml(val)}" 
                           required>
                </div>
            `;
            lista.appendChild(col);
        }

        contadorSpan.textContent = lista.querySelectorAll('.input-serie-item').length;
    }

    // 4. Modal para pegar series en bloque
    const textareaBloque = document.getElementById('textarea-series-bloque');
    const contadorPegadas = document.getElementById('contador-series-pegadas');

    textareaBloque.addEventListener('input', function() {
        const series = extraerSeriesDeTexto(this.value);
        contadorPegadas.textContent = `${series.length} series detectadas`;
    });

    document.getElementById('btn-aplicar-series-bloque').addEventListener('click', function() {
        if (targetRowForBatchSerials === null) return;
        const series = extraerSeriesDeTexto(textareaBloque.value);
        if (series.length === 0) {
            alert("No se detectó ningún número de serie en el texto.");
            return;
        }

        const contenedor = document.getElementById(`contenedor-series-${targetRowForBatchSerials}`);
        if (!contenedor) return;

        // Si hay más series pegadas que la cantidad actual, auto-actualizar la cantidad
        const fila = document.getElementById(`fila-item-${targetRowForBatchSerials}`);
        const inputCant = fila.querySelector('.input-cantidad');
        if (series.length > parseInt(inputCant.value || 0)) {
            inputCant.value = series.length;
            inputCant.dispatchEvent(new Event('input'));
        }

        const inputs = contenedor.querySelectorAll('.input-serie-item');
        inputs.forEach((inp, idx) => {
            if (series[idx]) {
                inp.value = series[idx];
            }
        });

        bootstrap.Modal.getInstance(document.getElementById('modalPegarSeries')).hide();
    });

    function extraerSeriesDeTexto(texto) {
        if (!texto) return [];
        return texto.split(/[\r\n,;\t]+/)
            .map(s => s.trim())
            .filter(s => s.length > 0);
    }

    // 5. Actualizar totales y estado del botón submit
    function actualizarTotales() {
        const totalItems = itemsAgregados.size;
        contadorFilas.textContent = totalItems;

        let sumaUnidades = 0;
        document.querySelectorAll('.input-cantidad').forEach(inp => {
            sumaUnidades += Math.round(parseFloat(inp.value) || 0);
        });

        badgeTotalItems.textContent = `${sumaUnidades} Unidades en Recepción`;

        if (totalItems > 0 && sumaUnidades > 0) {
            btnGuardar.removeAttribute('disabled');
        } else {
            btnGuardar.setAttribute('disabled', 'disabled');
        }
    }

    // 6. Modal de creación rápida de nuevo artículo
    const formArticuloRapido = document.getElementById('form-crear-articulo-rapido');
    const alertaErrorModal = document.getElementById('alerta-error-modal');
    const btnSubmitArticulo = document.getElementById('btn-submit-articulo-rapido');

    window.abrirModalConNombre = function(nombre) {
        cajaResultados.style.display = 'none';
        document.getElementById('modal_descripcion').value = nombre || '';
        const modal = new bootstrap.Modal(document.getElementById('modalNuevoArticulo'));
        modal.show();
    };

    formArticuloRapido.addEventListener('submit', function(e) {
        e.preventDefault();
        alertaErrorModal.classList.add('d-none');
        btnSubmitArticulo.disabled = true;
        btnSubmitArticulo.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creando...';

        const payload = {
            _token: "{{ csrf_token() }}",
            codigo_sku: document.getElementById('modal_codigo_sku').value.trim() || null,
            descripcion: document.getElementById('modal_descripcion').value.trim(),
            categoria_id: document.getElementById('modal_categoria_id').value,
            tipo_articulo: document.getElementById('modal_tipo_articulo').value,
            unidad_medida: document.getElementById('modal_unidad_medida').value.trim(),
            marca: document.getElementById('modal_marca').value.trim() || null,
            modelo: document.getElementById('modal_modelo').value.trim() || null,
            stock_minimo: document.getElementById('modal_stock_minimo').value || 0,
            control_serie: document.getElementById('modal_control_serie').checked ? 1 : 0,
            es_instalable: document.getElementById('modal_es_instalable').checked ? 1 : 0,
            ubicacion_id: selectUbicacion.value || null
        };

        fetch("{{ route('ingresos.crear-articulo-rapido') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            btnSubmitArticulo.disabled = false;
            btnSubmitArticulo.innerHTML = '<i class="bi bi-check-circle me-1"></i> Crear & Agregar al Ingreso';

            if (data.success && data.articulo) {
                // Agregar inmediatamente a la tabla
                agregarArticuloATabla(data.articulo, 1);
                // Resetear form y cerrar modal
                formArticuloRapido.reset();
                bootstrap.Modal.getInstance(document.getElementById('modalNuevoArticulo')).hide();
            } else {
                alertaErrorModal.textContent = data.message || "Error al registrar el artículo.";
                alertaErrorModal.classList.remove('d-none');
            }
        })
        .catch(err => {
            btnSubmitArticulo.disabled = false;
            btnSubmitArticulo.innerHTML = '<i class="bi bi-check-circle me-1"></i> Crear & Agregar al Ingreso';
            alertaErrorModal.textContent = "Error al procesar la solicitud. Verifique que los datos sean válidos.";
            alertaErrorModal.classList.remove('d-none');
            console.error(err);
        });
    });

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
</script>
@endpush
