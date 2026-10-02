<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    /**
     * Display a listing of system users.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $rol = $request->input('rol');
        $estado = $request->input('estado');

        $query = User::with(['personal.proyecto', 'roles', 'proyecto', 'proyectosAsignados'])->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($rol) {
            $query->where('rol', $rol);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::all();
        $rolesWithPermissions = Role::with('permissions')->get();
        $permissionsGrouped = RoleSeeder::getPermissionsGrouped();
        $usersPerRole = User::selectRaw('rol, count(*) as total')->groupBy('rol')->pluck('total', 'rol');
        $tab = $request->input('tab', 'cuentas');

        $stats = [
            'total' => User::count(),
            'activos' => User::where('estado', 'ACTIVO')->count(),
            'inactivos' => User::where('estado', 'INACTIVO')->count(),
            'con_personal' => User::has('personal')->count(),
        ];

        return view('users.index', compact(
            'users',
            'roles',
            'stats',
            'search',
            'rol',
            'estado',
            'rolesWithPermissions',
            'permissionsGrouped',
            'usersPerRole',
            'tab'
        ));
    }

    /**
     * Mostrar la vista dedicada para la gestión y configuración de permisos de un rol.
     */
    public function editRolePermissions(Role $role): View
    {
        abort_unless(auth()->user()?->rol === 'ADMINISTRADOR', 403, 'Acceso denegado: Solo el rol ADMINISTRADOR puede gestionar permisos de roles.');

        $role->load('permissions');
        $permissionsGrouped = RoleSeeder::getPermissionsGrouped();
        $userCount = User::where('rol', $role->name)->count();
        $totalPermsAvailable = 29;
        $activePermsCount = $role->permissions->count();

        $roleColor = match ($role->name) {
            'ADMINISTRADOR' => '#0284c7',
            'LOGISTICO' => '#059669',
            'SUPERVISOR' => '#d97706',
            'TECNICO' => '#06b6d4',
            'AUDITOR' => '#475569',
            default => '#6b7280',
        };

        $roleDescription = match ($role->name) {
            'ADMINISTRADOR' => 'Acceso maestro sin restricciones para auditoría, mantenimiento, configuración y supervisión global.',
            'LOGISTICO' => 'Control operativo integral de inventario, kardex, recepción, despachos, kits y alta técnica de taller.',
            'SUPERVISOR' => 'Supervisión de proyectos asignados, aprobación de vales, envío de activos a taller y reportes de obra.',
            'TECNICO' => 'Perfil operativo de campo: consulta de stock, catálogo de equipos y firma receptora de herramientas.',
            'AUDITOR' => 'Modo fiscalización: lectura de trazabilidad, métricas, movimientos y generación de reportes ejecutivos.',
            default => 'Configuración de permisos y alcance del rol.',
        };

        return view('users.role-permissions', compact(
            'role',
            'permissionsGrouped',
            'userCount',
            'totalPermsAvailable',
            'activePermsCount',
            'roleColor',
            'roleDescription'
        ));
    }

    /**
     * Actualizar los permisos asignados a un rol específico.
     */
    public function updateRolePermissions(Request $request, Role $role): RedirectResponse
    {
        abort_unless(auth()->user()?->rol === 'ADMINISTRADOR', 403, 'Acceso denegado: Solo el rol ADMINISTRADOR puede gestionar permisos de roles.');

        $permissions = $request->input('permissions', []);

        if ($role->name === 'ADMINISTRADOR') {
            $role->syncPermissions(Permission::all());

            return redirect()
                ->route('users.index', ['tab' => 'roles'])
                ->with('status', 'El rol ADMINISTRADOR posee acceso total irrevocable por diseño de seguridad.');
        }

        $role->syncPermissions($permissions);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $activeCount = count($permissions);

        return redirect()
            ->route('users.index', ['tab' => 'roles'])
            ->with('status', "Permisos del rol {$role->name} actualizados exitosamente ({$activeCount} permisos activos).");
    }

    /**
     * Show the form for creating a new system user.
     */
    public function create(): View
    {
        $personalSinUsuario = Personal::whereNull('user_id')
            ->where('estado', 'ACTIVO')
            ->orderBy('nombres')
            ->get();
        $roles = Role::all();
        $proyectos = Proyecto::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('users.create', compact('personalSinUsuario', 'roles', 'proyectos'));
    }

    /**
     * Store a newly created system user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $personalId = $data['personal_id'] ?? null;
        $proyectosIds = collect($data['proyectos_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if (! empty($data['proyecto_id']) && ! $proyectosIds->contains((int) $data['proyecto_id'])) {
            $proyectosIds->prepend((int) $data['proyecto_id']);
        }

        $primaryProyectoId = $data['rol'] !== 'ADMINISTRADOR'
            ? ($data['proyecto_id'] ?? $proyectosIds->first())
            : null;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'rol' => $data['rol'],
            'estado' => $data['estado'],
            'proyecto_id' => $primaryProyectoId,
        ]);

        if ($data['rol'] !== 'ADMINISTRADOR' && $proyectosIds->isNotEmpty()) {
            $user->proyectosAsignados()->sync($proyectosIds->all());
        }

        // Asignar rol Spatie si existe
        if (Role::where('name', $data['rol'])->exists()) {
            $user->syncRoles([$data['rol']]);
        }

        // Vincular con ficha de personal si se seleccionó
        if ($personalId) {
            Personal::where('id', $personalId)->update(['user_id' => $user->id]);
        }

        return redirect()
            ->route('users.index')
            ->with('status', "Usuario {$user->email} creado exitosamente con rol {$user->rol}.");
    }

    /**
     * Display the specified system user.
     */
    public function show(User $user): View
    {
        $user->load(['personal.proyecto', 'personal.proyectos', 'roles', 'proyecto', 'proyectosAsignados', 'despachosRegistrados' => fn ($q) => $q->latest('id')->limit(5)]);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified system user.
     */
    public function edit(User $user): View
    {
        $user->load('proyectosAsignados');

        $personalSinUsuario = Personal::where(function ($q) use ($user) {
            $q->whereNull('user_id')
                ->orWhere('user_id', $user->id);
        })->where('estado', 'ACTIVO')->orderBy('nombres')->get();

        $roles = Role::all();
        $proyectos = Proyecto::where('estado', 'ACTIVO')->orderBy('nombre')->get();

        return view('users.edit', compact('user', 'personalSinUsuario', 'roles', 'proyectos'));
    }

    /**
     * Update the specified system user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $personalId = $data['personal_id'] ?? null;
        $proyectosIds = collect($data['proyectos_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if (! empty($data['proyecto_id']) && ! $proyectosIds->contains((int) $data['proyecto_id'])) {
            $proyectosIds->prepend((int) $data['proyecto_id']);
        }

        $primaryProyectoId = $data['rol'] !== 'ADMINISTRADOR'
            ? ($data['proyecto_id'] ?? $proyectosIds->first())
            : null;

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'rol' => $data['rol'],
            'estado' => $data['estado'],
            'proyecto_id' => $primaryProyectoId,
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        if ($data['rol'] === 'ADMINISTRADOR') {
            $user->proyectosAsignados()->detach();
        } elseif ($request->has('proyectos_ids') || array_key_exists('proyecto_id', $data)) {
            $user->proyectosAsignados()->sync($proyectosIds->all());
        }

        // Actualizar rol Spatie
        if (Role::where('name', $data['rol'])->exists()) {
            $user->syncRoles([$data['rol']]);
        }

        // Manejo de la vinculación con personal
        if ($personalId) {
            // Desvincular cualquier personal previo si era distinto
            Personal::where('user_id', $user->id)->where('id', '!=', $personalId)->update(['user_id' => null]);
            Personal::where('id', $personalId)->update(['user_id' => $user->id]);
        } else {
            Personal::where('user_id', $user->id)->update(['user_id' => null]);
        }

        return redirect()
            ->route('users.index')
            ->with('status', "Usuario {$user->email} actualizado correctamente.");
    }

    /**
     * Remove the specified system user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()
                ->route('users.index')
                ->with('error', 'No puedes eliminar tu propia cuenta de usuario en sesión.');
        }

        // Desvincular ficha de personal asociada antes del borrado suave
        Personal::where('user_id', $user->id)->update(['user_id' => null]);

        $email = $user->email;
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', "Usuario {$email} deshabilitado del sistema.");
    }
}
