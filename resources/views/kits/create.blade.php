@extends('layouts.admin')

@section('title', 'Nuevo Kit Técnico')
@section('page_title', 'Configurar Nuevo Kit Estandarizado por Almacén')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kits.index') }}" class="text-decoration-none fw-semibold text-primary">Kits</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Nuevo Kit</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    @php
        $defaultUbicacionId = old('ubicacion_id', $ubicaciones->first()?->id);
        $initialItems = $defaultUbicacionId ? ($articulosPorAlmacen[$defaultUbicacionId] ?? []) : [];
    @endphp

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="admin-card p-4">
                <form action="{{ route('kits.store') }}" method="POST" id="kitForm">
                    @csrf

                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                        <i class="bi bi-collection me-2 text-primary"></i> Datos Generales y Almacén del Kit
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="codigo_kit" class="form-label small fw-bold">Código del Kit <span class="text-danger">*</span></label>
                            <input type="text" name="codigo_kit" id="codigo_kit" class="form-control font-monospace fw-bold @error('codigo_kit') is-invalid @enderror" value="{{ old('codigo_kit', $codigoSugerido) }}" required>
                            @error('codigo_kit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="nombre_kit" class="form-label small fw-bold">Nombre del Kit <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_kit" id="nombre_kit" class="form-control @error('nombre_kit') is-invalid @enderror" value="{{ old('nombre_kit') }}" placeholder="Ej: Kit Estándar de Empalme de Fibra Óptica" required>
                            @error('nombre_kit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="ubicacion_id" class="form-label small fw-bold">Almacén del Kit <span class="text-danger">*</span></label>
                            <select name="ubicacion_id" id="ubicacion_id" class="form-select @error('ubicacion_id') is-invalid @enderror" required>
                                <option value="">Seleccione un almacén...</option>
                                @foreach($ubicaciones as $ubi)
                                    <option value="{{ $ubi->id }}" {{ (string) $defaultUbicacionId === (string) $ubi->id ? 'selected' : '' }}>
                                        [{{ $ubi->codigo }}] {{ $ubi->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ubicacion_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="tipo_kit" class="form-label small fw-bold">Tipo de Kit <span class="text-danger">*</span></label>
                            <select name="tipo_kit" id="tipo_kit" class="form-select @error('tipo_kit') is-invalid @enderror" required>
                                <option value="KIT_HERRAMIENTAS" {{ old('tipo_kit') == 'KIT_HERRAMIENTAS' ? 'selected' : '' }}>HERRAMIENTAS</option>
                                <option value="KIT_EPP" {{ old('tipo_kit') == 'KIT_EPP' ? 'selected' : '' }}>EPP (SEGURIDAD)</option>
                                <option value="KIT_EMPALME" {{ old('tipo_kit') == 'KIT_EMPALME' ? 'selected' : '' }}>EMPALME FIBRA</option>
                                <option value="KIT_MATERIALES" {{ old('tipo_kit') == 'KIT_MATERIALES' ? 'selected' : '' }}>MATERIALES</option>
                            </select>
                            @error('tipo_kit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="estado" class="form-label small fw-bold">Estado <span class="text-danger">*</span></label>
                            <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror" required>
                                <option value="ACTIVO" {{ old('estado', 'ACTIVO') == 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
                                <option value="INACTIVO" {{ old('estado') == 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="descripcion" class="form-label small fw-bold">Descripción del Propósito del Kit</label>
                            <input type="text" name="descripcion" id="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion') }}" placeholder="Indicar para qué tarea operativa está homologado este paquete...">
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>
                            <strong>Regla de Integridad por Almacén:</strong> Los kits solo pueden conformarse con ítems que se encuentren físicamente disponibles en el <strong>mismo almacén seleccionado</strong>.
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h5 class="fw-bold mb-0 text-heading">
                            <i class="bi bi-list-check me-2 text-primary"></i> Componentes Disponibles en el Almacén Seleccionado
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="addCompBtn">
                            <i class="bi bi-plus-lg me-1"></i> Agregar Artículo al Kit
                        </button>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm align-middle" id="componentesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Artículo / Bien Disponible en el Almacén</th>
                                    <th style="width: 150px;">Cantidad Estándar</th>
                                    <th class="text-center" style="width: 60px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="componentesList">
                                <tr class="comp-row">
                                    <td>
                                        <select name="componentes[0][articulo_id]" class="form-select form-select-sm comp-articulo-select" required>
                                            <option value="">Seleccione un artículo del almacén...</option>
                                            @foreach($initialItems as $item)
                                                <option value="{{ $item['id'] }}">
                                                    [{{ $item['codigo_sku'] }}] {{ $item['descripcion'] }} ({{ $item['unidad_medida'] }}) — Stock en almacén: {{ $item['stock_disponible'] }} — {{ $item['control_serie'] ? 'Serializado' : 'Consumible' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="1" min="1" name="componentes[0][cantidad]" class="form-control form-control-sm text-end" value="1" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn" title="Eliminar fila">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('kits.index') }}" class="btn btn-outline-secondary px-3">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow">
                            <i class="bi bi-save me-1"></i> Guardar Kit
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Template para nuevas filas dinámicas en Javascript -->
    <template id="rowTemplate">
        <tr class="comp-row">
            <td>
                <select name="componentes[INDEX][articulo_id]" class="form-select form-select-sm comp-articulo-select" required>
                    <option value="">Seleccione un artículo del almacén...</option>
                </select>
            </td>
            <td>
                <input type="number" step="1" min="1" name="componentes[INDEX][cantidad]" class="form-control form-control-sm text-end" value="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn" title="Eliminar fila">
                    <i class="bi bi-x-lg"></i>
                </button>
            </td>
        </tr>
    </template>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const articulosPorAlmacen = @json($articulosPorAlmacen);
        let rowIdx = 1;
        const ubicacionSelect = document.getElementById('ubicacion_id');
        const addBtn = document.getElementById('addCompBtn');
        const list = document.getElementById('componentesList');
        const template = document.getElementById('rowTemplate');

        function buildOptionsHtml(ubicacionId, selectedValue = '') {
            const items = articulosPorAlmacen[ubicacionId] || [];
            if (!ubicacionId) {
                return '<option value="">Seleccione primero un almacén...</option>';
            }
            if (items.length === 0) {
                return '<option value="">Sin artículos con stock disponible en este almacén</option>';
            }
            let html = '<option value="">Seleccione un artículo del almacén...</option>';
            items.forEach(item => {
                const isSelected = String(item.id) === String(selectedValue) ? 'selected' : '';
                const tipoStr = item.control_serie ? 'Serializado' : 'Consumible';
                html += `<option value="${item.id}" ${isSelected}>[${item.codigo_sku}] ${item.descripcion} (${item.unidad_medida}) — Stock en almacén: ${item.stock_disponible} — ${tipoStr}</option>`;
            });
            return html;
        }

        ubicacionSelect.addEventListener('change', () => {
            const uId = ubicacionSelect.value;
            list.querySelectorAll('.comp-articulo-select').forEach(sel => {
                sel.innerHTML = buildOptionsHtml(uId, '');
            });
        });

        addBtn.addEventListener('click', () => {
            const html = template.innerHTML.replaceAll('INDEX', rowIdx);
            list.insertAdjacentHTML('beforeend', html);
            const newRow = list.lastElementChild;
            const select = newRow.querySelector('.comp-articulo-select');
            if (select) {
                select.innerHTML = buildOptionsHtml(ubicacionSelect.value, '');
            }
            rowIdx++;
        });

        list.addEventListener('click', (e) => {
            if (e.target.closest('.remove-row-btn')) {
                const rows = list.querySelectorAll('.comp-row');
                if (rows.length > 1) {
                    e.target.closest('.comp-row').remove();
                } else {
                    alert('El kit debe tener al menos un componente configurado.');
                }
            }
        });
    });
</script>
@endpush

