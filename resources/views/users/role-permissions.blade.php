@extends('layouts.admin')

@section('title', 'Permisos del Rol: ' . $role->name)
@section('page_title', 'Gestión de Permisos & Alcances: ' . $role->name)

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none fw-semibold text-primary">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index', ['tab' => 'roles']) }}" class="text-decoration-none fw-semibold text-primary">Usuarios & Roles</a></li>
            <li class="breadcrumb-item active fw-medium" aria-current="page">Permisos de {{ $role->name }}</li>
        </ol>
    </nav>
@endsection

@section('page_actions')
    <a href="{{ route('users.index', ['tab' => 'roles']) }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Volver a Roles
    </a>
@endsection

@section('content')

    @include('layouts.partials.alerts')

    @php
        $permPercent = round(($activePermsCount / max(1, $totalPermsAvailable)) * 100);
    @endphp

    <!-- Tarjeta de Resumen y Estado del Rol -->
    <div class="admin-card p-4 mb-4 border shadow-sm">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge px-3 py-2 text-white fw-bold text-uppercase fs-6" style="background-color: {{ $roleColor }}; border-radius: 8px;">
                        <i class="bi bi-shield-lock me-1"></i> {{ $role->name }}
                    </span>
                    <span class="badge bg-body-secondary text-body border px-2 py-1">
                        <i class="bi bi-people me-1"></i> {{ $userCount }} {{ $userCount === 1 ? 'usuario asignado' : 'usuarios asignados' }}
                    </span>
                    @if($role->name === 'ADMINISTRADOR')
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fw-bold">
                            <i class="bi bi-patch-check-fill me-1"></i> Acceso Maestro Total
                        </span>
                    @endif
                </div>
                <p class="text-muted mb-0 small" style="line-height: 1.5;">
                    {{ $roleDescription }}
                </p>
            </div>
            <div class="col-lg-4 border-start-lg ps-lg-4">
                <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                    <span class="fw-semibold">Cobertura de Permisos:</span>
                    <span class="fw-bold text-heading" id="statsPermsCountDisplay">{{ $activePermsCount }} de {{ $totalPermsAvailable }} ({{ $permPercent }}%)</span>
                </div>
                <div class="progress mb-2" style="height: 10px; border-radius: 6px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="statsProgressBar" role="progressbar" style="width: {{ $permPercent }}%; background-color: {{ $roleColor }};" aria-valuenow="{{ $permPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <span class="text-muted" style="font-size: 0.78rem;">
                    <i class="bi bi-info-circle me-1"></i> Active o desactive los alcances que este perfil tendrá en las vistas y rutas.
                </span>
            </div>
        </div>
    </div>

    @if($role->name === 'ADMINISTRADOR')
        <div class="alert alert-info border-0 mb-4 d-flex align-items-center gap-3 p-3 rounded-3 shadow-sm">
            <i class="bi bi-shield-fill-check fs-2 text-primary"></i>
            <div>
                <strong class="d-block text-heading mb-1">Acceso Maestro Irrevocable:</strong>
                <p class="mb-0 small text-muted">
                    Por diseño institucional y seguridad, el rol <code>ADMINISTRADOR</code> posee todos los permisos del sistema sin excepción. La desactivación selectiva está reservada para roles operativos como <code>LOGISTICO</code>, <code>SUPERVISOR</code>, <code>TECNICO</code> y <code>AUDITOR</code>.
                </p>
            </div>
        </div>
    @endif

    <form action="{{ route('users.roles.permissions.update', $role) }}" method="POST" id="formRolePermissions">
        @csrf
        @method('PUT')

        <!-- Barra de Herramientas Rápida: Filtro en vivo & Acciones Globales -->
        <div class="admin-card p-3 mb-4 border shadow-sm">
            <div class="row g-2 align-items-center justify-content-between">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-body text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="filterPermsInput" class="form-control" placeholder="Buscar permiso por nombre o clave..." autocomplete="off">
                    </div>
                </div>
                <div class="col-md-7 col-lg-8 d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
                    @if($role->name !== 'ADMINISTRADOR')
                        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="toggleAllPermissions(true)">
                            <i class="bi bi-check-all me-1"></i> Marcar Todo el Sistema
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold" onclick="toggleAllPermissions(false)">
                            <i class="bi bi-x-lg me-1"></i> Desmarcar Todo
                        </button>
                        <span class="border-end mx-1 d-none d-md-inline" style="height: 20px;"></span>
                    @endif
                    <button type="submit" class="btn btn-sm btn-primary fw-bold px-3 shadow-sm" {{ $role->name === 'ADMINISTRADOR' ? 'disabled' : '' }}>
                        <i class="bi bi-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>

        <!-- Módulos del Sistema y Permisos Granulares -->
        <div class="row g-4 mb-4">
            @php $moduleIndex = 0; @endphp
            @foreach($permissionsGrouped as $moduloName => $modulePerms)
                @php
                    $moduleIndex++;
                    $modContainerId = "module_group_{$moduleIndex}";
                    $assignedInMod = 0;
                    foreach(array_keys($modulePerms) as $pk) {
                        if ($role->permissions->contains('name', $pk)) { $assignedInMod++; }
                    }
                    $totalInMod = count($modulePerms);
                @endphp

                <div class="col-12 perm-module-container" id="{{ $modContainerId }}" data-module-name="{{ strtolower($moduloName) }}">
                    <div class="admin-card border shadow-sm overflow-hidden">
                        <!-- Cabecera del Módulo -->
                        <div class="card-header bg-body-tertiary px-4 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary text-white p-2 rounded-3">
                                    <i class="bi bi-folder2-open fs-6"></i>
                                </span>
                                <div>
                                    <h5 class="fw-bold mb-0 text-heading fs-6">{{ $moduloName }}</h5>
                                    <span class="text-muted small">
                                        <span class="mod-counter-display fw-semibold text-primary">{{ $assignedInMod }}</span> de {{ $totalInMod }} permisos activos
                                    </span>
                                </div>
                            </div>

                            @if($role->name !== 'ADMINISTRADOR')
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary py-1 px-2" style="font-size: 0.76rem;" onclick="toggleModuleScope('{{ $modContainerId }}', true)">
                                        <i class="bi bi-check-circle me-1"></i> Activar Módulo
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary py-1 px-2" style="font-size: 0.76rem;" onclick="toggleModuleScope('{{ $modContainerId }}', false)">
                                        <i class="bi bi-dash-circle me-1"></i> Desactivar Módulo
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Lista de Permisos del Módulo en Grid -->
                        <div class="card-body p-4 bg-body">
                            <div class="row g-3">
                                @foreach($modulePerms as $permKey => $permDesc)
                                    @php
                                        $hasPerm = $role->permissions->contains('name', $permKey);
                                        $chkId = 'perm_' . str_replace('.', '_', $permKey);
                                    @endphp
                                    <div class="col-md-6 col-lg-4 perm-item-card" data-perm-key="{{ strtolower($permKey) }}" data-perm-desc="{{ strtolower($permDesc) }}">
                                        <div class="p-3 border rounded-3 h-100 bg-body-tertiary bg-opacity-50 transition-card" style="cursor: pointer;" onclick="toggleCardCheck(event, '{{ $chkId }}')">
                                            <div class="form-check form-switch mb-2 d-flex justify-content-between align-items-center ps-0">
                                                <label class="form-check-label small fw-bold text-heading mb-0" for="{{ $chkId }}" style="cursor: pointer;">
                                                    {{ $permDesc }}
                                                </label>
                                                <input class="form-check-input ms-2 perm-checkbox" type="checkbox" role="switch" name="permissions[]" value="{{ $permKey }}" id="{{ $chkId }}" {{ $hasPerm ? 'checked' : '' }} {{ $role->name === 'ADMINISTRADOR' ? 'disabled' : '' }}>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-secondary border-opacity-10">
                                                <code class="text-primary-emphasis bg-primary-subtle px-2 py-0.5 rounded small" style="font-size: 0.72rem;">{{ $permKey }}</code>
                                                <span class="badge bg-body-secondary text-muted" style="font-size: 0.65rem;">Alcance Operativo</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Barra Inferior de Acción y Guardado -->
        <div class="admin-card p-3 border shadow-sm sticky-bottom bg-body d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-success fs-5"></i>
                <span class="small fw-semibold text-heading">
                    Permisos a guardar para <span class="badge text-white fw-bold" style="background-color: {{ $roleColor }};">{{ $role->name }}</span>:
                    <strong id="footerActivePermsCount" class="text-primary fs-6 ms-1">{{ $activePermsCount }}</strong> de {{ $totalPermsAvailable }}
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('users.index', ['tab' => 'roles']) }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
                    Cancelar
                </a>
                @if($role->name !== 'ADMINISTRADOR')
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm">
                        <i class="bi bi-save me-1"></i> Guardar Permisos
                    </button>
                @endif
            </div>
        </div>
    </form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const totalAvailable = {{ $totalPermsAvailable }};
    const roleColor = '{{ $roleColor }}';

    function recalculateCounters() {
        const allCheckboxes = document.querySelectorAll('.perm-checkbox');
        let totalChecked = 0;

        allCheckboxes.forEach(cb => {
            if (cb.checked) totalChecked++;
        });

        // Actualizar barra de progreso superior
        const percent = Math.round((totalChecked / Math.max(1, totalAvailable)) * 100);
        const statsDisplay = document.getElementById('statsPermsCountDisplay');
        const progressBar = document.getElementById('statsProgressBar');
        const footerCount = document.getElementById('footerActivePermsCount');

        if (statsDisplay) {
            statsDisplay.textContent = `${totalChecked} de ${totalAvailable} (${percent}%)`;
        }
        if (progressBar) {
            progressBar.style.width = `${percent}%`;
            progressBar.setAttribute('aria-valuenow', percent);
        }
        if (footerCount) {
            footerCount.textContent = totalChecked;
        }

        // Actualizar contadores por módulo
        document.querySelectorAll('.perm-module-container').forEach(mod => {
            const modCheckboxes = mod.querySelectorAll('.perm-checkbox');
            let modChecked = 0;
            modCheckboxes.forEach(cb => {
                if (cb.checked) modChecked++;
            });
            const counterDisplay = mod.querySelector('.mod-counter-display');
            if (counterDisplay) {
                counterDisplay.textContent = modChecked;
            }
        });
    }

    // Escuchar cambios en los switches individuales
    document.querySelectorAll('.perm-checkbox').forEach(cb => {
        cb.addEventListener('change', recalculateCounters);
    });

    // Filtro de búsqueda en tiempo real
    const filterInput = document.getElementById('filterPermsInput');
    if (filterInput) {
        filterInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.perm-item-card');

            items.forEach(card => {
                const key = card.getAttribute('data-perm-key') || '';
                const desc = card.getAttribute('data-perm-desc') || '';

                if (!query || key.includes(query) || desc.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });

            // Ocultar módulos completos si ninguno de sus ítems coincide
            document.querySelectorAll('.perm-module-container').forEach(mod => {
                const visibleItems = mod.querySelectorAll('.perm-item-card:not([style*="display: none"])');
                if (visibleItems.length === 0 && query !== '') {
                    mod.style.display = 'none';
                } else {
                    mod.style.display = '';
                }
            });
        });
    }

    window.toggleAllPermissions = function (state) {
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            if (!cb.disabled) {
                cb.checked = state;
            }
        });
        recalculateCounters();
    };

    window.toggleModuleScope = function (moduleId, state) {
        const mod = document.getElementById(moduleId);
        if (!mod) return;
        mod.querySelectorAll('.perm-checkbox').forEach(cb => {
            if (!cb.disabled) {
                cb.checked = state;
            }
        });
        recalculateCounters();
    };

    window.toggleCardCheck = function (event, checkboxId) {
        // Evitar doble toggle si se hace click directamente en el input switch
        if (event.target.tagName.toLowerCase() === 'input') {
            return;
        }
        const cb = document.getElementById(checkboxId);
        if (cb && !cb.disabled) {
            cb.checked = !cb.checked;
            recalculateCounters();
        }
    };
});
</script>

<style>
.transition-card {
    transition: all 0.2s ease-in-out;
}
.transition-card:hover {
    background-color: rgba(var(--admin-primary-rgb), 0.05) !important;
    border-color: rgba(var(--admin-primary-rgb), 0.3) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}
</style>
@endpush
