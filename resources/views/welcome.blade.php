<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $empresaGlobal = \App\Models\EmpresaConfig::instancia(); @endphp
    <title>{{ $empresaGlobal->sistema_nombre ?: config('app.name', 'LogisticPCS') }} — Plataforma de Gestión Logística & Operaciones</title>
    
    <!-- Favicon del Sistema -->
    <link rel="icon" type="image/webp" href="{{ $empresaGlobal->icono_url }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --lp-primary: #0f2b48;
            --lp-accent: #0284c7;
            --lp-success: #10b981;
            --lp-dark: #091a2c;
            --lp-card-bg: #ffffff;
            --lp-body-bg: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--lp-body-bg);
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-brand-custom {
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--lp-primary);
        }

        .hero-banner {
            background: radial-gradient(120% 120% at 50% 10%, #1e3a5f 0%, #0a192f 100%);
            color: #ffffff;
            padding: 5rem 0 4rem;
            position: relative;
            overflow: hidden;
        }

        .hero-banner::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: linear-gradient(to top right, var(--lp-body-bg) 49%, transparent 51%);
        }

        .module-card {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            transition: all 0.25s ease-in-out;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(15, 43, 72, 0.08), 0 8px 10px -6px rgba(15, 43, 72, 0.04);
            border-color: #cbd5e1;
        }

        .module-card.active-module {
            border-color: #38bdf8;
            box-shadow: 0 10px 15px -3px rgba(2, 132, 199, 0.1);
        }

        .module-card.future-module {
            border-style: dashed;
            background: #fdfefe;
            border-color: #cbd5e1;
        }

        .icon-box {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .badge-live {
            background-color: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.3);
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 20px;
        }

        .badge-roadmap {
            background-color: rgba(2, 132, 199, 0.1);
            color: #0284c7;
            border: 1px solid rgba(2, 132, 199, 0.3);
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 20px;
        }

        .telemetry-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand navbar-brand-custom d-flex align-items-center gap-2" href="{{ url('/') }}">
                <span class="p-1 rounded-2 border bg-white shadow-sm d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <img src="{{ $empresaGlobal->icono_url }}" alt="Logo" style="max-width: 28px; max-height: 28px; object-fit: contain;">
                </span>
                <span>{{ $empresaGlobal->sistema_nombre ?: 'LogisticPCS' }}</span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <span class="d-none d-md-inline-flex align-items-center gap-2 badge-live">
                    <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 8px; height: 8px;"></span>
                    Sistema Operativo
                </span>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
                        <i class="bi bi-speedometer2 me-1"></i> Ir al Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm px-3 fw-semibold" style="background-color: var(--lp-accent); border-color: var(--lp-accent);">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-banner">
        <div class="container text-center py-4">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <span class="badge bg-white text-dark mb-3 px-3 py-2 fw-semibold rounded-pill shadow-sm">
                        <i class="bi bi-shield-check text-primary me-1"></i> Solución Integral de Operaciones & Logística
                    </span>
                    <h1 class="display-5 fw-bold mb-3">{{ $empresaGlobal->sistema_nombre ?: 'LogisticPCS' }}</h1>
                    <p class="lead text-light opacity-75 mb-4 px-md-5">
                        {{ $empresaGlobal->sistema_subtitulo ?: 'Control integral de inventarios, despacho con firma digital, programación de cuadrillas con régimen 14x7 y trazabilidad transaccional de kardex.' }}
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="{{ route('login') }}" class="btn btn-light btn-lg px-4 fw-bold shadow-sm text-primary">
                            <i class="bi bi-box-arrow-in-right me-2 text-primary"></i> Ingresar al Sistema
                        </a>
                        <a href="#modulos-sistema" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                            <i class="bi bi-grid-3x3-gap me-2"></i> Explorar Módulos
                        </a>
                        <a href="#futuros-proyectos" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                            <i class="bi bi-compass me-2"></i> Próximos Proyectos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Area -->
    <main class="container my-5 flex-grow-1">

        <!-- Business Indicators Banner -->
        <div class="card border-0 shadow-sm mb-5" style="border-radius: 1rem; background: #ffffff;">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">
                            <i class="bi bi-graph-up-arrow text-primary me-2"></i> Indicadores Operativos de la Plataforma
                        </h5>
                        <small class="text-muted">Resumen ejecutivo consolidado de recursos y operaciones en tiempo real</small>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-check-circle-fill me-1"></i> Servicios Activos
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="telemetry-pill">
                            <div class="text-muted small fw-medium">Artículos Catalogados</div>
                            <div class="fs-4 fw-bold text-dark">{{ number_format($stats['articulos'] ?? 0) }}</div>
                            <small class="text-success"><i class="bi bi-box-seam me-1"></i>Inventario maestro</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="telemetry-pill">
                            <div class="text-muted small fw-medium">Activos Serializados (QR)</div>
                            <div class="fs-4 fw-bold text-dark">{{ number_format($stats['activos'] ?? 0) }}</div>
                            <small class="text-primary"><i class="bi bi-qr-code me-1"></i>Con trazabilidad unitaria</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="telemetry-pill">
                            <div class="text-muted small fw-medium">Proyectos Activos</div>
                            <div class="fs-4 fw-bold text-dark">{{ number_format($stats['proyectos'] ?? 0) }}</div>
                            <small class="text-info"><i class="bi bi-building me-1"></i>Centros de costo</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="telemetry-pill">
                            <div class="text-muted small fw-medium">Centros de Almacén</div>
                            <div class="fs-4 fw-bold text-dark">{{ number_format($stats['ubicaciones'] ?? 0) }}</div>
                            <small class="text-secondary"><i class="bi bi-geo-alt me-1"></i>Central, Obra y Taller</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modules Grid (Operativos) -->
        <div id="modulos-sistema" class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Módulos Operativos en Producción</h3>
                    <p class="text-muted mb-0">Ecosistema modular de gestión y trazabilidad para operaciones de alta exigencia.</p>
                </div>
            </div>

            <div class="row g-4">
                
                <!-- Módulo 1: Catálogo & Activos Serializados -->
                <div class="col-lg-6">
                    <div class="card module-card active-module p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-primary text-white" style="background-color: var(--lp-primary) !important;">
                                <i class="bi bi-box-seam-fill"></i>
                            </div>
                            <span class="badge-live">
                                <i class="bi bi-check-circle-fill me-1"></i> Operativo
                            </span>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">Catálogo Maestro & Activos Serializados</h4>
                        <p class="text-muted small flex-grow-1">
                            Gestión de artículos, clasificación por categorías, control de números de serie, placas patrimoniales y generación de códigos QR térmicos de alta definición.
                        </p>
                        <div class="border-top pt-3 mt-3">
                            <div class="row g-2 small text-muted mb-3">
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Activos con QR / Placa</div>
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Kits de Herramientas</div>
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Stock por Almacén</div>
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Categorías Maestras</div>
                            </div>
                            <a href="{{ route('login') }}" class="btn btn-primary w-100 fw-bold py-2" style="background-color: var(--lp-accent); border-color: var(--lp-accent);">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Acceder al Módulo
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Módulo 2: Despachos, Devoluciones & Kardex -->
                <div class="col-lg-6">
                    <div class="card module-card active-module p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-warning text-dark">
                                <i class="bi bi-arrow-left-right"></i>
                            </div>
                            <span class="badge-live">
                                <i class="bi bi-check-circle-fill me-1"></i> Operativo
                            </span>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">Operaciones de Almacén & Kardex PEPS</h4>
                        <p class="text-muted small flex-grow-1">
                            Emisión de vales de salida con firma digitalizada en pantalla, actas de entrega en formato PDF A4, liquidación de devoluciones y auditoría cronológica PEPS.
                        </p>
                        <div class="border-top pt-3 mt-3">
                            <div class="row g-2 small text-muted mb-3">
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Firma Digital en Canvas</div>
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Actas PDF Oficiales</div>
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Devoluciones de Campo</div>
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Kardex PEPS Transaccional</div>
                            </div>
                            <a href="{{ route('login') }}" class="btn btn-outline-primary w-100 fw-bold py-2">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Acceder a Operaciones
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Módulo 3: Cuadrillas & Roster 14x7 -->
                <div class="col-lg-6">
                    <div class="card module-card active-module p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-info text-white">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <span class="badge-live">
                                <i class="bi bi-check-circle-fill me-1"></i> Operativo
                            </span>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">Cuadrillas de Obra & Programación Roster 14x7</h4>
                        <p class="text-muted small flex-grow-1">
                            Administración de cuadrillas operativas con custodia de herramientas y calendario interactivo de turnos con guardias rotativas (Guardia A / Guardia B).
                        </p>
                        <div class="border-top pt-3 mt-3">
                            <div class="row g-2 small text-muted mb-3">
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Dotación Técnica Dinámica</div>
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Calendario Roster 14x7</div>
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Bloqueo de Descanso AJAX</div>
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Hojas de Cargo en PDF</div>
                            </div>
                            <a href="{{ route('login') }}" class="btn btn-outline-primary w-100 fw-bold py-2">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Acceder a Cuadrillas
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Módulo 4: Taller, Calibraciones & Alertas -->
                <div class="col-lg-6">
                    <div class="card module-card active-module p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-danger text-white">
                                <i class="bi bi-tools"></i>
                            </div>
                            <span class="badge-live">
                                <i class="bi bi-check-circle-fill me-1"></i> Operativo
                            </span>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">Taller, Calibraciones & Centro de Alertas</h4>
                        <p class="text-muted small flex-grow-1">
                            Mantenimientos preventivos y correctivos, trazabilidad de calibración metrológica de equipos y notificaciones preventivas de reposición y vencimientos.
                        </p>
                        <div class="border-top pt-3 mt-3">
                            <div class="row g-2 small text-muted mb-3">
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Calibraciones de Lab</div>
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Inmovilización de Activos</div>
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Alertas de Stock Crítico</div>
                                <div class="col-6"><i class="bi bi-check2 text-primary me-1"></i> Escáner Automático</div>
                            </div>
                            <a href="{{ route('login') }}" class="btn btn-outline-primary w-100 fw-bold py-2">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Acceder a Taller & Alertas
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Roadmap / Futuros Proyectos & Extensiones -->
        <div id="futuros-proyectos" class="mb-5 pt-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small rounded-pill fw-semibold">
                            <i class="bi bi-stars me-1"></i> Próximos Lanzamientos
                        </span>
                        <h3 class="fw-bold mb-0">Roadmap de Futuros Módulos</h3>
                    </div>
                    <p class="text-muted mb-0">Extensiones planificadas para potenciar el alcance integral de las operaciones corporativas.</p>
                </div>
                <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill small">
                    <i class="bi bi-arrow-repeat me-1"></i> En Planificación & Diseño
                </span>
            </div>

            <div class="row g-4">
                
                <!-- Futuro 1: Gestión de Flota y Vehículos -->
                <div class="col-md-6 col-lg-4">
                    <div class="card module-card future-module p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-primary-subtle text-primary">
                                <i class="bi bi-truck-front-fill"></i>
                            </div>
                            <span class="badge-roadmap">
                                <i class="bi bi-clock-history me-1"></i> En Roadmap
                            </span>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Gestión de Flota & Vehículos</h5>
                        <p class="text-muted small flex-grow-1">
                            Control integral de vehículos de obra, bitácora de kilometraje, asignación a cuadrillas, consumo de combustible y alertas de vencimiento de SOAT y revisiones técnicas.
                        </p>
                        <div class="border-top pt-3 mt-2">
                            <ul class="list-unstyled small text-muted mb-0">
                                <li><i class="bi bi-check-circle text-primary me-1"></i> Control de SOAT & Revisiones</li>
                                <li><i class="bi bi-check-circle text-primary me-1"></i> Rendimiento de Combustible</li>
                                <li><i class="bi bi-check-circle text-primary me-1"></i> Asignación a Proyectos</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Futuro 2: Gestión de Tareas y Actividades en Campo -->
                <div class="col-md-6 col-lg-4">
                    <div class="card module-card future-module p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-success-subtle text-success">
                                <i class="bi bi-list-check"></i>
                            </div>
                            <span class="badge-roadmap">
                                <i class="bi bi-clock-history me-1"></i> En Roadmap
                            </span>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Tareas & Órdenes de Trabajo (OT)</h5>
                        <p class="text-muted small flex-grow-1">
                            Despacho de órdenes de trabajo a cuadrillas, checklist digital de inicio de jornada, registro de avance físico en obra, geolocalización y captura fotográfica de evidencias.
                        </p>
                        <div class="border-top pt-3 mt-2">
                            <ul class="list-unstyled small text-muted mb-0">
                                <li><i class="bi bi-check-circle text-success me-1"></i> Asignación de OTs en Campo</li>
                                <li><i class="bi bi-check-circle text-success me-1"></i> Evidencias con Georreferencia</li>
                                <li><i class="bi bi-check-circle text-success me-1"></i> Checklist de Seguridad Pre-Jornada</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Futuro 3: Portal de Proveedores & Cotizaciones -->
                <div class="col-md-6 col-lg-4">
                    <div class="card module-card future-module p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-info-subtle text-info">
                                <i class="bi bi-cart-check-fill"></i>
                            </div>
                            <span class="badge-roadmap">
                                <i class="bi bi-clock-history me-1"></i> En Roadmap
                            </span>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Portal de Proveedores & Compras</h5>
                        <p class="text-muted small flex-grow-1">
                            Requerimientos de adquisición interna, cuadro comparativo de cotizaciones de proveedores, emisión de órdenes de compra (OC) y control de recepción contra orden.
                        </p>
                        <div class="border-top pt-3 mt-2">
                            <ul class="list-unstyled small text-muted mb-0">
                                <li><i class="bi bi-check-circle text-info me-1"></i> Cuadro Comparativo de Ofertas</li>
                                <li><i class="bi bi-check-circle text-info me-1"></i> Órdenes de Compra Automáticas</li>
                                <li><i class="bi bi-check-circle text-info me-1"></i> Imputación a Presupuesto</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Futuro 4: Control de EPPs y Seguridad Ocupacional (SST) -->
                <div class="col-md-6 col-lg-4">
                    <div class="card module-card future-module p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-warning-subtle text-warning">
                                <i class="bi bi-shield-shaded"></i>
                            </div>
                            <span class="badge-roadmap">
                                <i class="bi bi-clock-history me-1"></i> En Roadmap
                            </span>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Control de EPPs & Seguridad SST</h5>
                        <p class="text-muted small flex-grow-1">
                            Matriz de asignación de Equipos de Protección Personal por puesto, registro de actas de entrega firmadas por el colaborador y alertas por vencimiento de ciclo de vida.
                        </p>
                        <div class="border-top pt-3 mt-2">
                            <ul class="list-unstyled small text-muted mb-0">
                                <li><i class="bi bi-check-circle text-warning me-1"></i> Matriz EPP por Especialidad</li>
                                <li><i class="bi bi-check-circle text-warning me-1"></i> Firmas Digitales de Entrega</li>
                                <li><i class="bi bi-check-circle text-warning me-1"></i> Control de Vida Útil / Recambio</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Futuro 5: App Móvil Offline para Cuadrillas -->
                <div class="col-md-6 col-lg-4">
                    <div class="card module-card future-module p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-danger-subtle text-danger">
                                <i class="bi bi-phone-fill"></i>
                            </div>
                            <span class="badge-roadmap">
                                <i class="bi bi-clock-history me-1"></i> En Roadmap
                            </span>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">App Móvil Offline para Frentes</h5>
                        <p class="text-muted small flex-grow-1">
                            Aplicación para dispositivos móviles y tablets con almacenamiento local para zonas sin señal, lectura directa de tags NFC y códigos de barras, y sincronización en la nube.
                        </p>
                        <div class="border-top pt-3 mt-2">
                            <ul class="list-unstyled small text-muted mb-0">
                                <li><i class="bi bi-check-circle text-danger me-1"></i> Operatividad 100% Offline</li>
                                <li><i class="bi bi-check-circle text-danger me-1"></i> Escáner Barcode & NFC</li>
                                <li><i class="bi bi-check-circle text-danger me-1"></i> Sincronización Automática</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Futuro 6: Business Intelligence & Analítica Predictiva -->
                <div class="col-md-6 col-lg-4">
                    <div class="card module-card future-module p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-box bg-purple-subtle text-dark" style="background-color: #ede9fe; color: #6d28d9;">
                                <i class="bi bi-pie-chart-fill"></i>
                            </div>
                            <span class="badge-roadmap">
                                <i class="bi bi-clock-history me-1"></i> En Roadmap
                            </span>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Analítica & KPIs Predictivos</h5>
                        <p class="text-muted small flex-grow-1">
                            Modelos analíticos de proyección de demanda de materiales, cálculo de rotación de inventarios PEPS, costos ocultos por tiempos muertos y tableros interactivos para gerencia.
                        </p>
                        <div class="border-top pt-3 mt-2">
                            <ul class="list-unstyled small text-muted mb-0">
                                <li><i class="bi bi-check-circle text-primary me-1"></i> Pronóstico de Reabastecimiento</li>
                                <li><i class="bi bi-check-circle text-primary me-1"></i> Costeo por Frente de Obra</li>
                                <li><i class="bi bi-check-circle text-primary me-1"></i> Exportación Dinámica BI</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Quick Access Card (Clean & Seguro) -->
        <div class="card border-0 shadow-sm text-center p-4 bg-white" style="border-radius: 1rem;">
            <div class="card-body">
                <h5 class="fw-bold text-dark mb-2">Acceso a la Plataforma Corporativa</h5>
                <p class="text-muted small mb-3">
                    Ingrese con sus credenciales autorizadas asignadas por el Administrador para acceder a la consola operativa:
                </p>
                <div class="d-inline-flex flex-wrap gap-3 bg-light border p-2 px-3 rounded-pill align-items-center mb-3">
                    <span class="small text-muted"><i class="bi bi-shield-lock-fill text-success me-1"></i> Autenticación Cifrada (Bcrypt)</span>
                    <span class="text-muted">|</span>
                    <span class="small text-muted"><i class="bi bi-clock-history text-primary me-1"></i> Hora Oficial Lima (UTC-5)</span>
                </div>
                <div>
                    <a href="{{ route('login') }}" class="btn btn-primary px-4 fw-semibold shadow-sm" style="background-color: var(--lp-accent); border-color: var(--lp-accent);">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión en el Portal
                    </a>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-4 text-center text-muted small mt-auto">
        <div class="container">
            <p class="mb-1"><strong>{{ $empresaGlobal->sistema_nombre ?: 'LogisticPCS' }}</strong> &copy; {{ date('Y') }} — Plataforma de Gestión Logística, Control de Activos & Operaciones</p>
            <p class="mb-0 text-secondary">Todos los derechos reservados. Edición Corporativa.</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
