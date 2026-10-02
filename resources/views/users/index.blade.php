@extends('layouts.admin')

@section('title', 'Usuarios & Roles')
@section('page_title', 'Gestión de Usuarios & Roles')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item text-muted">Configuración Inicial</li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Usuarios & Roles</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm px-3 fw-bold shadow" aria-label="Registrar nuevo usuario">
        <i class="bi bi-person-plus me-1" aria-hidden="true"></i> Nuevo Usuario
    </a>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    <!-- Mini KPIs Header -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Total Cuentas</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-heading">{{ $stats['total'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Usuarios Activos</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success">{{ $stats['activos'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Cuentas Inactivas</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-person-slash"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-muted">{{ $stats['inactivos'] }}</h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-uppercase text-muted">Vinculados a Personal</span>
                    <div class="gradient-icon-box" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); width: 38px; height: 38px; font-size: 1.1rem;">
                        <i class="bi bi-link-45deg"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-primary">{{ $stats['con_personal'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" id="usersTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link px-4 py-2 fw-bold rounded-pill {{ $tab === 'cuentas' ? 'active shadow-sm' : 'bg-body-secondary text-body' }}" 
               href="{{ route('users.index', array_merge(request()->except(['page']), ['tab' => 'cuentas'])) }}"
               role="tab" aria-selected="{{ $tab === 'cuentas' ? 'true' : 'false' }}">
                <i class="bi bi-people me-1"></i> Cuentas de Usuario
                <span class="badge {{ $tab === 'cuentas' ? 'bg-white text-primary' : 'bg-secondary text-white' }} rounded-pill ms-2">{{ $stats['total'] }}</span>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link px-4 py-2 fw-bold rounded-pill {{ $tab === 'roles' ? 'active shadow-sm' : 'bg-body-secondary text-body' }}" 
               href="{{ route('users.index', array_merge(request()->except(['page']), ['tab' => 'roles'])) }}"
               role="tab" aria-selected="{{ $tab === 'roles' ? 'true' : 'false' }}">
                <i class="bi bi-shield-lock-fill me-1"></i> Roles & Gestión de Permisos
                <span class="badge {{ $tab === 'roles' ? 'bg-white text-primary' : 'bg-secondary text-white' }} rounded-pill ms-2">{{ $rolesWithPermissions->count() }} Roles</span>
            </a>
        </li>
    </ul>

    @if($tab === 'cuentas')
        <!-- Search & Filter Bar -->
        <div class="admin-card p-3 mb-4">
            <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="cuentas">
                <div class="col-md-6 col-lg-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-body border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0" placeholder="Buscar por nombre o correo electrónico...">
                    </div>
                </div>
                <div class="col-md-3 col-lg-3">
                    <select name="rol" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Todos los roles --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ $rol === $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-lg-3 d-flex gap-2">
                    <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Todos los estados --</option>
                        <option value="ACTIVO" {{ $estado === 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
                        <option value="INACTIVO" {{ $estado === 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if ($search || $rol || $estado)
                        <a href="{{ route('users.index', ['tab' => 'cuentas']) }}" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Users Table Card -->
        <div class="admin-card overflow-hidden mb-4">
            <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-heading">
                    <i class="bi bi-shield-lock text-primary me-2"></i> Padrón de Usuarios, Roles & Alcance por Proyecto
                </h5>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small fw-bold">
                    {{ $users->total() }} Cuentas
                </span>
            </div>
            <div class="table-responsive" role="region" aria-label="Listado de Usuarios y Roles" tabindex="0">
                <table class="table table-glass align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Usuario / Credencial</th>
                            <th scope="col">Rol & Proyectos a Cargo</th>
                            <th scope="col">Ficha de Personal Vinculada</th>
                            <th scope="col" class="text-center" style="width: 120px;">Estado</th>
                            <th scope="col">Fecha Creación</th>
                            <th scope="col" class="text-end" style="width: 130px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $usr)
                            @php
                                $proyectosUsr = $usr->proyectosAsignados->isNotEmpty()
                                    ? $usr->proyectosAsignados
                                    : ($usr->proyecto ? collect([$usr->proyecto]) : collect());
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar text-white d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm" style="width: 32px; height: 32px; font-size: 0.8rem; background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-primary-gradient) 100%);">
                                            {{ strtoupper(substr($usr->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('users.show', $usr) }}" class="fw-bold text-decoration-none text-heading d-block">
                                                {{ $usr->name }}
                                            </a>
                                            <small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $usr->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($usr->rol === 'ADMINISTRADOR')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-semibold">
                                            <i class="bi bi-shield-fill me-1"></i> ADMINISTRADOR
                                        </span>
                                        <small class="text-success d-block mt-1 fw-semibold" style="font-size: 0.72rem;">
                                            <i class="bi bi-globe2 me-1"></i>Acceso Globalizado (Todas las sucursales)
                                        </small>
                                    @elseif($usr->rol === 'LOGISTICO' || $usr->rol === 'ALMACENERO')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold">
                                            <i class="bi bi-box-seam me-1"></i> LOGÍSTICO
                                        </span>
                                    @elseif($usr->rol === 'SUPERVISOR')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill fw-semibold">
                                            <i class="bi bi-person-badge me-1"></i> SUPERVISOR
                                        </span>
                                    @elseif($usr->rol === 'TECNICO')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill fw-semibold">
                                            <i class="bi bi-wrench-adjustable me-1"></i> TÉCNICO
                                        </span>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                            <i class="bi bi-shield-check me-1"></i>Consultas & Custodia
                                        </small>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill fw-semibold">
                                            <i class="bi bi-eye me-1"></i> AUDITOR
                                        </span>
                                    @endif

                                    @if($usr->rol !== 'ADMINISTRADOR')
                                        @if($proyectosUsr->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @foreach($proyectosUsr as $pItem)
                                                    <span class="badge bg-body-secondary text-body border font-monospace" style="font-size: 0.68rem;" title="{{ $pItem->nombre }}">
                                                        <i class="bi bi-folder-check text-primary me-1"></i>{{ $pItem->codigo }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Sin restricción fija</small>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($usr->personal)
                                        <a href="{{ route('personal.show', $usr->personal) }}" class="text-decoration-none text-primary fw-semibold small d-block">
                                            <i class="bi bi-link-45deg me-1"></i>{{ $usr->personal->nombre_completo }}
                                        </a>
                                        <small class="text-muted" style="font-size: 0.72rem;">DNI: {{ $usr->personal->dni }} • {{ $usr->personal->cargo }}</small>
                                    @else
                                        <span class="text-muted small fst-italic">Sin ficha física vinculada</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($usr->estado === 'ACTIVO')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small fw-semibold">
                                            <i class="bi bi-check-circle me-1"></i> ACTIVO
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small fw-semibold">
                                            INACTIVO
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $usr->created_at?->format('d/m/Y') }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('users.show', $usr) }}" class="btn btn-outline-secondary" title="Ver Detalles" aria-label="Ver detalles de {{ $usr->name }}">
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $usr) }}" class="btn btn-outline-primary" title="Editar" aria-label="Editar usuario {{ $usr->name }}">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </a>
                                        @if(auth()->id() !== $usr->id)
                                            <form action="{{ route('users.destroy', $usr) }}" method="POST" class="d-inline form-delete" data-confirm-text="¿Está seguro de deshabilitar la cuenta del usuario {{ $usr->name }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Dar de baja" aria-label="Dar de baja usuario {{ $usr->name }}">
                                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2 text-muted"></i>
                                    No se encontraron cuentas de usuario que coincidan con los filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="card-footer bg-transparent border-top py-3 px-4 d-flex justify-content-end">
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    @else
        <!-- TAB: ROLES & GESTIÓN DE PERMISOS -->
        <div class="admin-card p-4 mb-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="gradient-icon-box text-primary fs-3" style="width: 46px; height: 46px; background: rgba(2, 132, 199, 0.12); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-shield-fill-check text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-heading">Gestión de Usuarios, Matriz de Roles y Niveles de Acceso</h4>
                    <p class="text-muted mb-0 small">Control de seguridad basado en perfiles para garantizar la segregación de funciones operativas y contables.</p>
                </div>
            </div>

            <!-- Matriz Visual de Niveles de Acceso y Permisos Activos -->
            @php
                $rolesByName = $rolesWithPermissions->keyBy('name');
                $standardRoles = ['ADMINISTRADOR', 'LOGISTICO', 'SUPERVISOR', 'TECNICO', 'AUDITOR'];
            @endphp
            <div class="table-responsive rounded-3 border mb-4 shadow-sm bg-body">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-secondary">
                        <tr class="align-middle">
                            <th style="width: 32%;" class="ps-3 py-3 fw-bold text-heading">Módulo / Alcance del Sistema</th>
                            <th class="text-center py-3">
                                <span class="badge px-3 py-2 text-white fw-bold text-uppercase" style="background-color: #0284c7; border-radius: 6px; letter-spacing: 0.5px; font-size: 0.78rem;">ADMINISTRADOR</span>
                            </th>
                            <th class="text-center py-3">
                                <span class="badge px-3 py-2 text-white fw-bold text-uppercase" style="background-color: #059669; border-radius: 6px; letter-spacing: 0.5px; font-size: 0.78rem;">LOGISTICO</span>
                            </th>
                            <th class="text-center py-3">
                                <span class="badge px-3 py-2 text-white fw-bold text-uppercase" style="background-color: #d97706; border-radius: 6px; letter-spacing: 0.5px; font-size: 0.78rem;">SUPERVISOR</span>
                            </th>
                            <th class="text-center py-3">
                                <span class="badge px-3 py-2 text-white fw-bold text-uppercase" style="background-color: #06b6d4; border-radius: 6px; letter-spacing: 0.5px; font-size: 0.78rem;">TECNICO</span>
                            </th>
                            <th class="text-center py-3 pe-3">
                                <span class="badge px-3 py-2 text-white fw-bold text-uppercase" style="background-color: #475569; border-radius: 6px; letter-spacing: 0.5px; font-size: 0.78rem;">AUDITOR</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($permissionsGrouped as $moduloName => $modulePerms)
                            @php
                                $totalModulePerms = count($modulePerms);
                            @endphp
                            <tr>
                                <td class="ps-3 py-3 fw-bold text-heading">
                                    <i class="bi bi-folder2-open text-primary me-2"></i>{{ $moduloName }}
                                    <div class="text-muted small fw-normal">{{ $totalModulePerms }} {{ $totalModulePerms === 1 ? 'permiso configurable' : 'permisos configurables' }}</div>
                                </td>
                                @foreach($standardRoles as $roleName)
                                    @php
                                        $r = $rolesByName->get($roleName);
                                        $hasCount = 0;
                                        if ($r) {
                                            if ($roleName === 'ADMINISTRADOR') {
                                                $hasCount = $totalModulePerms;
                                            } else {
                                                foreach(array_keys($modulePerms) as $pk) {
                                                    if ($r->hasPermissionTo($pk)) {
                                                        $hasCount++;
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                    <td class="text-center {{ $loop->last ? 'pe-3' : '' }}">
                                        @if($hasCount === $totalModulePerms)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-2 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i>Total ({{ $hasCount }}/{{ $totalModulePerms }})
                                            </span>
                                        @elseif($hasCount === 0)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold px-2 py-1">
                                                <i class="bi bi-x-circle me-1"></i>Sin Acceso
                                            </span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold px-2 py-1">
                                                <i class="bi bi-shield-check me-1"></i>Parcial ({{ $hasCount }}/{{ $totalModulePerms }})
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Apartado: Configuración y Gestión de Permisos por Rol -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1 text-heading">
                        <i class="bi bi-sliders2 text-primary me-2"></i>Personalización de Permisos por Rol
                    </h5>
                    <p class="text-muted small mb-0">Haga clic en un rol para inspeccionar o personalizar su conjunto de permisos activos en el sistema.</p>
                </div>
            </div>

            <div class="row g-3">
                @foreach($rolesWithPermissions as $rItem)
                    @php
                        $roleColor = match($rItem->name) {
                            'ADMINISTRADOR' => '#0284c7',
                            'LOGISTICO' => '#059669',
                            'SUPERVISOR' => '#d97706',
                            'TECNICO' => '#06b6d4',
                            'AUDITOR' => '#475569',
                            default => '#6b7280',
                        };
                        $userCount = $usersPerRole[$rItem->name] ?? 0;
                        $permsCount = $rItem->permissions->count();
                        $totalPermsAvailable = 29;
                        $permPercent = round(($permsCount / max(1, $totalPermsAvailable)) * 100);
                    @endphp
                    <div class="col-md-6 col-xl-4">
                        <div class="admin-card p-3 h-100 border d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge px-3 py-2 text-white fw-bold text-uppercase" style="background-color: {{ $roleColor }}; border-radius: 6px;">
                                        {{ $rItem->name }}
                                    </span>
                                    <span class="badge bg-body-secondary text-body border small">
                                        <i class="bi bi-person me-1"></i>{{ $userCount }} {{ $userCount === 1 ? 'usuario' : 'usuarios' }}
                                    </span>
                                </div>
                                <p class="text-muted small mb-3">
                                    @if($rItem->name === 'ADMINISTRADOR')
                                        Acceso maestro sin restricciones para auditoría, mantenimiento, configuración y supervisión global.
                                    @elseif($rItem->name === 'LOGISTICO')
                                        Control operativo integral de inventario, kardex, recepción, despachos, kits y alta de taller.
                                    @elseif($rItem->name === 'SUPERVISOR')
                                        Supervisión de proyectos, aprobación de vales, envío de activos a taller y reportes de obra.
                                    @elseif($rItem->name === 'TECNICO')
                                        Perfil operativo de campo: consulta de stock, catálogo de equipos y firma receptora de herramientas.
                                    @elseif($rItem->name === 'AUDITOR')
                                        Modo fiscalización: lectura de trazabilidad, métricas, movimientos y generación de reportes.
                                    @endif
                                </p>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                                    <span>Permisos Asignados</span>
                                    <span class="fw-bold text-heading">{{ $permsCount }} de {{ $totalPermsAvailable }} ({{ $permPercent }}%)</span>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $permPercent }}%; background-color: {{ $roleColor }};" aria-valuenow="{{ $permPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <a href="{{ route('users.roles.permissions.edit', $rItem) }}" class="btn btn-sm w-100 fw-bold {{ $rItem->name === 'ADMINISTRADOR' ? 'btn-outline-secondary' : 'btn-primary' }}">
                                    <i class="bi {{ $rItem->name === 'ADMINISTRADOR' ? 'bi-eye' : 'bi-shield-gear' }} me-1"></i>
                                    {{ $rItem->name === 'ADMINISTRADOR' ? 'Ver Permisos (Maestro)' : 'Gestionar Permisos' }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

@endsection
