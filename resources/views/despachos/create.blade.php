@extends('layouts.admin')

@section('title', 'Nueva Orden de Despacho & Préstamo')
@section('page_title', 'Emisión de Vale de Despacho con Firma Digital')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('despachos.index') }}" class="text-decoration-none fw-semibold text-primary">Despachos</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Nuevo Despacho</li>
        </ol>
    </nav>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <form action="{{ route('despachos.store') }}" method="POST" id="despachoForm">
        @csrf

        <div class="row g-4">
            <!-- Columna Izquierda: Cabecera de la Guía y Lista de Ítems -->
            <div class="col-lg-8">
                <!-- Tarjeta de Datos de la Operación -->
                <div class="admin-card p-4 mb-4">
                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                        <i class="bi bi-file-earmark-text me-2 text-primary"></i> Datos Generales de la Guía de Despacho
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="numero_guia" class="form-label small fw-bold">N° Guía / Vale <span class="text-danger">*</span></label>
                            <input type="text" name="numero_guia" id="numero_guia" class="form-control font-monospace fw-bold @error('numero_guia') is-invalid @enderror" value="{{ old('numero_guia', $numeroGuiaSugerido) }}" required>
                            @error('numero_guia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="tipo_movimiento" class="form-label small fw-bold">Tipo de Operación <span class="text-danger">*</span></label>
                            <select name="tipo_movimiento" id="tipo_movimiento" class="form-select @error('tipo_movimiento') is-invalid @enderror" required>
                                <option value="SALIDA_PRESTAMO_CAMPO" {{ old('tipo_movimiento') == 'SALIDA_PRESTAMO_CAMPO' ? 'selected' : '' }}>SALIDA POR PRÉSTAMO A CAMPO</option>
                                <option value="CONSUMO_DIRECTO" {{ old('tipo_movimiento') == 'CONSUMO_DIRECTO' ? 'selected' : '' }}>SALIDA POR CONSUMO DIRECTO (NO RETORNA)</option>
                                <option value="TRANSFERENCIA_UBICACION" {{ old('tipo_movimiento') == 'TRANSFERENCIA_UBICACION' ? 'selected' : '' }}>TRANSFERENCIA ENTRE ALMACENES</option>
                            </select>
                            @error('tipo_movimiento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="ubicacion_origen_id" class="form-label small fw-bold">Almacén de Origen <span class="text-danger">*</span></label>
                            <select name="ubicacion_origen_id" id="ubicacion_origen_id" class="form-select @error('ubicacion_origen_id') is-invalid @enderror" required>
                                @foreach($ubicaciones as $ub)
                                    <option value="{{ $ub->id }}" {{ (string) old('ubicacion_origen_id', request('ubicacion_origen_id')) === (string) $ub->id ? 'selected' : '' }}>
                                        {{ $ub->nombre }} ({{ $ub->tipo }})
                                    </option>
                                @endforeach
                            </select>
                            @error('ubicacion_origen_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="proyecto_id" class="form-label small fw-bold">Proyecto de Imputación <span class="text-danger">*</span></label>
                            <select name="proyecto_id" id="proyecto_id" class="form-select @error('proyecto_id') is-invalid @enderror" required>
                                <option value="">Seleccione el proyecto...</option>
                                @foreach($proyectos as $pry)
                                    <option value="{{ $pry->id }}" {{ old('proyecto_id', session('proyecto_activo_id')) == $pry->id ? 'selected' : '' }}>
                                        {{ $pry->codigo }} - {{ $pry->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('proyecto_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="personal_id" class="form-label small fw-bold">Personal que Retira / Traslada <span class="text-danger">*</span></label>
                            <select name="personal_id" id="personal_id" class="form-select @error('personal_id') is-invalid @enderror" required>
                                <option value="">Seleccione al personal que retira o traslada...</option>
                                @foreach($personal as $p)
                                    <option value="{{ $p->id }}" {{ old('personal_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nombre_completo }} ({{ $p->cargo }})
                                    </option>
                                @endforeach
                            </select>
                            <div id="rosterWarningAlert" class="mt-2 d-none">
                                <div class="alert alert-warning py-1 px-2 mb-0 small d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-triangle-fill fs-6 text-warning"></i>
                                    <span id="rosterWarningText"></span>
                                </div>
                            </div>
                            @error('personal_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="fecha_compromiso_retorno" class="form-label small fw-bold">Fecha Proyectada de Devolución</label>
                            <input type="date" name="fecha_compromiso_retorno" id="fecha_compromiso_retorno" class="form-control @error('fecha_compromiso_retorno') is-invalid @enderror" value="{{ old('fecha_compromiso_retorno', date('Y-m-d', strtotime('+7 days'))) }}">
                            @error('fecha_compromiso_retorno')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="observaciones" class="form-label small fw-bold">Observaciones / Destino de Trabajo</label>
                            <input type="text" name="observaciones" id="observaciones" class="form-control @error('observaciones') is-invalid @enderror" value="{{ old('observaciones') }}" placeholder="Ej: Mantenimiento enlace troncal Km 42">
                            @error('observaciones')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Ítems a Despachar -->
                <div class="admin-card p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                        <h5 class="fw-bold mb-0 text-heading">
                            <i class="bi bi-boxes me-2 text-primary"></i> Detalle de Bienes a Entregar
                        </h5>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success fw-bold" id="addActivoBtn">
                                <i class="bi bi-qr-code-scan me-1"></i> + Activo Serializado (QR)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="addMaterialBtn">
                                <i class="bi bi-box-seam me-1"></i> + Material / Consumible
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold" id="addKitBtn">
                                <i class="bi bi-collection me-1"></i> + Cargar Kit
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Artículo / Activo Físico</th>
                                    <th style="width: 130px;" class="text-end">Cantidad</th>
                                    <th class="text-center" style="width: 140px;">Acción / Kit</th>
                                </tr>
                            </thead>
                            <tbody id="itemsList">
                                <!-- Se llena dinámicamente mediante Javascript -->
                            </tbody>
                        </table>
                    </div>

                    <div id="noItemsNotice" class="text-center py-4 text-muted border border-dashed rounded">
                        <i class="bi bi-basket3 fs-2 d-block mb-1 opacity-50"></i>
                        Aún no ha agregado ningún ítem al despacho. Use los botones superiores para agregar activos con QR, consumibles o kits.
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Panel de Firma Digital Táctil y Confirmación -->
            <div class="col-lg-4">
                <div class="admin-card p-4 sticky-top" style="top: 20px;">
                    <h5 class="fw-bold mb-3 pb-2 border-bottom text-heading">
                        <i class="bi bi-pen-fill me-2 text-primary"></i> Firma Digital del Receptor
                    </h5>

                    <p class="small text-muted mb-2">
                        El técnico o responsable debe firmar a continuación en pantalla táctil o con el ratón como constancia de recepción conforme.
                    </p>

                    <!-- Lienzo HTML5 Canvas para firma -->
                    <div class="border rounded bg-white shadow-sm p-1 mb-2 text-center position-relative" style="touch-action: none;">
                        <canvas id="signatureCanvas" width="340" height="180" style="width: 100%; height: 180px; background: #ffffff; cursor: crosshair;"></canvas>
                        <div id="signPlaceholder" class="position-absolute top-50 start-50 translate-middle text-muted small opacity-50 pe-none">
                            <i class="bi bi-pen me-1"></i> Firme aquí
                        </div>
                    </div>

                    <input type="hidden" name="firma_digital_base64" id="firma_digital_base64">

                    <div class="d-flex justify-content-between mb-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSignBtn">
                            <i class="bi bi-eraser me-1"></i> Borrar Firma
                        </button>
                        <span class="badge bg-light text-muted border align-self-center small">Firma Manuscrita Digital</span>
                    </div>

                    <div class="alert alert-light border small text-muted mb-4">
                        <i class="bi bi-shield-check text-success me-1"></i>
                        Esta orden generará automáticamente movimientos en el <strong>Kardex</strong> de almacén y cambiará el estado de los activos a <strong>PRESTADO EN CAMPO</strong>.
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-bold py-2 shadow" id="submitDespachoBtn">
                            <i class="bi bi-check2-circle fs-6 me-1"></i> Emitir Despacho & Generar Vale
                        </button>
                        <a href="{{ route('despachos.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modales o Plantillas para añadir elementos dinámicos -->
    <div class="modal fade" id="kitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-collection me-2 text-primary"></i> Seleccionar Kit del Almacén</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="bi bi-building-check me-1"></i>
                        Mostrando kits disponibles en el <strong>Almacén de Origen</strong> seleccionado. Si solo necesita retirar o trasladar un ítem de manera independiente, puede <strong>desvincularlo del kit</strong> una vez cargado.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Seleccione el Kit a Despachar:</label>
                        <select id="modalKitSelect" class="form-select">
                            <option value="">Seleccione un kit...</option>
                            @foreach($kits as $k)
                                <option value="{{ $k->id }}"
                                        data-ubicacion-id="{{ $k->ubicacion_id }}"
                                        data-codigo="{{ $k->codigo_kit }}"
                                        data-nombre="{{ $k->nombre_kit }}"
                                        data-componentes="{{ json_encode($k->componentes) }}">
                                    [{{ $k->codigo_kit }}] {{ $k->nombre_kit }} — {{ $k->ubicacion->nombre ?? 'Almacén General' }} ({{ $k->componentes->count() }} componentes)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="confirmAddKitBtn">Cargar Componentes</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let itemIndex = 0;
        const itemsList = document.getElementById('itemsList');
        const noItemsNotice = document.getElementById('noItemsNotice');
        const ubicacionOrigenSelect = document.getElementById('ubicacion_origen_id');

        // Artículos y Activos en formato JSON para el selector en frontend
        const todosArticulos = @json($articulos->values());
        const articulosConsumibles = @json($articulos->where('control_serie', false)->values());
        const articulosFungibles = articulosConsumibles; // alias de compatibilidad
        const activosDisponibles = @json($activosDisponibles);
        const preselectedArticuloId = @json(request('articulo_id'));

        function updateEmptyState() {
            if (itemsList.children.length === 0) {
                noItemsNotice.style.display = 'block';
            } else {
                noItemsNotice.style.display = 'none';
            }
        }

        function filtrarKitsPorAlmacen() {
            const uId = ubicacionOrigenSelect ? ubicacionOrigenSelect.value : '';
            const modalKitSelect = document.getElementById('modalKitSelect');
            if (!modalKitSelect) return;

            Array.from(modalKitSelect.options).forEach(opt => {
                if (!opt.value) return;
                const kitUbi = opt.dataset.ubicacionId || '';
                const coincide = !uId || !kitUbi || String(kitUbi) === String(uId);
                opt.hidden = !coincide;
                opt.disabled = !coincide;
            });
            modalKitSelect.value = '';
        }

        if (ubicacionOrigenSelect) {
            ubicacionOrigenSelect.addEventListener('change', filtrarKitsPorAlmacen);
            filtrarKitsPorAlmacen();
        }

        // 1. Agregar Activo Serializado (con código QR y N° Serie)
        document.getElementById('addActivoBtn').addEventListener('click', () => {
            if (activosDisponibles.length === 0) {
                alert('No hay activos serializados disponibles en condición de préstamo.');
                return;
            }

            let optionsHtml = '<option value="">Seleccione el activo con código QR...</option>';
            activosDisponibles.forEach(act => {
                optionsHtml += `<option value="${act.id}" data-articulo-id="${act.articulo_id}" data-descripcion="${act.articulo.descripcion}" data-codigo="${act.codigo_interno}" data-serie="${act.numero_serie || 'S/N'}">[${act.codigo_interno}] ${act.articulo.descripcion} (Serie: ${act.numero_serie || 'S/N'})</option>`;
            });

            const row = document.createElement('tr');
            row.className = 'item-row';
            row.innerHTML = `
                <td><span class="badge badge-soft-success"><i class="bi bi-qr-code"></i> Activo (QR)</span></td>
                <td>
                    <select class="form-select form-select-sm activo-select" required>
                        ${optionsHtml}
                    </select>
                    <input type="hidden" name="detalles[${itemIndex}][articulo_id]" class="articulo-id-input">
                    <input type="hidden" name="detalles[${itemIndex}][activo_id]" class="activo-id-input">
                </td>
                <td class="text-end">
                    <input type="number" step="1" min="1" name="detalles[${itemIndex}][cantidad]" class="form-control form-control-sm text-end" value="1" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn"><i class="bi bi-trash"></i></button>
                </td>
            `;

            itemsList.appendChild(row);
            itemIndex++;
            updateEmptyState();
        });

        // 2. Agregar Material / Consumible
        document.getElementById('addMaterialBtn').addEventListener('click', () => {
            let optionsHtml = '<option value="">Seleccione el material/consumible...</option>';
            articulosConsumibles.forEach(art => {
                optionsHtml += `<option value="${art.id}" data-unidad="${art.unidad_medida}">[${art.codigo_sku}] ${art.descripcion} (${art.unidad_medida})</option>`;
            });

            const row = document.createElement('tr');
            row.className = 'item-row';
            row.innerHTML = `
                <td><span class="badge badge-soft-primary"><i class="bi bi-box"></i> Material</span></td>
                <td>
                    <select name="detalles[${itemIndex}][articulo_id]" class="form-select form-select-sm" required>
                        ${optionsHtml}
                    </select>
                    <input type="hidden" name="detalles[${itemIndex}][activo_id]" value="">
                </td>
                <td class="text-end">
                    <input type="number" step="1" min="1" name="detalles[${itemIndex}][cantidad]" class="form-control form-control-sm text-end" value="1" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn"><i class="bi bi-trash"></i></button>
                </td>
            `;

            itemsList.appendChild(row);
            itemIndex++;
            updateEmptyState();
        });

        // Si viene redirigido desde "Desvincular y Dar Salida" de un Kit, precargar el elemento independiente
        if (preselectedArticuloId) {
            const artPre = todosArticulos.find(a => String(a.id) === String(preselectedArticuloId));
            if (artPre) {
                if (artPre.control_serie) {
                    const actPre = activosDisponibles.find(ac => String(ac.articulo_id) === String(artPre.id));
                    if (actPre) {
                        document.getElementById('addActivoBtn').click();
                        const lastSelect = itemsList.lastElementChild?.querySelector('.activo-select');
                        if (lastSelect) {
                            lastSelect.value = actPre.id;
                            lastSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                } else {
                    document.getElementById('addMaterialBtn').click();
                    const lastSelect = itemsList.lastElementChild?.querySelector('select');
                    if (lastSelect) {
                        lastSelect.value = artPre.id;
                    }
                }
            }
        }

        // 3. Cargar Kit
        const kitModal = new bootstrap.Modal(document.getElementById('kitModal'));
        document.getElementById('addKitBtn').addEventListener('click', () => {
            filtrarKitsPorAlmacen();
            kitModal.show();
        });

        document.getElementById('confirmAddKitBtn').addEventListener('click', () => {
            const select = document.getElementById('modalKitSelect');
            const selectedOpt = select.options[select.selectedIndex];
            if (!selectedOpt || !selectedOpt.value) {
                alert('Por favor seleccione un kit.');
                return;
            }

            const kitId = selectedOpt.value;
            const kitCodigo = selectedOpt.dataset.codigo || 'KIT';
            const componentes = JSON.parse(selectedOpt.dataset.componentes);

            componentes.forEach(comp => {
                const art = comp.articulo;
                const row = document.createElement('tr');
                row.className = 'item-row';
                row.innerHTML = `
                    <td class="tipo-cell"><span class="badge badge-soft-warning"><i class="bi bi-collection"></i> ${kitCodigo}</span></td>
                    <td>
                        <input type="hidden" name="detalles[${itemIndex}][articulo_id]" value="${comp.articulo_id}">
                        <input type="hidden" name="detalles[${itemIndex}][kit_id]" class="kit-id-input" value="${kitId}">
                        <div class="fw-semibold small">${art.descripcion}</div>
                        <div class="small text-muted font-monospace">${art.codigo_sku} (${art.unidad_medida})</div>
                    </td>
                    <td class="text-end">
                        <input type="number" step="1" min="1" name="detalles[${itemIndex}][cantidad]" class="form-control form-control-sm text-end" value="${Math.max(1, Math.round(parseFloat(comp.cantidad) || 1))}" required>
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <button type="button"
                                    class="btn btn-outline-warning btn-sm unlink-kit-item-btn"
                                    data-kit-id="${kitId}"
                                    data-kit-codigo="${kitCodigo}"
                                    data-articulo-id="${comp.articulo_id}"
                                    data-articulo-nombre="${art.descripcion}"
                                    title="Desvincular del kit para darle salida independiente sin afectar al kit">
                                <i class="bi bi-link-45deg"></i> Desvincular
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn" title="Quitar de este despacho">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                itemsList.appendChild(row);
                itemIndex++;
            });

            kitModal.hide();
            updateEmptyState();
        });

        // Event delegation para selección de activos individuales, desvinculación de kit y eliminación de filas
        itemsList.addEventListener('change', (e) => {
            if (e.target.classList.contains('activo-select')) {
                const opt = e.target.options[e.target.selectedIndex];
                const tr = e.target.closest('tr');
                if (opt && opt.value) {
                    tr.querySelector('.activo-id-input').value = opt.value;
                    tr.querySelector('.articulo-id-input').value = opt.dataset.articuloId;
                } else {
                    tr.querySelector('.activo-id-input').value = '';
                    tr.querySelector('.articulo-id-input').value = '';
                }
            }
        });

        itemsList.addEventListener('click', (e) => {
            const unlinkBtn = e.target.closest('.unlink-kit-item-btn');
            if (unlinkBtn) {
                const kitId = unlinkBtn.dataset.kitId;
                const kitCodigo = unlinkBtn.dataset.kitCodigo;
                const articuloId = unlinkBtn.dataset.articuloId;
                const artNombre = unlinkBtn.dataset.articuloNombre;
                const tr = unlinkBtn.closest('tr');

                if (!confirm(`¿Desea desvincular «${artNombre}» del kit ${kitCodigo} para registrarle un movimiento independiente sin perjudicar la composición del kit?`)) {
                    return;
                }

                fetch(`/kits/${kitId}/componentes/${articuloId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(() => {
                    const kitInput = tr.querySelector('.kit-id-input');
                    if (kitInput) kitInput.value = '';
                    const tipoCell = tr.querySelector('.tipo-cell');
                    if (tipoCell) {
                        tipoCell.innerHTML = '<span class="badge badge-soft-info"><i class="bi bi-box-arrow-up-right"></i> Independiente</span>';
                    }
                    unlinkBtn.remove();
                })
                .catch(() => {
                    const kitInput = tr.querySelector('.kit-id-input');
                    if (kitInput) kitInput.value = '';
                    unlinkBtn.remove();
                });
                return;
            }

            if (e.target.closest('.remove-item-btn')) {
                e.target.closest('tr').remove();
                updateEmptyState();
            }
        });

        // -------------------------------------------------------------
        // Lienzo HTML5 Canvas para Firma Digital Manuscrita
        // -------------------------------------------------------------
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        const signPlaceholder = document.getElementById('signPlaceholder');
        let isDrawing = false;
        let hasSigned = false;

        ctx.strokeStyle = '#0284c7';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            hasSigned = true;
            signPlaceholder.style.display = 'none';
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            e.preventDefault();
        }

        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
                ctx.closePath();
                document.getElementById('firma_digital_base64').value = canvas.toDataURL('image/png');
            }
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);

        document.getElementById('clearSignBtn').addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('firma_digital_base64').value = '';
            hasSigned = false;
            signPlaceholder.style.display = 'block';
        });

        // Verificación preventiva de Roster 14x7 en tiempo real
        const personalSelect = document.getElementById('personal_id');
        const fechaDespachoInput = document.getElementById('fecha_despacho');
        const rosterAlert = document.getElementById('rosterWarningAlert');
        const rosterText = document.getElementById('rosterWarningText');

        function checkRosterLaboral() {
            const perId = personalSelect.value;
            const fecha = fechaDespachoInput.value ? fechaDespachoInput.value.substring(0, 10) : '';

            if (!perId) {
                rosterAlert.classList.add('d-none');
                return;
            }

            fetch(`{{ route('roster.check-condicion') }}?personal_id=${perId}&fecha=${fecha}`)
                .then(res => res.json())
                .then(data => {
                    if (data.registrado && !data.es_operativo) {
                        rosterText.textContent = data.mensaje;
                        rosterAlert.classList.remove('d-none');
                    } else {
                        rosterAlert.classList.add('d-none');
                    }
                })
                .catch(() => {
                    rosterAlert.classList.add('d-none');
                });
        }

        personalSelect.addEventListener('change', checkRosterLaboral);
        if (fechaDespachoInput) {
            fechaDespachoInput.addEventListener('change', checkRosterLaboral);
        }

        // Validación antes de enviar formulario
        document.getElementById('despachoForm').addEventListener('submit', (e) => {
            if (itemsList.children.length === 0) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Despacho Vacío',
                        text: 'Debe agregar al menos un artículo o activo al despacho antes de procesar.',
                        confirmButtonColor: 'var(--admin-primary, #0284c7)',
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    alert('Debe agregar al menos un artículo o activo al despacho.');
                }
                return false;
            }

            if (!hasSigned && !form.dataset.skipSignConfirmed) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¿Emitir sin firma digital?',
                        text: 'No se ha registrado una firma digital manuscrita en el recuadro. ¿Desea continuar y emitir la orden como PENDIENTE DE FIRMA?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: 'var(--admin-primary, #0284c7)',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Sí, continuar',
                        cancelButtonText: 'Regresar y firmar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.dataset.skipSignConfirmed = 'true';
                            form.submit();
                        }
                    });
                    return false;
                } else {
                    if (!confirm('No se ha registrado una firma digital manuscrita en el recuadro. ¿Desea continuar y emitir la orden como PENDIENTE DE FIRMA?')) {
                        return false;
                    }
                }
            } else if (hasSigned) {
                document.getElementById('firma_digital_base64').value = canvas.toDataURL('image/png');
            }
        });
    });
</script>
@endpush
