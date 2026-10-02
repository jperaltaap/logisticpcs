@extends('layouts.admin')

@section('title', 'Activos Serializados con Código QR')
@section('page_title', 'Control de Activos & Equipos Serializados')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Inventario & Activos</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Activos (QR)</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    @if(auth()->user()->rol !== 'TECNICO')
    <a href="{{ route('reportes.export.activos', request()->query()) }}" class="btn btn-success btn-sm px-3 fw-bold shadow-sm">
        <i class="bi bi-file-earmark-excel me-1"></i> Exportar Excel
    </a>
    @endif
    @if(!in_array(auth()->user()->rol, ['AUDITOR', 'TECNICO'], true))
    <a href="{{ route('activos.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow">
        <i class="bi bi-qr-code-scan me-1"></i> Registrar Activo
    </a>
    @endif
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Resumen KPI de Activos Serializados -->
    @if(isset($stats))
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="admin-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3 fs-4"><i class="bi bi-qr-code"></i></div>
                <div>
                    <div class="text-muted small fw-semibold">Unidades Serializadas</div>
                    <div class="fs-4 fw-bold text-heading">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="admin-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 text-success p-3 fs-4"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="text-muted small fw-semibold">Disponibles en Almacén</div>
                    <div class="fs-4 fw-bold text-success">{{ $stats['disponibles'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="admin-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 text-info p-3 fs-4"><i class="bi bi-truck"></i></div>
                <div>
                    <div class="text-muted small fw-semibold">En Campo / Obra</div>
                    <div class="fs-4 fw-bold text-info">{{ $stats['prestados'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="admin-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3 fs-4"><i class="bi bi-tools"></i></div>
                <div>
                    <div class="text-muted small fw-semibold">En Mantenimiento</div>
                    <div class="fs-4 fw-bold text-warning">{{ $stats['mantenimiento'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filtros de Búsqueda -->
    <div class="admin-card p-3 mb-4">
        <form action="{{ route('activos.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="filtro_act_search" class="form-label small fw-semibold text-muted mb-1">Buscar por Código, Serie o Artículo</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                    <input type="text" id="filtro_act_search" name="search" class="form-control" placeholder="Ej: ACT-00001, SN-..., Fusionadora" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label for="filtro_act_articulo" class="form-label small fw-semibold text-muted mb-1">Artículo Maestro</label>
                <select id="filtro_act_articulo" name="articulo_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todos los artículos</option>
                    @foreach($articulos as $art)
                        <option value="{{ $art->id }}" {{ request('articulo_id') == $art->id ? 'selected' : '' }}>
                            {{ $art->codigo_sku }} - {{ $art->descripcion }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtro_act_ubicacion" class="form-label small fw-semibold text-muted mb-1">Centro de Almacén</label>
                <select id="filtro_act_ubicacion" name="ubicacion_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todos los almacenes</option>
                    @foreach($ubicaciones as $ub)
                        <option value="{{ $ub->id }}" {{ request('ubicacion_id') == $ub->id ? 'selected' : '' }}>
                            {{ $ub->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtro_act_estado_op" class="form-label small fw-semibold text-muted mb-1">Estado Operativo</label>
                <select id="filtro_act_estado_op" name="estado_operativo" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="OPERATIVO" {{ request('estado_operativo') == 'OPERATIVO' ? 'selected' : '' }}>OPERATIVO</option>
                    <option value="EN_MANTENIMIENTO" {{ request('estado_operativo') == 'EN_MANTENIMIENTO' ? 'selected' : '' }}>EN MANTENIMIENTO</option>
                    <option value="DANADO" {{ request('estado_operativo') == 'DANADO' ? 'selected' : '' }}>DAÑADO</option>
                    <option value="DE_BAJA" {{ request('estado_operativo') == 'DE_BAJA' ? 'selected' : '' }}>DE BAJA</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filtro_act_condicion" class="form-label small fw-semibold text-muted mb-1">Condición Préstamo</label>
                <select id="filtro_act_condicion" name="condicion_prestamo" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Todas las condiciones</option>
                    <option value="DISPONIBLE" {{ request('condicion_prestamo') == 'DISPONIBLE' ? 'selected' : '' }}>DISPONIBLE</option>
                    <option value="PRESTADO_CAMPO" {{ request('condicion_prestamo') == 'PRESTADO_CAMPO' ? 'selected' : '' }}>PRESTADO EN CAMPO</option>
                    <option value="INSTALADO_PROYECTO" {{ request('condicion_prestamo') == 'INSTALADO_PROYECTO' ? 'selected' : '' }}>INSTALADO EN PROYECTO</option>
                    <option value="EN_TRANSFERENCIA" {{ request('condicion_prestamo') == 'EN_TRANSFERENCIA' ? 'selected' : '' }}>EN TRANSFERENCIA</option>
                    <option value="EXTRAVIADO" {{ request('condicion_prestamo') == 'EXTRAVIADO' ? 'selected' : '' }}>EXTRAVIADO</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold" aria-label="Filtrar activos" title="Filtrar">
                    <i class="bi bi-funnel" aria-hidden="true"></i>
                </button>
                <a href="{{ route('activos.index') }}" class="btn btn-outline-secondary btn-sm" aria-label="Limpiar filtros de activos" title="Limpiar">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla Principal de Activos -->
    <div class="admin-card overflow-hidden">
        <div class="table-responsive" tabindex="0" role="region" aria-label="Tabla de inventario de activos serializados">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="ps-3" style="width: 140px;">Placa / QR</th>
                        <th scope="col">N° Serie Fábrica</th>
                        <th scope="col">Bien / Modelo</th>
                        <th scope="col">Ubicación Actual</th>
                        <th scope="col">Custodio / Proyecto</th>
                        <th scope="col">Estado Operativo</th>
                        <th scope="col">Préstamo</th>
                        <th scope="col" class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activos as $act)
                        <tr>
                            <td class="ps-3 fw-bold font-monospace text-primary">
                                <a href="{{ route('activos.show', $act) }}" class="text-decoration-none d-flex align-items-center gap-1">
                                    <i class="bi bi-qr-code text-muted"></i> {{ $act->codigo_interno }}
                                </a>
                            </td>
                            <td class="font-monospace small text-heading">
                                {{ $act->numero_serie ?? 'SIN SERIE' }}
                            </td>
                            <td>
                                <div class="fw-semibold text-heading">{{ $act->articulo->descripcion ?? 'N/A' }}</div>
                                <div class="small text-muted">
                                    SKU: <span class="font-monospace text-primary">{{ $act->articulo->codigo_sku ?? '' }}</span>
                                    @if($act->articulo->marca)
                                        · {{ $act->articulo->marca }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1 small text-heading">
                                    <i class="bi bi-geo-alt text-danger"></i>
                                    {{ $act->ubicacion->nombre ?? 'Sin Ubicación' }}
                                </div>
                            </td>
                            <td>
                                @if($act->responsable)
                                    <div class="small fw-semibold text-heading">
                                        <i class="bi bi-person-badge text-primary me-1"></i> {{ $act->responsable->nombre_completo }}
                                    </div>
                                @endif
                                @if($act->proyecto)
                                    <div class="small text-muted">
                                        <i class="bi bi-buildings text-muted me-1"></i> {{ $act->proyecto->nombre }}
                                    </div>
                                @endif
                                @if(!$act->responsable && !$act->proyecto)
                                    <span class="small text-muted italic">En stock almacén</span>
                                @endif
                            </td>
                            <td>
                                @if($act->estado_operativo === 'EN_MANTENIMIENTO')
                                    <a href="{{ route('mantenimientos.index', ['q' => $act->codigo_interno]) }}" class="badge badge-soft-warning text-decoration-none" title="Ver seguimiento en Calibraciones & Taller">
                                        <i class="bi bi-tools me-1"></i> EN MANTENIMIENTO
                                    </a>
                                @else
                                    @php
                                        $opBadge = match($act->estado_operativo) {
                                            'OPERATIVO' => 'badge-soft-success',
                                            'DANADO' => 'badge-soft-danger',
                                            'DE_BAJA' => 'badge-soft-secondary',
                                            default => 'badge-soft-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $opBadge }}">{{ $act->estado_operativo }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $prestBadge = match($act->condicion_prestamo) {
                                        'DISPONIBLE' => 'badge-soft-success',
                                        'PRESTADO_CAMPO' => 'badge-soft-primary',
                                        'INSTALADO_PROYECTO' => 'badge-soft-info',
                                        'EN_TRANSFERENCIA' => 'badge-soft-warning',
                                        'EXTRAVIADO' => 'badge-soft-danger',
                                        default => 'badge-soft-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $prestBadge }}">{{ $act->condicion_prestamo }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('activos.show', $act) }}" class="btn btn-outline-secondary" aria-label="Ver ficha y QR del activo {{ $act->codigo_interno }}" title="Ver Activo & QR">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('activos.etiqueta', $act) }}" target="_blank" class="btn btn-outline-dark" aria-label="Imprimir etiqueta QR del activo {{ $act->codigo_interno }}" title="Imprimir Etiqueta QR">
                                        <i class="bi bi-printer" aria-hidden="true"></i>
                                    </a>
                                    @if(auth()->user()->rol !== 'TECNICO')
                                        @if($act->estado_operativo === 'EN_MANTENIMIENTO')
                                            <a href="{{ route('mantenimientos.index', ['q' => $act->codigo_interno]) }}" class="btn btn-outline-warning" aria-label="Ver seguimiento en taller de {{ $act->codigo_interno }}" title="Seguimiento en Taller / Dar de Alta">
                                                <i class="bi bi-tools" aria-hidden="true"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('mantenimientos.create', ['activo_id' => $act->id]) }}" class="btn btn-outline-warning" aria-label="Enviar a taller/calibración {{ $act->codigo_interno }}" title="Enviar a Taller / Calibración">
                                                <i class="bi bi-tools" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                    @endif
                                    @if(!in_array(auth()->user()->rol, ['AUDITOR', 'TECNICO'], true))
                                    <a href="{{ route('activos.edit', $act) }}" class="btn btn-outline-primary" aria-label="Editar activo {{ $act->codigo_interno }}" title="Editar">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </a>
                                    <form action="{{ route('activos.destroy', $act) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Está seguro de eliminar el activo {{ $act->codigo_interno }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" aria-label="Eliminar activo {{ $act->codigo_interno }}" title="Eliminar">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-qr-code fs-1 d-block mb-2 opacity-50"></i>
                                    No se encontraron activos serializados con los filtros aplicados.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activos->hasPages())
            <div class="p-3 border-top">
                {{ $activos->links() }}
            </div>
        @endif
    </div>

@endsection
