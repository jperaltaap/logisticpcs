@extends('layouts.admin')

@section('title', 'Centro de Reportes & Exportaciones')

@section('content')
<div class="container-fluid py-3">

    <!-- Header & Breadcrumbs -->
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-spreadsheet-fill text-success"></i>
                Centro de Reportes & Exportaciones
            </h1>
            <p class="text-secondary small mb-0">
                Generación de libros contables en Microsoft Excel (.xlsx) y emisión de informes ejecutivos en PDF para auditoría y frentes de obra.
            </p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <span class="badge badge-soft-info p-2 px-3 fs-6">
                <i class="bi bi-shield-check me-1"></i> Conforme a Normas de Control Interno
            </span>
        </div>
    </div>

    <!-- Quick Stats Metric Bar -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">Catálogo Ítems</span>
                    <h4 class="fw-bold mb-0 text-primary">{{ number_format($metrics['total_articulos']) }}</h4>
                    <span class="badge badge-soft-primary mt-1" style="font-size: 0.68rem;">SKUs Únicos</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">Activos Serializados</span>
                    <h4 class="fw-bold mb-0 text-indigo" style="color: #6366f1;">{{ number_format($metrics['total_activos']) }}</h4>
                    <span class="badge badge-soft-info mt-1" style="font-size: 0.68rem;">Con Placa QR</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">Mov. Kardex</span>
                    <h4 class="fw-bold mb-0 text-success">{{ number_format($metrics['movimientos_kardex']) }}</h4>
                    <span class="badge badge-soft-success mt-1" style="font-size: 0.68rem;">Transacciones</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">Turnos Roster</span>
                    <h4 class="fw-bold mb-0 text-info">{{ number_format($metrics['turnos_roster']) }}</h4>
                    <span class="badge badge-soft-info mt-1" style="font-size: 0.68rem;">Jornada 14x7</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">Cuadrillas en Obra</span>
                    <h4 class="fw-bold mb-0 text-purple" style="color: #a855f7;">{{ number_format($metrics['cuadrillas_activas']) }}</h4>
                    <span class="badge badge-soft-secondary mt-1" style="font-size: 0.68rem;">Frentes Activos</span>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">Bajo Stock Mínimo</span>
                    <h4 class="fw-bold mb-0 text-warning">{{ number_format($metrics['items_bajo_stock']) }}</h4>
                    <span class="badge badge-soft-warning mt-1" style="font-size: 0.68rem;">Reposición Requerida</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="row g-4">

        <!-- Reporte 1: Inventario & Stock Físico -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-2 bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-box-seam fs-5"></i>
                        </span>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">1. Inventario & Existencias de Almacén</h5>
                            <small class="text-secondary">Saldos físicos actuales, niveles mínimos y estados de stock</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('reportes.export.inventario') }}" method="GET" id="formInventario">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Centro de Almacén</label>
                                <select name="ubicacion_id" class="form-select form-select-sm">
                                    <option value="">-- Todos los Almacenes --</option>
                                    @foreach($ubicaciones as $ub)
                                        <option value="{{ $ub->id }}">{{ $ub->nombre }} ({{ $ub->codigo }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Categoría de Bienes</label>
                                <select name="categoria_id" class="form-select form-select-sm">
                                    <option value="">-- Todas las Categorías --</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Buscar por SKU o Descripción</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Ej. Bobina, Multímetro, Casco...">
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                            <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-2 px-3 shadow-sm">
                                <i class="bi bi-file-earmark-excel"></i> Descargar Excel (.xlsx)
                            </button>
                            <a href="#" onclick="descargarPdfInventario(false); return false;" class="btn btn-sm btn-danger d-flex align-items-center gap-2 px-3 shadow-sm">
                                <i class="bi bi-file-earmark-pdf"></i> Descargar PDF A4
                            </a>
                            <a href="#" onclick="descargarPdfInventario(true); return false;" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                <i class="bi bi-eye"></i> Vista Previa PDF
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reporte 2: Kardex Transaccional PEPS -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-2 bg-success bg-opacity-10 text-success">
                            <i class="bi bi-arrow-left-right fs-5"></i>
                        </span>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">2. Kardex & Trazabilidad Histórica</h5>
                            <small class="text-secondary">Libro cronológico de entradas, salidas de campo y retornos</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('reportes.export.kardex') }}" method="GET">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Almacén / Ubicación</label>
                                <select name="ubicacion_id" class="form-select form-select-sm">
                                    <option value="">-- Todos los Almacenes --</option>
                                    @foreach($ubicaciones as $ub)
                                        <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Tipo de Operación</label>
                                <select name="tipo_movimiento" class="form-select form-select-sm">
                                    <option value="">-- Todos los Movimientos --</option>
                                    <option value="INGRESO_COMPRA">INGRESO POR COMPRA</option>
                                    <option value="SALIDA_CONSUMO">SALIDA DE CONSUMO</option>
                                    <option value="SALIDA_PRESTAMO">SALIDA PRÉSTAMO CAMPO</option>
                                    <option value="RETORNO_PRESTAMO">RETORNO DE PRÉSTAMO</option>
                                    <option value="TRANSFERENCIA_INGRESO">TRANSFERENCIA INGRESO</option>
                                    <option value="TRANSFERENCIA_SALIDA">TRANSFERENCIA SALIDA</option>
                                    <option value="AJUSTE_SOBRANTE">AJUSTE SOBRANTE</option>
                                    <option value="AJUSTE_FALTANTE">AJUSTE FALTANTE</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="{{ now()->startOfMonth()->toDateString() }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Fecha Fin</label>
                                <input type="date" name="fecha_fin" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                            <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-2 px-3 shadow-sm">
                                <i class="bi bi-file-earmark-excel"></i> Exportar Kardex a Excel (.xlsx)
                            </button>
                            <a href="{{ route('kardex.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                <i class="bi bi-search"></i> Ver en Tabla Interactiva
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reporte 3: Roster 14x7 & Guardia Operativa -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-2 bg-info bg-opacity-10 text-info">
                            <i class="bi bi-calendar3 fs-5"></i>
                        </span>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">3. Roster & Jornadas 14x7</h5>
                            <small class="text-secondary">Programación de turnos, guardias A/B, campo y descansos</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('reportes.export.roster') }}" method="GET">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Proyecto de Obra</label>
                                <select name="proyecto_id" class="form-select form-select-sm">
                                    <option value="">-- Todos los Proyectos --</option>
                                    @foreach($proyectos as $pry)
                                        <option value="{{ $pry->id }}">{{ $pry->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Grupo de Guardia</label>
                                <select name="grupo_guardia" class="form-select form-select-sm">
                                    <option value="">-- Todas las Guardias --</option>
                                    <option value="GUARDIA A">GUARDIA A</option>
                                    <option value="GUARDIA B">GUARDIA B</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Mes</label>
                                <select name="mes" class="form-select form-select-sm">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $m, 1)->locale('es')->monthName }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Año</label>
                                <input type="number" name="anio" class="form-control form-control-sm" value="{{ now()->year }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Condición</label>
                                <select name="condicion_laboral" class="form-select form-select-sm">
                                    <option value="">-- Todas --</option>
                                    <option value="TRABAJO_CAMPO">TRABAJO EN CAMPO</option>
                                    <option value="BAJADA_DESCANSO">BAJADA DESCANSO</option>
                                    <option value="DESCANSO_CAMPAMENTO">EN CAMPAMENTO</option>
                                    <option value="PERMISO">PERMISO</option>
                                    <option value="LICENCIA_MEDICA">LICENCIA MÉDICA</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                            <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-2 px-3 shadow-sm">
                                <i class="bi bi-file-earmark-excel"></i> Exportar Roster a Excel (.xlsx)
                            </button>
                            <a href="{{ route('roster.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                <i class="bi bi-calendar-event"></i> Ver Matriz Mensual
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reporte 4: Padrón de Activos Serializados (QR) -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-2 bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-upc-scan fs-5"></i>
                        </span>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">4. Padrón de Activos Serializados</h5>
                            <small class="text-secondary">Inventario patrimonial, números de serie y custodios</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('reportes.export.activos') }}" method="GET">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Estado Operativo</label>
                                <select name="estado_operativo" class="form-select form-select-sm">
                                    <option value="">-- Todos los Estados --</option>
                                    <option value="OPERATIVO">OPERATIVO</option>
                                    <option value="EN_MANTENIMIENTO">EN MANTENIMIENTO</option>
                                    <option value="CALIBRACION_PENDIENTE">CALIBRACIÓN PENDIENTE</option>
                                    <option value="DADO_DE_BAJA">DADO DE BAJA</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Condición de Préstamo</label>
                                <select name="condicion_prestamo" class="form-select form-select-sm">
                                    <option value="">-- Todas las Condiciones --</option>
                                    <option value="DISPONIBLE">DISPONIBLE EN ALMACÉN</option>
                                    <option value="PRESTADO_CAMPO">PRESTADO EN CAMPO</option>
                                    <option value="ASIGNADO_CUADRILLA">ASIGNADO A CUADRILLA</option>
                                    <option value="RESERVADO">RESERVADO</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Almacén Actual</label>
                                <select name="ubicacion_id" class="form-select form-select-sm">
                                    <option value="">-- Todos los Almacenes --</option>
                                    @foreach($ubicaciones as $ub)
                                        <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Proyecto Asignado</label>
                                <select name="proyecto_id" class="form-select form-select-sm">
                                    <option value="">-- Todos los Proyectos --</option>
                                    @foreach($proyectos as $pry)
                                        <option value="{{ $pry->id }}">{{ $pry->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                            <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-2 px-3 shadow-sm">
                                <i class="bi bi-file-earmark-excel"></i> Exportar Activos a Excel (.xlsx)
                            </button>
                            <a href="{{ route('activos.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                <i class="bi bi-list-check"></i> Ir a Padrón de Activos
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reporte 5: Hoja de Cargo de Cuadrillas (PDF Oficial) -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded-2 bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-people-fill fs-5"></i>
                        </span>
                        <div>
                            <h5 class="card-title mb-0 fw-bold">5. Hoja de Cargo y Asignación de Cuadrilla (Documento Oficial A4)</h5>
                            <small class="text-secondary">Emisión de acta formal de dotación técnica, equipos en custodia y actas de responsabilidad</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-6 col-md-7">
                            <label class="form-label small fw-semibold">Seleccionar Cuadrilla de Trabajo</label>
                            <select id="selectCuadrilla" class="form-select">
                                <option value="">-- Seleccione una Cuadrilla --</option>
                                @foreach($cuadrillas as $cd)
                                    <option value="{{ $cd->id }}">
                                        {{ $cd->codigo_cuadrilla }} - {{ $cd->nombre }} ({{ $cd->proyecto?->nombre ?? 'Sin Proyecto' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-5 d-flex flex-wrap gap-2 align-items-end pt-md-4">
                            <button type="button" onclick="descargarHojaCargo(false)" class="btn btn-danger d-flex align-items-center gap-2 px-3 shadow-sm">
                                <i class="bi bi-file-earmark-pdf-fill"></i> Descargar Hoja de Cargo (PDF)
                            </button>
                            <button type="button" onclick="descargarHojaCargo(true)" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                                <i class="bi bi-eye"></i> Vista Previa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    function descargarPdfInventario(stream) {
        const form = document.getElementById('formInventario');
        const params = new URLSearchParams(new FormData(form));
        if (stream) {
            params.append('stream', '1');
        }
        const url = "{{ route('reportes.pdf.inventario') }}?" + params.toString();
        window.open(url, stream ? '_blank' : '_self');
    }

    function descargarHojaCargo(stream) {
        const select = document.getElementById('selectCuadrilla');
        const cuadrillaId = select.value;
        if (!cuadrillaId) {
            alert('Por favor seleccione una cuadrilla para emitir su Hoja de Cargo.');
            select.focus();
            return;
        }

        let url = "{{ url('reportes/cuadrilla') }}/" + cuadrillaId + "/pdf";
        if (stream) {
            url += "?stream=1";
        }
        window.open(url, stream ? '_blank' : '_self');
    }
</script>
@endpush
@endsection
