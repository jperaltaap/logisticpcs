@extends('layouts.admin')

@section('title', 'Manual de Usuario & Documentación del Sistema')
@section('page_title', 'Manual de Usuario & Documentación Operativa')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Documentación & Manual</li>
        </ol>
    </nav>
@endsection

@section('content')

    <!-- Encabezado Hero de Documentación -->
    <div class="admin-card p-4 mb-4" style="background: linear-gradient(135deg, rgba(var(--admin-primary-rgb), 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary text-white shadow-sm d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                    <i class="bi bi-book-half fs-2"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-heading mb-1">{{ $empresa->sistema_nombre ?: 'LogisticPCS' }} — Manual Oficial de Usuario</h4>
                    <p class="text-muted small mb-0">
                        Estructura completa del menú, procesos operativos de almacén, flujo integral de calibraciones y taller, centro de reportes y matriz de permisos.
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill font-monospace small">
                    <i class="bi bi-shield-check me-1"></i> Versión Enterprise · PHP 8.4 / Laravel 11
                </span>
            </div>
        </div>
    </div>

    <!-- Navegación por Pestañas Principales -->
    <div class="admin-card p-2 mb-4">
        <ul class="nav nav-pills nav-fill gap-2" id="docTabs" role="tablist" aria-label="Secciones del Manual de Usuario">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold py-2 d-flex align-items-center justify-content-center gap-2" id="menu-tab" data-bs-toggle="tab" data-bs-target="#tab-menu" type="button" role="tab" aria-controls="tab-menu" aria-selected="true">
                    <i class="bi bi-compass fs-5" aria-hidden="true"></i>
                    <span>1. Menú & Módulos</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold py-2 d-flex align-items-center justify-content-center gap-2" id="procesos-tab" data-bs-toggle="tab" data-bs-target="#tab-procesos" type="button" role="tab" aria-controls="tab-procesos" aria-selected="false">
                    <i class="bi bi-arrow-repeat fs-5" aria-hidden="true"></i>
                    <span>2. Procesos & Flujos Clave</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold py-2 d-flex align-items-center justify-content-center gap-2" id="taller-tab" data-bs-toggle="tab" data-bs-target="#tab-taller" type="button" role="tab" aria-controls="tab-taller" aria-selected="false">
                    <i class="bi bi-tools fs-5" aria-hidden="true"></i>
                    <span>3. Taller & Calibraciones (Flujo)</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold py-2 d-flex align-items-center justify-content-center gap-2" id="reportes-tab" data-bs-toggle="tab" data-bs-target="#tab-reportes" type="button" role="tab" aria-controls="tab-reportes" aria-selected="false">
                    <i class="bi bi-file-earmark-spreadsheet fs-5" aria-hidden="true"></i>
                    <span>4. Reportes & Exportaciones</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold py-2 d-flex align-items-center justify-content-center gap-2" id="guia-tab" data-bs-toggle="tab" data-bs-target="#tab-guia" type="button" role="tab" aria-controls="tab-guia" aria-selected="false">
                    <i class="bi bi-mortarboard fs-5" aria-hidden="true"></i>
                    <span>5. Casos Prácticos & Usuarios / Roles</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold py-2 d-flex align-items-center justify-content-center gap-2" id="stack-tab" data-bs-toggle="tab" data-bs-target="#tab-stack" type="button" role="tab" aria-controls="tab-stack" aria-selected="false">
                    <i class="bi bi-cpu fs-5" aria-hidden="true"></i>
                    <span>6. Arquitectura Técnica</span>
                </button>
            </li>
        </ul>
    </div>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="docTabsContent">

        <!-- ======================================================== -->
        <!-- TAB 1: ESTRUCTURA DEL MENÚ & MÓDULOS DEL SISTEMA         -->
        <!-- ======================================================== -->
        <div class="tab-pane fade show active" id="tab-menu" role="tabpanel">

            <div class="admin-card p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <i class="bi bi-layout-sidebar-inset fs-4 text-primary"></i>
                    <div>
                        <h5 class="fw-bold mb-0 text-heading">Estructura del Menú de Navegación Lateral (Sidebar)</h5>
                        <small class="text-muted">El sistema organiza sus funcionalidades en 8 secciones lógicas de acceso rápido y orden operativo natural.</small>
                    </div>
                </div>

                <div class="row g-4">

                    <!-- 1. Principal -->
                    <div class="col-lg-6">
                        <div class="border rounded-3 p-3 h-100 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary p-2 rounded-3"><i class="bi bi-speedometer2 fs-6"></i></span>
                                <h6 class="fw-bold text-heading mb-0">1. Sección Principal</h6>
                            </div>
                            <ul class="list-unstyled mb-0 small text-muted d-flex flex-column gap-2 mt-3">
                                <li>
                                    <strong class="text-dark"><i class="bi bi-speedometer2 text-primary me-1"></i> Dashboard General:</strong>
                                    Panel ejecutivo centralizado con tarjetas KPI de stock valorizado, total de activos serializados, préstamos activos en campo, órdenes de taller en curso y alertas activas. Incluye gráficas dinámicas de consumos y accesos rápidos a operaciones frecuentes.
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- 2. Control de Personal -->
                    <div class="col-lg-6">
                        <div class="border rounded-3 p-3 h-100 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-info p-2 rounded-3 text-white"><i class="bi bi-people-fill fs-6"></i></span>
                                <h6 class="fw-bold text-heading mb-0">2. Control de Personal (Frentes & Cuadrillas)</h6>
                            </div>
                            <ul class="list-unstyled mb-0 small text-muted d-flex flex-column gap-2 mt-3">
                                <li>
                                    <strong class="text-dark"><i class="bi bi-person-badge text-info me-1"></i> Control de Personal:</strong>
                                    Padrón de técnicos, supervisores y operarios con DNI, teléfono, cargo, fotocheck, proyecto asignado y régimen laboral.
                                </li>
                                <li>
                                    <strong class="text-dark"><i class="bi bi-people text-info me-1"></i> Cuadrillas de Trabajo:</strong>
                                    Conformación de brigadas operativas designando un líder o capataz responsable. Permite asignar dotaciones completas a toda la cuadrilla en un solo movimiento.
                                </li>
                                <li>
                                    <strong class="text-dark"><i class="bi bi-calendar3 text-info me-1"></i> Programación de Roster 14x7:</strong>
                                    Planificador de guardias rotativas (ej. 14 días trabajados x 7 días de descanso). Impide o alerta preventivamente en el despacho si un técnico está programado en descanso.
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- 3. Inventario & Activos -->
                    <div class="col-lg-6">
                        <div class="border rounded-3 p-3 h-100 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-warning p-2 rounded-3 text-dark"><i class="bi bi-box-seam fs-6"></i></span>
                                <h6 class="fw-bold text-heading mb-0">3. Inventario & Activos (Catálogo)</h6>
                            </div>
                            <ul class="list-unstyled mb-0 small text-muted d-flex flex-column gap-2 mt-3">
                                <li>
                                    <strong class="text-dark"><i class="bi bi-box text-warning me-1"></i> Artículos General:</strong>
                                    Catálogo maestro de materiales, consumibles, herramientas menores y EPPs. Incluye carga de imágenes con previsualización en vivo, stock mínimo y control de serialización.
                                </li>
                                <li>
                                    <strong class="text-dark"><i class="bi bi-qr-code text-warning me-1"></i> Artículos Serializados (Activos):</strong>
                                    Hoja de vida unitaria de cada equipo mayor con serie de fábrica, código patrimonial interno, código QR dinámico en SVG, custodio actual, ubicación y condición operativa (`OPERATIVO`, `EN_MANTENIMIENTO`, `DE_BAJA`).
                                </li>
                                <li>
                                    <strong class="text-dark"><i class="bi bi-boxes text-warning me-1"></i> Kits de Herramientas:</strong>
                                    Agrupación estandarizada de herramientas (ej. Kit Empalme Fibra, Kit Trabajo en Altura). Facilita el despacho grupal en un clic sin registrar ítem por ítem.
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- 4. Gestión de Almacén -->
                    <div class="col-lg-6">
                        <div class="border rounded-3 p-3 h-100 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success p-2 rounded-3"><i class="bi bi-arrow-left-right fs-6"></i></span>
                                <h6 class="fw-bold text-heading mb-0">4. Gestión de Almacén (Operaciones)</h6>
                            </div>
                            <ul class="list-unstyled mb-0 small text-muted d-flex flex-column gap-2 mt-3">
                                <li>
                                    <strong class="text-dark"><i class="bi bi-box-arrow-in-down text-success me-1"></i> Ingresos / Entradas:</strong>
                                    Recepción formal de mercadería por compras, transferencias o saldos iniciales. Aumenta automáticamente el stock físico en el almacén de destino y registra el movimiento en Kardex.
                                </li>
                                <li>
                                    <strong class="text-dark"><i class="bi bi-box-arrow-up-right text-success me-1"></i> Salidas / Préstamos (Despachos):</strong>
                                    Entrega de materiales y asignación de activos a custodios con captura de firma digital en pantalla táctil, emisión de Acta oficial en PDF y control de saldo pendiente en campo.
                                </li>
                                <li>
                                    <strong class="text-dark"><i class="bi bi-layers text-success me-1"></i> Stock Disponible:</strong>
                                    Visor en tiempo real del saldo disponible vs. comprometido en campo por almacén y por artículo, con alertas semafóricas de umbral mínimo.
                                </li>
                                <li>
                                    <strong class="text-dark"><i class="bi bi-bell text-success me-1"></i> Centro de Alertas:</strong>
                                    Monitor de contingencias que consolida: existencias bajo el mínimo crítico, préstamos vencidos sin devolver y calibraciones próximas a caducar o vencidas.
                                </li>
                                <li>
                                    <strong class="text-dark"><i class="bi bi-journal-text text-success me-1"></i> Historial de Kardex:</strong>
                                    Libro cronológico inmutable de movimientos multialmacén con entradas, salidas, saldos acumulados, documento de referencia y usuario auditor responsable.
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- 5. Mantenimiento -->
                    <div class="col-lg-6">
                        <div class="border rounded-3 p-3 h-100 bg-body-tertiary border-primary">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-danger p-2 rounded-3"><i class="bi bi-tools fs-6"></i></span>
                                <h6 class="fw-bold text-heading mb-0">5. Mantenimiento & Taller</h6>
                            </div>
                            <ul class="list-unstyled mb-0 small text-muted d-flex flex-column gap-2 mt-3">
                                <li>
                                    <strong class="text-dark"><i class="bi bi-wrench-adjustable text-danger me-1"></i> Calibraciones & Taller:</strong>
                                    Módulo neurálgico de seguimiento a equipos de precisión y herramientas pesadas. Gestiona el ciclo de vida técnico: ingreso a taller, seguimiento preventivo/correctivo, control de certificado de calibración y <strong>alta técnica con retorno a almacén</strong>.
                                </li>
                                <li>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace py-1 px-2">
                                        <i class="bi bi-check-circle-fill"></i> Pestañas integradas: En Taller (Activos), Concluidos (Histórico) y Todos.
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- 6. Reportabilidad -->
                    <div class="col-lg-6">
                        <div class="border rounded-3 p-3 h-100 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-secondary p-2 rounded-3"><i class="bi bi-file-earmark-spreadsheet fs-6"></i></span>
                                <h6 class="fw-bold text-heading mb-0">6. Reportabilidad & Análisis</h6>
                            </div>
                            <ul class="list-unstyled mb-0 small text-muted d-flex flex-column gap-2 mt-3">
                                <li>
                                    <strong class="text-dark"><i class="bi bi-file-earmark-excel text-success me-1"></i> Centro de Reportes:</strong>
                                    Generador de informes consolidados con filtros por proyecto, rango de fechas y almacén. Exporta sábanas en Excel (.xlsx) nativo y genera documentos PDF membretados para gerencia y auditoría contable.
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- 7. Configuración Inicial -->
                    <div class="col-lg-6">
                        <div class="border rounded-3 p-3 h-100 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-dark p-2 rounded-3"><i class="bi bi-sliders fs-6"></i></span>
                                <h6 class="fw-bold text-heading mb-0">7. Configuración Inicial & Administración</h6>
                            </div>
                            <ul class="list-unstyled mb-0 small text-muted d-flex flex-column gap-2 mt-3">
                                <li><strong class="text-dark">Datos de la Empresa:</strong> Configuración de Razón Social, RUC, logo corporativo, pie de página de actas y datos legales.</li>
                                <li><strong class="text-dark">Gestión de Proyectos:</strong> Creación y conmutación de proyectos aislados (Multi-Tenant por sesión).</li>
                                <li><strong class="text-dark">Centros de Almacén:</strong> Alta de almacenes centrales, de proyecto o móviles (furgones/camionetas).</li>
                                <li><strong class="text-dark">Categorías de Bienes:</strong> Taxonomía de artículos (Equipos, Materiales, EPPs, Herramientas).</li>
                                <li><strong class="text-dark">Usuarios & Roles:</strong> Control granular de cuentas de usuario, asignación de proyectos y perfiles (`ADMINISTRADOR`, `LOGISTICO`, `SUPERVISOR`, `TECNICO`, `AUDITOR`).</li>
                                <li><strong class="text-dark">Log del Sistema (Auditoría):</strong> Bitácora de accesos, creaciones, modificaciones y eliminaciones.</li>
                                <li><strong class="text-dark">Backups de Base de Datos:</strong> Generación y descarga directa de volcados SQL de respaldo.</li>
                            </ul>
                        </div>
                    </div>

                    <!-- 8. Ayuda & Soporte -->
                    <div class="col-lg-6">
                        <div class="border rounded-3 p-3 h-100 bg-body-tertiary">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-primary p-2 rounded-3"><i class="bi bi-question-circle fs-6"></i></span>
                                <h6 class="fw-bold text-heading mb-0">8. Ayuda & Soporte</h6>
                            </div>
                            <ul class="list-unstyled mb-0 small text-muted d-flex flex-column gap-2 mt-3">
                                <li>
                                    <strong class="text-dark"><i class="bi bi-book text-primary me-1"></i> Documentación & Guía:</strong>
                                    Este manual interactivo con especificación de flujos, diagramas de proceso, casuísticas de campo reales y especificaciones del stack tecnológico.
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- ======================================================== -->
        <!-- TAB 2: PROCESOS & FLUJOS CLAVE OPERATIVOS                -->
        <!-- ======================================================== -->
        <div class="tab-pane fade" id="tab-procesos" role="tabpanel">

            <!-- Flujo 1: Recepción e Ingreso de Stock -->
            <div class="admin-card p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="badge bg-success rounded-pill px-3 py-2 fw-bold">Flujo A</span>
                    <h5 class="fw-bold mb-0 text-heading">Recepción de Mercadería, Ingreso a Almacén e Impacto en Kardex</h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="fw-bold text-success mb-1">1. Factura o Guía Proveedor</div>
                            <p class="text-muted small mb-0">El almacenero recibe los bultos o equipos en el almacén físico junto a la guía de remisión del proveedor o contratista.</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="fw-bold text-success mb-1">2. Registro de Ingreso</div>
                            <p class="text-muted small mb-0">En <strong>Operaciones > Ingresos</strong>, se selecciona el almacén de destino, tipo de ingreso (Compra/Traslado) y se agregan los ítems con sus cantidades.</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="fw-bold text-success mb-1">3. Alta de Activos Serializados</div>
                            <p class="text-muted small mb-0">Si el artículo es serializado, se registran sus números de serie de fábrica. El sistema autogenera su código interno y su código QR en SVG.</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="fw-bold text-success mb-1">4. Actualización en Kardex</div>
                            <p class="text-muted small mb-0">En una transacción segura (`DB::transaction`), se incrementa el saldo en `inventario_stock` y se asienta el movimiento positivo en el Kardex.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flujo 2: Despacho a Campo con Firma y Roster -->
            <div class="admin-card p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="badge bg-primary rounded-pill px-3 py-2 fw-bold">Flujo B</span>
                    <h5 class="fw-bold mb-0 text-heading">Despacho de Materiales / Préstamo de Herramientas con Firma Digital</h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="fw-bold text-primary mb-1">1. Solicitud & Custodio</div>
                            <p class="text-muted small mb-0">El técnico o líder de cuadrilla solicita materiales o equipos para su jornada de trabajo en el frente de obra.</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="fw-bold text-primary mb-1">2. Validación de Roster 14x7</div>
                            <p class="text-muted small mb-0">El sistema verifica si el técnico está de guardia o en descanso (franco), emitiendo una alerta preventiva en caso de descanso programado.</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="fw-bold text-primary mb-1">3. Escaneo QR o Selección</div>
                            <p class="text-muted small mb-0">Se agregan consumibles o se escanea la etiqueta QR del activo serializado. El activo pasa a condición <code>PRESTADO</code>.</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="fw-bold text-primary mb-1">4. Firma Digital & Acta PDF</div>
                            <p class="text-muted small mb-0">El receptor firma digitalmente en pantalla mediante Signature Pad. Se emite el Acta de Entrega membretada en PDF y se afecta el Kardex.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flujo 3: Devoluciones y Cierre de Saldo -->
            <div class="admin-card p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="badge bg-info text-white rounded-pill px-3 py-2 fw-bold">Flujo C</span>
                    <h5 class="fw-bold mb-0 text-heading">Devolución de Herramientas, Reingreso y Liquidación de Saldo en Campo</h5>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="fw-bold text-info mb-1">1. Retorno al Almacén</div>
                            <p class="text-muted small mb-0">Al culminar la jornada o el turno de 14 días, el técnico retorna los equipos y el excedente de materiales al almacén base.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="fw-bold text-info mb-1">2. Registro de Devolución</div>
                            <p class="text-muted small mb-0">Desde el detalle del despacho o en <strong>Devoluciones</strong>, el almacenero marca los ítems retornados y su estado físico (Bueno / Deteriorado / Requiere Mantenimiento).</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light h-100">
                            <div class="fw-bold text-info mb-1">3. Liberación de Custodia</div>
                            <p class="text-muted small mb-0">El activo regresa a condición <code>DISPONIBLE</code>, el saldo en campo del técnico se salda y se asienta el reingreso correspondiente en Kardex.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ======================================================== -->
        <!-- TAB 3: CALIBRACIONES & TALLER (FLUJO COMPLETO)           -->
        <!-- ======================================================== -->
        <div class="tab-pane fade" id="tab-taller" role="tabpanel">

            <div class="admin-card p-4 mb-4 border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-tools fs-3 text-danger"></i>
                        <div>
                            <h5 class="fw-bold mb-0 text-heading">Flujo de Calibraciones, Mantenimiento Técnico y Proceso de Alta</h5>
                            <small class="text-muted">Garantía de trazabilidad estricta: ningún equipo en mantenimiento puede ser usado ni activado sin alta técnica formal.</small>
                        </div>
                    </div>
                    <span class="badge bg-danger text-white px-3 py-2 rounded-pill font-monospace">Flujo Estricto & Seguro</span>
                </div>

                <!-- Resumen Visual del Ciclo de Vida -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 border border-danger-subtle rounded-3 bg-danger bg-opacity-10 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2 text-danger fw-bold">
                                <i class="bi bi-box-arrow-right fs-5"></i>
                                <span>Paso 1: Envío a Taller</span>
                            </div>
                            <p class="small text-muted mb-0">
                                Cuando un activo presenta fallas o requiere calibración periódica (ej. fusionadoras, OTDR, telurómetros), se registra en <strong>Calibraciones & Taller</strong> o se pasa a <code>EN_MANTENIMIENTO</code> en la edición del activo.
                            </p>
                            <div class="mt-2 text-danger small fw-semibold">
                                <i class="bi bi-arrow-right-circle"></i> Se enlista automáticamente en Taller (`EN_PROCESO`).
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border border-warning-subtle rounded-3 bg-warning bg-opacity-10 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2 text-warning-emphasis fw-bold">
                                <i class="bi bi-shield-lock-fill fs-5"></i>
                                <span>Paso 2: Bloqueo de Activación</span>
                            </div>
                            <p class="small text-muted mb-0">
                                <strong>Regla de negocio estricta:</strong> El activo NO puede cambiarse a <code>OPERATIVO</code> directamente desde el catálogo mientras tenga un servicio abierto en taller. Esto evita el uso accidental de equipos descalibrados o defectuosos.
                            </p>
                            <div class="mt-2 text-warning-emphasis small fw-semibold">
                                <i class="bi bi-lock-fill"></i> Bloqueo en UI y validación en backend.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border border-success-subtle rounded-3 bg-success bg-opacity-10 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2 text-success fw-bold">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                                <span>Paso 3: Alta Técnica & Retorno</span>
                            </div>
                            <p class="small text-muted mb-0">
                                La <strong>única forma</strong> de dar de alta al equipo es desde el módulo de <strong>Calibraciones & Taller</strong> pulsando el botón <strong>"Dar de Alta"</strong> con el resultado técnico conforme, costo y próxima fecha de calibración.
                            </p>
                            <div class="mt-2 text-success small fw-semibold">
                                <i class="bi bi-arrow-return-left"></i> Retorna a almacén y desaparece de la lista activa.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalle de las Pestañas de Calibraciones & Taller -->
                <div class="card bg-body-tertiary border-0 p-3 mb-4 rounded-3">
                    <h6 class="fw-bold text-heading mb-3"><i class="bi bi-segmented-nav text-primary me-2"></i>Navegación por Pestañas en el Módulo de Calibraciones & Taller:</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-white rounded border h-100">
                                <span class="badge bg-danger rounded-pill mb-2">Pestaña 1</span>
                                <h6 class="fw-bold text-danger mb-1"><i class="bi bi-tools me-1"></i> En Taller / Activos (Por Defecto)</h6>
                                <p class="text-muted small mb-0">
                                    Muestra exclusivamente los equipos que se encuentran actualmente en servicio técnico o laboratorio (`EN_PROCESO`). Aquí se encuentra activo el botón verde <strong>"Dar de Alta"</strong> para registrar el retorno conforme.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-white rounded border h-100">
                                <span class="badge bg-success rounded-pill mb-2">Pestaña 2</span>
                                <h6 class="fw-bold text-success mb-1"><i class="bi bi-check2-circle me-1"></i> Concluidos / Histórico</h6>
                                <p class="text-muted small mb-0">
                                    Historial de todos los servicios culminados exitosamente con sus fechas de salida, costos acumulados, certificados emitidos y proveedores. Al dar de alta un activo, <strong>se traslada automáticamente a esta pestaña</strong>.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-white rounded border h-100">
                                <span class="badge bg-secondary rounded-pill mb-2">Pestaña 3</span>
                                <h6 class="fw-bold text-secondary mb-1"><i class="bi bi-collection me-1"></i> Todos los Registros</h6>
                                <p class="text-muted small mb-0">
                                    Vista consolidada global con buscador rápido, filtros avanzados y paginación para auditorías completas de costos de mantenimiento de la empresa.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ¿Qué ocurre al dar de alta? -->
                <div class="p-3 rounded-3 border bg-white">
                    <h6 class="fw-bold text-heading mb-2"><i class="bi bi-gear-wide-connected text-primary me-2"></i>Efectos Inmediatos al Confirmar el "Alta Técnica":</h6>
                    <ul class="mb-0 small text-muted d-flex flex-column gap-2">
                        <li>
                            <i class="bi bi-check2-square text-success me-1"></i>
                            <strong>Estado del Activo:</strong> Pasa de <code>EN_MANTENIMIENTO</code> a <code>OPERATIVO</code> de forma inmediata.
                        </li>
                        <li>
                            <i class="bi bi-check2-square text-success me-1"></i>
                            <strong>Condición de Préstamo:</strong> Se restablece a <code>DISPONIBLE</code> y se registra su <code>fecha_ultimo_retorno</code>.
                        </li>
                        <li>
                            <i class="bi bi-check2-square text-success me-1"></i>
                            <strong>Sincronización de Stock:</strong> El servicio `InventarioStockService` recalcula y suma la unidad al stock disponible del almacén de asignación.
                        </li>
                        <li>
                            <i class="bi bi-check2-square text-success me-1"></i>
                            <strong>Cierre del Ticket Técnico:</strong> El registro de taller cambia su estado a <code>CONFORME_OPERATIVO</code>, guardando la fecha de salida real, observaciones del trabajo y costo de la intervención.
                        </li>
                        <li>
                            <i class="bi bi-check2-square text-success me-1"></i>
                            <strong>Depuración de la Lista de Taller:</strong> El activo desaparece de la pestaña <strong>"En Taller"</strong> y queda registrado en el historial de concluidos.
                        </li>
                    </ul>
                </div>

            </div>

        </div>

        <!-- ======================================================== -->
        <!-- TAB 4: CENTRO DE REPORTES & EXPORTACIONES                -->
        <!-- ======================================================== -->
        <div class="tab-pane fade" id="tab-reportes" role="tabpanel">

            <div class="admin-card p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <i class="bi bi-file-earmark-spreadsheet fs-4 text-success"></i>
                    <div>
                        <h5 class="fw-bold mb-0 text-heading">Centro de Reportes: Exportaciones Excel (.xlsx) y Documentos PDF</h5>
                        <small class="text-muted">Generación masiva de datos estructurados para liquidación de obra, balances contables y auditorías externas.</small>
                    </div>
                </div>

                <div class="row g-4">

                    <!-- Excel -->
                    <div class="col-lg-6">
                        <div class="p-3 border rounded-3 bg-body-tertiary h-100">
                            <div class="d-flex align-items-center gap-2 mb-3 text-success">
                                <i class="bi bi-file-earmark-excel fs-3"></i>
                                <h6 class="fw-bold mb-0 text-heading">Reportes en Hoja de Cálculo Excel (.xlsx)</h6>
                            </div>
                            <div class="d-flex flex-column gap-3 small text-muted">
                                <div class="p-2 border rounded bg-white">
                                    <strong class="text-dark d-block mb-1"><i class="bi bi-download text-success me-1"></i> Balance de Stock Multialmacén:</strong>
                                    Planilla con código de artículo, SKU, descripción, categoría, unidad de medida, stock total, stock disponible, stock prestado y valorización económica.
                                </div>
                                <div class="p-2 border rounded bg-white">
                                    <strong class="text-dark d-block mb-1"><i class="bi bi-download text-success me-1"></i> Libro de Movimientos de Kardex:</strong>
                                    Detalle cronológico de ingresos, salidas, transferencias y mermas por proyecto activo y almacén físico, con saldo acumulado.
                                </div>
                                <div class="p-2 border rounded bg-white">
                                    <strong class="text-dark d-block mb-1"><i class="bi bi-download text-success me-1"></i> Sábana de Activos Serializados:</strong>
                                    Listado completo con códigos patrimoniales, series de fabricante, modelo, costo de adquisición, custodio actual y estado de calibración.
                                </div>
                                <div class="p-2 border rounded bg-white">
                                    <strong class="text-dark d-block mb-1"><i class="bi bi-download text-success me-1"></i> Historial de Calibraciones & Taller:</strong>
                                    Registro de costos devengados en mantenimiento preventivo, fechas de calibración e identificación de proveedores y certificados.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PDF -->
                    <div class="col-lg-6">
                        <div class="p-3 border rounded-3 bg-body-tertiary h-100">
                            <div class="d-flex align-items-center gap-2 mb-3 text-danger">
                                <i class="bi bi-file-earmark-pdf fs-3"></i>
                                <h6 class="fw-bold mb-0 text-heading">Documentos Oficiales en PDF (DomPDF)</h6>
                            </div>
                            <div class="d-flex flex-column gap-3 small text-muted">
                                <div class="p-2 border rounded bg-white">
                                    <strong class="text-dark d-block mb-1"><i class="bi bi-printer text-danger me-1"></i> Acta de Entrega / Despacho:</strong>
                                    Documento formal con membrete legal de la empresa, datos del custodio, proyecto, detalle de ítems, código QR y <strong>firma digital capturada</strong>.
                                </div>
                                <div class="p-2 border rounded bg-white">
                                    <strong class="text-dark d-block mb-1"><i class="bi bi-printer text-danger me-1"></i> Etiquetas Térmicas de Activos:</strong>
                                    Formato optimizado para impresión térmica autoadhesiva con código QR en SVG, código interno legible y nombre del activo para pegado en campo.
                                </div>
                                <div class="p-2 border rounded bg-white">
                                    <strong class="text-dark d-block mb-1"><i class="bi bi-printer text-danger me-1"></i> Planilla de Roster de Turnos 14x7:</strong>
                                    Calendario mensual impreso de asignación de guardias y relevos por técnico para control en tablero de operaciones en campamento.
                                </div>
                                <div class="p-2 border rounded bg-white">
                                    <strong class="text-dark d-block mb-1"><i class="bi bi-printer text-danger me-1"></i> Hoja de Servicio de Taller:</strong>
                                    Constancia de ingreso y salida técnica con diagnóstico del especialista y conformidad de operatividad.
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- ======================================================== -->
        <!-- TAB 5: GUÍA DE CASOS REALES & MATRIZ DE USUARIOS Y ROLES  -->
        <!-- ======================================================== -->
        <div class="tab-pane fade" id="tab-guia" role="tabpanel" aria-labelledby="guia-tab">

            <!-- Matriz de Usuarios, Roles y Permisos -->
            <div class="admin-card p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <i class="bi bi-shield-lock-fill fs-4 text-primary" aria-hidden="true"></i>
                    <div>
                        <h5 class="fw-bold mb-0 text-heading">Gestión de Usuarios, Matriz de Roles y Niveles de Acceso</h5>
                        <small class="text-muted">Control de seguridad basado en perfiles para garantizar la segregación de funciones operativas y contables.</small>
                    </div>
                </div>

                <div class="table-responsive" role="region" aria-label="Matriz de Roles y Niveles de Acceso del Sistema" tabindex="0">
                    <table class="table table-bordered table-hover align-middle small mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th scope="col" class="text-start">Módulo / Funcionalidad</th>
                                <th scope="col"><span class="badge bg-primary">ADMINISTRADOR</span></th>
                                <th scope="col"><span class="badge bg-success">LOGISTICO</span></th>
                                <th scope="col"><span class="badge bg-warning text-dark">SUPERVISOR</span></th>
                                <th scope="col"><span class="badge bg-info text-white">TECNICO</span></th>
                                <th scope="col"><span class="badge bg-secondary">AUDITOR</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-semibold">Dashboard General & Métricas</td>
                                <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Total</td>
                                <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Total</td>
                                <td class="text-center text-primary"><i class="bi bi-check-circle"></i> Proyecto</td>
                                <td class="text-center text-muted"><i class="bi bi-dash"></i> Básico</td>
                                <td class="text-center text-success"><i class="bi bi-eye-fill"></i> Lectura</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Catálogo de Artículos & Activos</td>
                                <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Total</td>
                                <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Total</td>
                                <td class="text-center text-primary"><i class="bi bi-eye-fill"></i> Consulta</td>
                                <td class="text-center text-muted"><i class="bi bi-eye-fill"></i> Consulta</td>
                                <td class="text-center text-success"><i class="bi bi-eye-fill"></i> Lectura</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Ingresos, Despachos & Kardex</td>
                                <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Total</td>
                                <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Total</td>
                                <td class="text-center text-primary"><i class="bi bi-check-circle"></i> Aprobar</td>
                                <td class="text-center text-info"><i class="bi bi-pen-fill"></i> Firma Receptor</td>
                                <td class="text-center text-success"><i class="bi bi-eye-fill"></i> Lectura</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Calibraciones & Taller (Alta Técnica)</td>
                                <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Total</td>
                                <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Total</td>
                                <td class="text-center text-warning"><i class="bi bi-exclamation-circle"></i> Registro</td>
                                <td class="text-center text-danger"><i class="bi bi-x-circle"></i> No</td>
                                <td class="text-center text-success"><i class="bi bi-eye-fill"></i> Lectura</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Centro de Reportes (Excel / PDF)</td>
                                <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Total</td>
                                <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Total</td>
                                <td class="text-center text-primary"><i class="bi bi-check-circle"></i> Proyecto</td>
                                <td class="text-center text-danger"><i class="bi bi-x-circle"></i> No</td>
                                <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Total</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Configuración Empresa, Usuarios & Backups</td>
                                <td class="text-center text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Total</td>
                                <td class="text-center text-danger"><i class="bi bi-x-circle"></i> No</td>
                                <td class="text-center text-danger"><i class="bi bi-x-circle"></i> No</td>
                                <td class="text-center text-danger"><i class="bi bi-x-circle"></i> No</td>
                                <td class="text-center text-danger"><i class="bi bi-x-circle"></i> No</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Detalles y Alcance del Rol TÉCNICO -->
                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-info-subtle bg-opacity-25 h-100">
                            <h6 class="fw-bold text-info-emphasis d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-wrench-adjustable"></i> Alcance Operativo del Rol TÉCNICO
                            </h6>
                            <p class="small text-muted mb-2">
                                Diseñado para colaboradores de campo, cuadrilleros e instaladores que necesitan consultar disponibilidad de herramientas y dar fe de recepción:
                            </p>
                            <ul class="small text-muted mb-0 ps-3">
                                <li><strong>Consola Básica:</strong> Dashboard simplificado enfocado en sus equipos en custodia y préstamos por devolver.</li>
                                <li><strong>Catálogo en Modo Lectura:</strong> Búsqueda de artículos, repuestos, kits y activos con código QR sin botones de edición.</li>
                                <li><strong>Firma Receptora:</strong> Capacidad para firmar digitalmente las actas de entrega de herramientas y consumibles.</li>
                                <li><strong>Seguridad Restrictiva:</strong> Sin acceso a inventarios valorizados, altas de calibraciones, reportes contables ni configuración.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 border bg-primary-subtle bg-opacity-25 h-100">
                            <h6 class="fw-bold text-primary-emphasis d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-sliders2"></i> Gestión Interactiva de Permisos por Rol
                            </h6>
                            <p class="small text-muted mb-2">
                                En el módulo <strong>Usuarios & Roles</strong> (pestaña <em>"Roles & Gestión de Permisos"</em>), el Administrador puede:
                            </p>
                            <ul class="small text-muted mb-0 ps-3">
                                <li>Visualizar la <strong>Matriz Oficial de Seguridad y Niveles de Acceso</strong> en tiempo real.</li>
                                <li>Consultar el desglose de 29 permisos agrupados en 6 módulos funcionales.</li>
                                <li>Ajustar mediante casillas de verificación los permisos asignados a cada rol del sistema.</li>
                                <li>Sincronización instantánea de permisos en base a la infraestructura Spatie Permissions.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Casos Prácticos de Operación -->
            <div class="row g-4">

                <!-- Caso 1 -->
                <div class="col-lg-6">
                    <div class="admin-card p-4 h-100 border-start border-4 border-primary">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-bold">Caso Práctico 1</span>
                            <span class="small text-muted font-monospace"><i class="bi bi-broadcast me-1"></i> Telecomunicaciones</span>
                        </div>
                        <h5 class="fw-bold text-heading mb-2">Despliegue de Red FTTH (Fibra Óptica Urbana)</h5>
                        <p class="text-muted small">
                            Se inicia el tendido de fibra en un nuevo frente. Se requiere despachar fusionadoras, cortadoras de precisión y bobinas de fibra a 3 cuadrillas.
                        </p>
                        <div class="bg-light p-3 rounded border small mb-3">
                            <ol class="mb-0 ps-3 text-muted d-flex flex-column gap-1">
                                <li>Se selecciona el proyecto activo <code>PRY-LIMA-SUR</code> en el conmutador superior.</li>
                                <li>El almacenero crea el <strong>Kit Empalme FTTH</strong> para agrupar herramientas menores.</li>
                                <li>Se escanea el QR de la <strong>Fusionadora Fujikura 90S+</strong> (código <code>EQ-FUS-042</code>).</li>
                                <li>El técnico firma con el dedo en la tablet del almacén. El sistema genera el Acta PDF membretada y descuenta los consumibles del Kardex.</li>
                            </ol>
                        </div>
                        <div class="small text-success fw-semibold"><i class="bi bi-check-circle me-1"></i> Beneficio: Custodia legal garantizada y trazabilidad de activos de alto valor.</div>
                    </div>
                </div>

                <!-- Caso 2 -->
                <div class="col-lg-6">
                    <div class="admin-card p-4 h-100 border-start border-4 border-danger">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-bold">Caso Práctico 2</span>
                            <span class="small text-muted font-monospace"><i class="bi bi-wrench me-1"></i> Mantenimiento & Calibración</span>
                        </div>
                        <h5 class="fw-bold text-heading mb-2">Envío de OTDR a Calibración y Proceso de Alta Técnica</h5>
                        <p class="text-muted small">
                            Un reflectómetro óptico (OTDR) cumple su ciclo de calibración anual. Debe retirarse de operación y enviarse a laboratorio certificado.
                        </p>
                        <div class="bg-light p-3 rounded border small mb-3">
                            <ol class="mb-0 ps-3 text-muted d-flex flex-column gap-1">
                                <li>En el activo, se marca como <code>EN_MANTENIMIENTO</code>: se crea automáticamente el servicio en <strong>Calibraciones & Taller</strong>.</li>
                                <li>El equipo queda bloqueado en almacén; nadie puede ponerlo en <code>OPERATIVO</code> por error.</li>
                                <li>Al regresar de laboratorio con su certificado, el jefe de taller entra a la pestaña <strong>"En Taller"</strong> y pulsa <strong>"Dar de Alta"</strong>.</li>
                                <li>Ingresa fecha de salida, costo y nueva fecha de calibración. El equipo se vuelve <code>OPERATIVO / DISPONIBLE</code> y pasa a la pestaña <strong>"Concluidos"</strong>.</li>
                            </ol>
                        </div>
                        <div class="small text-danger fw-semibold"><i class="bi bi-shield-check me-1"></i> Beneficio: Cumplimiento de normas ISO/auditorías y cero riesgo de uso de equipos vencidos.</div>
                    </div>
                </div>

            </div>

        </div>

        <!-- ======================================================== -->
        <!-- TAB 6: ARQUITECTURA TÉCNICA & STACK TECNOLÓGICO          -->
        <!-- ======================================================== -->
        <div class="tab-pane fade" id="tab-stack" role="tabpanel">

            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="admin-card p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-3 text-primary">
                            <i class="bi bi-layers-fill fs-4"></i>
                            <h5 class="fw-bold mb-0 text-heading">Núcleo Backend</h5>
                        </div>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small">
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong>PHP 8.4:</strong> Constructor property promotion, tipos estrictos, operadores null-safe, match expressions y enums nativos.
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong>Laravel 11.x:</strong> Service Providers limpios, validaciones FormRequests, middleware modular y transacciones ACID (`DB::transaction`).
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong>Motor de Base de Datos:</strong> MySQL / MariaDB con integridad referencial, claves foráneas e índices optimizados en Kardex y Stock.
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="admin-card p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-3 text-info">
                            <i class="bi bi-palette-fill fs-4"></i>
                            <h5 class="fw-bold mb-0 text-heading">Frontend & Experiencia UI</h5>
                        </div>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small">
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong>Bootstrap 5.3:</strong> Sistema Glassmorphic dark/light, microinteracciones y diseño adaptado a tablets y móviles en campo.
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong>SweetAlert2:</strong> Diálogos modales no intrusivos para confirmaciones críticas, avisos de stock y alertas operativas.
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong>Signature Pad:</strong> Captura biométrica de firma digital en canvas HTML5 para actas de recepción en almacén.
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="admin-card p-4 h-100">
                        <div class="d-flex align-items-center gap-2 mb-3 text-warning">
                            <i class="bi bi-plugin fs-4"></i>
                            <h5 class="fw-bold mb-0 text-heading">Componentes & Reportes</h5>
                        </div>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small">
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong>Simple QrCode:</strong> Renderizado SVG dinámico de códigos QR para activos y generación de etiquetas térmicas de campo.
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong>Barryvdh DomPDF:</strong> Emisión de Actas de Entrega legalizadas, dotaciones e inventarios con membrete dinámico.
                                </div>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                <div>
                                    <strong>Maatwebsite Excel:</strong> Generación de planillas XLSX de existencias, kardex y sábanas de activos con estilos corporativos.
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Multi-Tenant por Sesión -->
            <div class="admin-card p-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <i class="bi bi-diagram-3-fill fs-4 text-primary"></i>
                    <h5 class="fw-bold mb-0 text-heading">Arquitectura Multi-Proyecto Aislado (Multi-Tenant por Sesión)</h5>
                </div>
                <p class="text-muted small mb-0">
                    El sistema aplica un modelo de segmentación de datos estricto basado en el <strong>Proyecto Activo</strong> seleccionado en la barra superior. Esto permite que una empresa gestione simultáneamente diferentes obras y contratos manteniendo independientes sus existencias de almacén, personal de campo, órdenes de despacho y kardex, evitando contaminación cruzada de costos e inventario.
                </p>
            </div>

        </div>

    </div>

@endsection
