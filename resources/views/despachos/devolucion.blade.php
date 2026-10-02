@extends('layouts.admin')

@section('title', 'Liquidación de Retorno - ' . $despacho->numero_guia)
@section('page_title', 'Registrar Devolución de Campo: ' . $despacho->numero_guia)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('despachos.index') }}" class="text-decoration-none fw-semibold text-primary">Despachos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('despachos.show', $despacho) }}" class="text-decoration-none fw-semibold text-primary">{{ $despacho->numero_guia }}</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Devolución</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <form action="{{ route('despachos.devolucion.store', $despacho) }}" method="POST">
        @csrf

        <div class="row g-4 justify-content-center">
            <div class="col-lg-11">
                <!-- Datos del Despacho Origen -->
                <div class="admin-card p-4 mb-4">
                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                        <i class="bi bi-arrow-return-left me-2 text-warning"></i> Liquidación de Equipos & Materiales en Retorno
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="small text-muted d-block">N° Guía / Vale:</label>
                            <span class="font-monospace fw-bold text-primary fs-6">{{ $despacho->numero_guia }}</span>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted d-block">Proyecto:</label>
                            <span class="fw-bold text-heading">{{ $despacho->proyecto->nombre }}</span>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted d-block">Receptor / Custodio:</label>
                            <span class="fw-bold text-heading">{{ $despacho->personal->nombre_completo ?? 'Sin asignar' }}</span>
                        </div>
                        <div class="col-md-3">
                            <label for="ubicacion_destino_id" class="form-label small fw-bold">Almacén de Reingreso <span class="text-danger">*</span></label>
                            <select name="ubicacion_destino_id" id="ubicacion_destino_id" class="form-select form-select-sm @error('ubicacion_destino_id') is-invalid @enderror" required>
                                @foreach($ubicaciones as $ub)
                                    <option value="{{ $ub->id }}" {{ $ub->id == $despacho->ubicacion_origen_id ? 'selected' : '' }}>
                                        {{ $ub->nombre }} ({{ $ub->tipo }})
                                    </option>
                                @endforeach
                            </select>
                            @error('ubicacion_destino_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Lista de Ítems Pendientes de Retorno -->
                <div class="admin-card p-4">
                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                        <i class="bi bi-check2-square me-2 text-primary"></i> Evaluación de Estado Físico & Saldo Reingresado
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 110px;">SKU</th>
                                    <th>Descripción del Bien</th>
                                    <th>Identificación (QR / Serial)</th>
                                    <th style="width: 90px;" class="text-end">Entregado</th>
                                    <th style="width: 120px;" class="text-end">Cant. Retorno</th>
                                    <th style="width: 220px;">Condición Física / Estado</th>
                                    <th>Observaciones de Retorno</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($despacho->detalles as $idx => $det)
                                    <tr class="item-retorno-row">
                                        <input type="hidden" name="items[{{ $idx }}][detalle_id]" value="{{ $det->id }}">
                                        
                                        <td class="font-monospace fw-bold text-primary">
                                            {{ $det->articulo->codigo_sku }}
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-heading">{{ $det->articulo->descripcion }}</div>
                                            <div class="small text-muted">{{ $det->articulo->marca ?? 'S/M' }} {{ $det->articulo->modelo ?? '' }}</div>
                                        </td>
                                        <td>
                                            @if($det->activo)
                                                <span class="badge badge-soft-success font-monospace">
                                                    <i class="bi bi-qr-code"></i> {{ $det->activo->codigo_interno }}
                                                </span>
                                                <div class="small text-muted font-monospace mt-1">SN: {{ $det->activo->numero_serie ?? 'S/N' }}</div>
                                                @if($det->articulo->es_instalable)
                                                    <span class="badge badge-soft-info font-monospace mt-1" title="Equipo/Material seriado instalable en obra">
                                                        <i class="bi bi-hdd-network"></i> Seriado Instalable
                                                    </span>
                                                @endif
                                            @else
                                                <span class="small text-muted italic">Consumible / A granel</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">
                                            {{ number_format($det->cantidad, 0) }} {{ $det->articulo->unidad_medida }}
                                        </td>
                                        <td>
                                            <input type="number" step="1" min="0" max="{{ (int) $det->cantidad }}" name="items[{{ $idx }}][cantidad_devuelta]" class="form-control form-control-sm text-end fw-bold input-cantidad-dev" value="{{ $det->activo ? '1' : (int) $det->cantidad }}" data-original="{{ $det->activo ? '1' : (int) $det->cantidad }}" {{ $det->activo ? 'readonly' : '' }} required>
                                        </td>
                                        <td>
                                            <select name="items[{{ $idx }}][estado_item]" class="form-select form-select-sm fw-semibold select-estado-item" required>
                                                <option value="DEVUELTO_OPERATIVO" selected class="text-success">✔ DEVUELTO OPERATIVO</option>
                                                <option value="DEVUELTO_DANADO" class="text-warning">⚠ DEVUELTO DAÑADO / EN TALLER</option>
                                                <option value="EXTRAVIADO" class="text-danger">✖ EXTRAVIADO / PERDIDO</option>
                                                @if(!$det->activo)
                                                    <option value="CONSUMIDO" class="text-secondary">⬚ CONSUMIDO EN CAMPO</option>
                                                @elseif($det->articulo->es_instalable)
                                                    <option value="CONSUMIDO" class="text-info fw-bold">⬚ INSTALADO EN OBRA (QUEDA EN PROYECTO)</option>
                                                @else
                                                    <option value="CONSUMIDO" class="text-secondary">⬚ INSTALADO / CONSUMIDO EN PROYECTO</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $idx }}][observacion_retorno]" class="form-control form-control-sm" placeholder="Ej: Operativo sin daños, instalado en rack...">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            Todos los bienes de esta orden ya han sido liquidados o devueltos.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-4">
                        <a href="{{ route('despachos.show', $despacho) }}" class="btn btn-outline-secondary px-3">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning text-dark px-4 fw-bold shadow">
                            <i class="bi bi-check-circle me-1"></i> Procesar Devolución & Reingreso a Kardex
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.select-estado-item').forEach(function(select) {
                select.addEventListener('change', function() {
                    const row = this.closest('tr');
                    const inputCant = row.querySelector('.input-cantidad-dev');
                    if (!inputCant) return;
                    
                    if (this.value === 'CONSUMIDO' || this.value === 'EXTRAVIADO') {
                        inputCant.value = '0';
                    } else {
                        inputCant.value = inputCant.getAttribute('data-original') || '1';
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
