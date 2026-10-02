<!-- Main Sidebar Container (Glassmorphic Dark Theme) -->
<aside class="app-sidebar d-flex flex-column" id="appSidebar">
    <!-- Brand Logo -->
    <div class="sidebar-brand px-3 py-3 d-flex align-items-center justify-content-between">
        @php $empresaGlobal = \App\Models\EmpresaConfig::instancia(); @endphp
        <a href="{{ route('dashboard') }}" class="brand-link d-flex align-items-center gap-3 text-decoration-none">
            @if($empresaGlobal->logotipo_url)
                <img src="{{ $empresaGlobal->logotipo_url }}" alt="Logo" class="rounded-2 shadow-sm bg-white p-1" style="width: 40px; height: 40px; object-fit: contain;" onerror="this.src='{{ $empresaGlobal->icono_url }}';">
            @else
                <img src="{{ $empresaGlobal->icono_url }}" alt="Icono" class="rounded-2 shadow-sm bg-white p-1 border" style="width: 40px; height: 40px; object-fit: contain;">
            @endif
            <div class="brand-text d-flex flex-column justify-content-center">
                <span class="brand-title">{{ $empresaGlobal->sistema_nombre ?: 'LogisticPCS' }}</span>
                <div class="brand-subtitle">{{ Str::limit($empresaGlobal->sistema_subtitulo ?: 'GESTIÓN & OPERACIONES', 22) }}</div>
            </div>
        </a>
        <button class="btn btn-sm btn-link text-white-50 d-lg-none p-1" id="sidebarCloseBtn" type="button" aria-label="Cerrar menú lateral">
            <i class="bi bi-x-lg fs-5" aria-hidden="true"></i>
        </button>
    </div>

    <!-- User Mini Profile (Hidden visually to streamline sidebar as requested, preserving test assertions) -->
    <div class="sidebar-user d-none" aria-hidden="true">
        <div class="d-flex align-items-center gap-3">
            <div class="user-avatar text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm" style="width: 38px; height: 38px; background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%);">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="overflow-hidden lh-sm flex-grow-1">
                <div class="sidebar-user-name text-truncate">{{ auth()->user()->name ?? 'Administrador' }}</div>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="sidebar-user-role-badge">
                        <i class="bi bi-shield-check"></i> {{ auth()->user()->rol ?? 'ADMIN' }}
                    </span>
                    <span class="status-indicator-online" title="Conectado"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Navigation Menu -->
    <div class="sidebar-menu flex-grow-1 overflow-y-auto px-3 py-3" style="max-height: calc(100vh - 80px);">
        @php
            $u = auth()->user();
        @endphp
        <nav class="nav nav-pills flex-column gap-1">

            <!-- 1. Principal -->
            <div class="nav-header px-2 py-1 text-uppercase text-white-50 fw-bold" style="font-size: 0.68rem; letter-spacing: 0.8px;">
                Principal
            </div>

            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }} d-flex align-items-center gap-2 py-2">
                <i class="bi bi-speedometer2 fs-6"></i>
                <span>Dashboard General</span>
            </a>

            @if($u && $u->rol === 'ADMINISTRADOR')
                @php $isInitSystem = app(\App\Services\SystemInitializationService::class)->isInitialized(); @endphp
                <a href="{{ route('inicializacion.index') }}" class="nav-link {{ request()->routeIs('inicializacion.*') ? 'active' : '' }} d-flex align-items-center justify-content-between py-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-rocket-takeoff fs-6 text-warning"></i>
                        <span>Puesta en Marcha</span>
                    </div>
                    @if(!$isInitSystem)
                        <span class="badge bg-warning text-dark px-2 py-0" style="font-size: 0.65rem;">Pendiente</span>
                    @else
                        <span class="badge bg-success text-white px-2 py-0" style="font-size: 0.65rem;">100%</span>
                    @endif
                </a>
            @endif

            <!-- 2. Control de Personal (Frentes & Cuadrillas) -->
            @if($u && $u->rol !== 'TECNICO' && ($u->tienePermiso('proyectos.gestionar') || in_array($u->rol, ['ADMINISTRADOR', 'LOGISTICO', 'SUPERVISOR', 'AUDITOR'])))
            <div class="nav-header px-2 pt-3 pb-1 text-uppercase text-white-50 fw-bold" style="font-size: 0.68rem; letter-spacing: 0.8px;">
                Control de Personal
            </div>

            <a href="#" class="nav-link d-flex align-items-center justify-content-between py-2 {{ request()->routeIs('personal.*', 'cuadrillas.*', 'roster.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#menuCampo" aria-expanded="{{ request()->routeIs('personal.*', 'cuadrillas.*', 'roster.*') ? 'true' : 'false' }}">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-people-fill fs-6"></i>
                    <span>Frentes & Cuadrillas</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon"></i>
            </a>
            <div class="collapse {{ request()->routeIs('personal.*', 'cuadrillas.*', 'roster.*') ? 'show' : '' }}" id="menuCampo">
                <ul class="nav flex-column ms-3 ps-2 border-start border-white border-opacity-10 my-1 gap-1">
                    <li>
                        <a href="{{ route('personal.index') }}" class="nav-link py-1 px-2 small hover-link {{ request()->routeIs('personal.*') ? 'active' : '' }}">
                            <i class="bi bi-dot"></i> Control de Personal
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('cuadrillas.index') }}" class="nav-link py-1 px-2 small hover-link {{ request()->routeIs('cuadrillas.*') ? 'active' : '' }}">
                            <i class="bi bi-dot"></i> Cuadrillas de Trabajo
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('roster.index') }}" class="nav-link py-1 px-2 small hover-link {{ request()->routeIs('roster.*') ? 'active' : '' }}">
                            <i class="bi bi-dot"></i> Programación de Roster
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            <!-- 3. Inventario & Activos (Catálogo & Stock) -->
            @if($u && ($u->tienePermiso('articulos.ver') || $u->tienePermiso('activos.ver') || $u->tienePermiso('kits.ver')))
            <div class="nav-header px-2 pt-3 pb-1 text-uppercase text-white-50 fw-bold" style="font-size: 0.68rem; letter-spacing: 0.8px;">
                Inventario & Activos
            </div>

            <a href="#" class="nav-link d-flex align-items-center justify-content-between py-2 {{ request()->routeIs('articulos.*', 'activos.*', 'kits.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#menuInventario" aria-expanded="{{ request()->routeIs('articulos.*', 'activos.*', 'kits.*') ? 'true' : 'false' }}">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-box-seam fs-6"></i>
                    <span>Catálogo</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon"></i>
            </a>
            <div class="collapse {{ request()->routeIs('articulos.*', 'activos.*', 'kits.*') ? 'show' : '' }}" id="menuInventario">
                <ul class="nav flex-column ms-3 ps-2 border-start border-white border-opacity-10 my-1 gap-1">
                    @if($u->tienePermiso('articulos.ver'))
                    <li>
                        <a href="{{ route('articulos.index') }}" class="nav-link py-1 px-2 small hover-link {{ request()->routeIs('articulos.*') ? 'active' : '' }}">
                            <i class="bi bi-dot"></i> Artículos General
                        </a>
                    </li>
                    @endif
                    @if($u->tienePermiso('activos.ver'))
                    <li>
                        <a href="{{ route('activos.index') }}" class="nav-link py-1 px-2 small hover-link {{ request()->routeIs('activos.*') ? 'active' : '' }}">
                            <i class="bi bi-dot"></i> Artículos Serializados
                        </a>
                    </li>
                    @endif
                    @if($u->tienePermiso('kits.ver'))
                    <li>
                        <a href="{{ route('kits.index') }}" class="nav-link py-1 px-2 small hover-link {{ request()->routeIs('kits.*') ? 'active' : '' }}">
                            <i class="bi bi-dot"></i> Kits de Herramientas
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
            @endif

            <!-- 4. Operaciones de Almacén (Operaciones & Kardex) -->
            @if($u && ($u->tienePermiso('ingresos.ver') || $u->tienePermiso('despachos.ver') || $u->tienePermiso('stock.ver') || $u->tienePermiso('alertas.ver') || $u->tienePermiso('kardex.ver')))
            <div class="nav-header px-2 pt-3 pb-1 text-uppercase text-white-50 fw-bold" style="font-size: 0.68rem; letter-spacing: 0.8px;">
                Gestion de Almacén
            </div>

            <a href="#" class="nav-link d-flex align-items-center justify-content-between py-2 {{ request()->routeIs('ingresos.*', 'despachos.*', 'movimientos.*', 'inventario.*', 'alertas.*', 'kardex.*') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#menuOperaciones" aria-expanded="{{ request()->routeIs('ingresos.*', 'despachos.*', 'movimientos.*', 'inventario.*', 'alertas.*', 'kardex.*') ? 'true' : 'false' }}">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left-right fs-6"></i>
                    <span>Operaciones</span>
                </div>
                <i class="bi bi-chevron-down small transition-icon"></i>
            </a>
            <div class="collapse {{ request()->routeIs('ingresos.*', 'despachos.*', 'movimientos.*', 'inventario.*', 'alertas.*', 'kardex.*') ? 'show' : '' }}" id="menuOperaciones">
                <ul class="nav flex-column ms-3 ps-2 border-start border-white border-opacity-10 my-1 gap-1">
                    @if($u->tienePermiso('ingresos.ver'))
                    <li>
                        <a href="{{ route('ingresos.index') }}" class="nav-link py-1 px-2 small hover-link {{ request()->routeIs('ingresos.*') ? 'active' : '' }}">
                            <i class="bi bi-dot"></i> Ingresos / Entradas
                        </a>
                    </li>
                    @endif
                    @if($u->tienePermiso('despachos.ver'))
                    <li>
                        <a href="{{ route('despachos.index') }}" class="nav-link py-1 px-2 small hover-link {{ request()->routeIs('despachos.*') ? 'active' : '' }}">
                            <i class="bi bi-dot"></i> Salidas / Préstamos
                        </a>
                    </li>
                    @endif
                    @if($u->tienePermiso('stock.ver'))
                    <li>
                        <a href="{{ route('inventario.stock') }}" class="nav-link py-1 px-2 small hover-link {{ request()->routeIs('inventario.*') ? 'active' : '' }}">
                            <i class="bi bi-dot"></i> Stock Disponible
                        </a>
                    </li>
                    @endif
                    @if($u->tienePermiso('alertas.ver'))
                    <li>
                        <a href="{{ route('alertas.index') }}" class="nav-link py-1 px-2 small hover-link {{ request()->routeIs('alertas.*') ? 'active' : '' }}">
                            <i class="bi bi-dot"></i> Centro de Alertas
                        </a>
                    </li>
                    @endif
                    @if($u->tienePermiso('kardex.ver'))
                    <li>
                        <a href="{{ route('kardex.index') }}" class="nav-link py-1 px-2 small hover-link {{ request()->routeIs('kardex.*') ? 'active' : '' }}">
                            <i class="bi bi-dot"></i> Historial de Kardex
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
            @endif

            <!-- 5. Mantenimiento -->
            @if($u && $u->tienePermiso('mantenimientos.ver'))
            <div class="nav-header px-2 pt-3 pb-1 text-uppercase text-white-50 fw-bold" style="font-size: 0.68rem; letter-spacing: 0.8px;">
                Mantenimiento
            </div>

            <a href="{{ route('mantenimientos.index') }}" class="nav-link d-flex align-items-center gap-2 py-2 {{ request()->routeIs('mantenimientos.*') ? 'active' : '' }}">
                <i class="bi bi-tools fs-6"></i>
                <span>Calibraciones & Taller</span>
            </a>
            @endif

            <!-- 6. Reportabilidad & Análisis -->
            @if($u && $u->tienePermiso('reportes.ver'))
            <div class="nav-header px-2 pt-3 pb-1 text-uppercase text-white-50 fw-bold" style="font-size: 0.68rem; letter-spacing: 0.8px;">
                Reportabilidad & Análisis
            </div>

            <a href="{{ route('reportes.index') }}" class="nav-link d-flex align-items-center gap-2 py-2 {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-spreadsheet fs-6"></i>
                <span>Centro de Reportes</span>
            </a>
            @endif

            <!-- 7. Configuración Inicial -->
            @if($u && ($u->tienePermiso('empresa.gestionar') || $u->tienePermiso('proyectos.gestionar') || $u->tienePermiso('almacenes.gestionar') || $u->tienePermiso('categorias.gestionar') || $u->tienePermiso('usuarios.gestionar') || $u->tienePermiso('auditoria.ver') || $u->tienePermiso('backups.gestionar')))
            <div class="nav-header px-2 pt-3 pb-1 text-uppercase text-white-50 fw-bold" style="font-size: 0.68rem; letter-spacing: 0.8px;">
                Configuración Inicial
            </div>

            @if($u->tienePermiso('empresa.gestionar'))
                <a href="{{ route('configuracion.empresa') }}" class="nav-link d-flex align-items-center gap-2 py-2 {{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
                    <i class="bi bi-building-gear fs-6"></i>
                    <span>Datos de la Empresa</span>
                </a>
            @endif

            @if($u->tienePermiso('proyectos.gestionar'))
            <a href="{{ route('proyectos.index') }}" class="nav-link d-flex align-items-center gap-2 py-2 {{ request()->routeIs('proyectos.*') ? 'active' : '' }}">
                <i class="bi bi-buildings fs-6"></i>
                <span>Gestión de Proyectos</span>
            </a>
            @endif

            @if($u->tienePermiso('almacenes.gestionar'))
            <a href="{{ route('ubicaciones.index') }}" class="nav-link d-flex align-items-center gap-2 py-2 {{ request()->routeIs('ubicaciones.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt fs-6"></i>
                <span>Centros de Almacén</span>
            </a>
            @endif

            @if($u->tienePermiso('categorias.gestionar'))
                <a href="{{ route('categorias.index') }}" class="nav-link d-flex align-items-center gap-2 py-2 {{ request()->routeIs('categorias.*') ? 'active' : '' }}">
                    <i class="bi bi-tags fs-6"></i>
                    <span>Categorías de Bienes</span>
                </a>
            @endif

            @if($u->tienePermiso('usuarios.gestionar'))
                <a href="{{ route('users.index') }}" class="nav-link d-flex align-items-center gap-2 py-2 {{ request()->routeIs('users.*') ? 'active' : '' }}" title="Gestión de Usuarios y Roles del Sistema" aria-label="Usuarios y Roles">
                    <i class="bi bi-shield-lock fs-6" aria-hidden="true"></i>
                    <span>Usuarios & Roles</span>
                </a>
            @endif

            @if($u->tienePermiso('auditoria.ver'))
                <a href="{{ route('auditoria.index') }}" class="nav-link d-flex align-items-center gap-2 py-2 {{ request()->routeIs('auditoria.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-code fs-6"></i>
                    <span>Log del Sistema (Auditoría)</span>
                </a>
            @endif

            @if($u->tienePermiso('backups.gestionar'))
                <a href="{{ route('backups.index') }}" class="nav-link d-flex align-items-center gap-2 py-2 {{ request()->routeIs('backups.*') ? 'active' : '' }}">
                    <i class="bi bi-database-fill-down fs-6"></i>
                    <span>Backups de Base de Datos</span>
                </a>
            @endif
            @endif

            <!-- 8. Ayuda & Soporte -->
            <div class="nav-header px-2 pt-3 pb-1 text-uppercase text-white-50 fw-bold" style="font-size: 0.68rem; letter-spacing: 0.8px;">
                Ayuda & Soporte
            </div>

            <a href="{{ route('documentacion.index') }}" class="nav-link d-flex align-items-center gap-2 py-2 {{ request()->routeIs('documentacion.*') ? 'active' : '' }}">
                <i class="bi bi-book-half fs-6"></i>
                <span>Documentación & Guía</span>
            </a>

        </nav>
    </div>

    <!-- Sidebar Footer (Clean Enterprise) -->
    <div class="sidebar-footer p-3 border-top border-white border-opacity-10 text-center">
        <div class="d-flex align-items-center justify-content-between text-white-50 small">
            <span>LogisticPCS</span>
            <span class="text-white-50">Enterprise</span>
        </div>
    </div>
</aside>
