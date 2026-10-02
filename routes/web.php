<?php

use App\Http\Controllers\ActivoController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\ArticuloController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CuadrillaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DespachoController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\DocumentacionController;
use App\Http\Controllers\EmpresaConfigController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\InicializacionController;
use App\Http\Controllers\InventarioStockController;
use App\Http\Controllers\KardexController;
use App\Http\Controllers\KitController;
use App\Http\Controllers\MantenimientoCalibracionController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\UserController;
use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - LogisticPCS
|--------------------------------------------------------------------------
*/

// Portal de Bienvenida
Route::get('/', function () {
    $stats = [
        'articulos' => Articulo::count(),
        'activos' => Activo::count(),
        'proyectos' => Proyecto::count(),
        'ubicaciones' => Ubicacion::count(),
    ];

    return view('welcome', compact('stats'));
})->name('home');

// Autenticación de Usuarios
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Módulos Protegidos del Sistema
Route::middleware('auth')->group(function () {
    // Asistente de Inicialización (Ventana dedicada Progress Steps)
    Route::get('/inicializacion', [InicializacionController::class, 'index'])->name('inicializacion.index');
    Route::get('/inicializacion/espera', [InicializacionController::class, 'espera'])->name('inicializacion.espera');

    // Dashboard Ejecutivo
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil de Usuario
    Route::get('perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('perfil', [ProfileController::class, 'update'])->name('profile.update');

    // Configuración de Empresa
    Route::get('configuracion/empresa', [EmpresaConfigController::class, 'edit'])->name('configuracion.empresa');
    Route::put('configuracion/empresa', [EmpresaConfigController::class, 'update'])->name('configuracion.empresa.update');
    Route::delete('configuracion/empresa/logotipo', [EmpresaConfigController::class, 'eliminarLogotipo'])->name('configuracion.empresa.logotipo.destroy');
    Route::delete('configuracion/empresa/icono', [EmpresaConfigController::class, 'eliminarIcono'])->name('configuracion.empresa.icono.destroy');

    // Configuración y Datos Maestros
    Route::resource('categorias', CategoriaController::class);
    Route::resource('ubicaciones', UbicacionController::class)->parameters(['ubicaciones' => 'ubicacion']);

    // Fase 2: Proyectos, Personal & Usuarios
    Route::post('proyectos/{proyecto}/activar', [ProyectoController::class, 'activar'])->name('proyectos.activar');
    Route::resource('proyectos', ProyectoController::class);
    Route::resource('personal', PersonalController::class);
    Route::get('users/roles/{role}/permissions', [UserController::class, 'editRolePermissions'])->name('users.roles.permissions.edit');
    Route::put('users/roles/{role}/permissions', [UserController::class, 'updateRolePermissions'])->name('users.roles.permissions.update');
    Route::resource('users', UserController::class);

    // Fase 3: Catálogo Maestro, Activos Serializados y Kits
    Route::resource('articulos', ArticuloController::class);
    Route::get('activos/{activo}/etiqueta', [ActivoController::class, 'etiqueta'])->name('activos.etiqueta');
    Route::resource('activos', ActivoController::class);
    Route::delete('kits/{kit}/componentes/{articulo}', [KitController::class, 'desvincularComponente'])->name('kits.componentes.desvincular');
    Route::resource('kits', KitController::class);
    Route::get('inventario/stock', [InventarioStockController::class, 'index'])->name('inventario.stock');

    // Fase 4: Operaciones de Almacén (Ingresos, Despachos, Devoluciones y Kardex PEPS)
    Route::get('ingresos/buscar-articulo', [IngresoController::class, 'buscarArticulo'])->name('ingresos.buscar-articulo');
    Route::post('ingresos/crear-articulo-rapido', [IngresoController::class, 'crearArticuloRapido'])->name('ingresos.crear-articulo-rapido');
    Route::resource('ingresos', IngresoController::class)->only(['index', 'create', 'store', 'show']);

    Route::get('despachos/{despacho}/acta', [DespachoController::class, 'acta'])->name('despachos.acta');
    Route::get('despachos/{despacho}/devolucion', [DevolucionController::class, 'create'])->name('despachos.devolucion');
    Route::post('despachos/{despacho}/devolucion', [DevolucionController::class, 'store'])->name('despachos.devolucion.store');
    Route::resource('despachos', DespachoController::class);
    Route::get('movimientos', [MovimientoController::class, 'index'])->name('movimientos.index');
    Route::get('kardex', [KardexController::class, 'index'])->name('kardex.index');

    // Fase 5: Cuadrillas de Trabajo & Programación Roster 14x7
    Route::post('cuadrillas/{cuadrilla}/miembros', [CuadrillaController::class, 'addMiembro'])->name('cuadrillas.miembros.add');
    Route::delete('cuadrillas/{cuadrilla}/miembros/{personal}', [CuadrillaController::class, 'retirarMiembro'])->name('cuadrillas.miembros.retirar');
    Route::resource('cuadrillas', CuadrillaController::class);

    Route::get('roster/check-condicion', [RosterController::class, 'checkCondicion'])->name('roster.check-condicion');
    Route::post('roster/generar-ciclo', [RosterController::class, 'generarCiclo'])->name('roster.generar-ciclo');
    Route::resource('roster', RosterController::class)->only(['index', 'store']);

    // Servicios Técnicos, Calibraciones y Taller
    Route::post('mantenimientos/{mantenimiento}/dar-alta', [MantenimientoCalibracionController::class, 'darAlta'])->name('mantenimientos.dar-alta');
    Route::resource('mantenimientos', MantenimientoCalibracionController::class);

    // Centro de Alertas y Notificaciones
    Route::get('alertas', [AlertaController::class, 'index'])->name('alertas.index');
    Route::post('alertas/{alerta}/leida', [AlertaController::class, 'marcarLeida'])->name('alertas.marcarLeida');
    Route::post('alertas/marcar-todas-leidas', [AlertaController::class, 'marcarTodasLeidas'])->name('alertas.marcarTodasLeidas');
    Route::post('alertas/escanear', [AlertaController::class, 'escanear'])->name('alertas.escanear');
    Route::delete('alertas/{alerta}', [AlertaController::class, 'destroy'])->name('alertas.destroy');

    // Fase 6: Reportabilidad, Exportaciones Masivas & Hojas de Cargo
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('reportes/export/inventario', [ReporteController::class, 'exportInventario'])->name('reportes.export.inventario');
    Route::get('reportes/export/kardex', [ReporteController::class, 'exportKardex'])->name('reportes.export.kardex');
    Route::get('reportes/export/roster', [ReporteController::class, 'exportRoster'])->name('reportes.export.roster');
    Route::get('reportes/export/activos', [ReporteController::class, 'exportActivos'])->name('reportes.export.activos');
    Route::get('reportes/pdf/inventario', [ReporteController::class, 'pdfInventario'])->name('reportes.pdf.inventario');
    Route::get('reportes/cuadrilla/{cuadrilla}/pdf', [ReporteController::class, 'pdfCuadrillaDotacion'])->name('reportes.cuadrilla.pdf');

    // Auditoría (Log del Sistema) y Backups de Base de Datos
    Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
    Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('backups/generar', [BackupController::class, 'generar'])->name('backups.generar');
    Route::get('backups/{archivo}/descargar', [BackupController::class, 'descargar'])->name('backups.descargar');
    Route::delete('backups/{archivo}', [BackupController::class, 'eliminar'])->name('backups.eliminar');

    // Módulo de Documentación, Stack Tecnológico y Guía de Usuario
    Route::get('documentacion', [DocumentacionController::class, 'index'])->name('documentacion.index');
});

// Ruta de entrega directa de archivos de storage (soporte multiplataforma y Windows)
Route::get('storage/{path}', function (string $path) {
    $fullPath = storage_path('app/public/'.$path);
    if (! file_exists($fullPath)) {
        abort(404);
    }

    return response()->file($fullPath);
})->where('path', '.*')->name('storage.file');
