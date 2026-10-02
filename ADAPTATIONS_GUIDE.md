# Guía de Arquitectura y Adaptaciones Futuras — LogisticPCS

Este documento está dirigido a desarrolladores, arquitectos de software y administradores de sistemas que deseen mantener, extender o desarrollar nuevos módulos sobre la plataforma **LogisticPCS**.

---

## 🏛️ 1. Arquitectura General del Sistema

LogisticPCS sigue el patrón de diseño **MVC (Modelo - Vista - Controlador)** bajo el framework **Laravel 12**, complementado con **Services/Actions** para lógica de negocio de alta complejidad (PEPS, Kardex, Onboarding).

```
app/
├── Http/
│   ├── Controllers/          # Controladores HTTP por módulo (Articulo, Inventario, Cuadrilla, etc.)
│   ├── Middleware/           # Middlewares de seguridad (CheckOnboardingComplete, Spatie RoleMiddleware)
│   └── Requests/             # Form Requests con reglas de validación aisladas
├── Models/                   # Modelos Eloquent con relaciones y casts de fecha
├── Services/                 # Servicios de negocio desacoplados (KardexService, CuadrillaRosterService)
├── Exports/                  # Clases Maatwebsite Excel para reportes
database/
├── migrations/               # 31 migraciones estructuradas cronológicamente
├── seeders/                  # Semilleros (RolePermissionSeeder, CleanInitialSeeder)
├── clean_deploy_database.sql # Volcado SQL puro listo para importación en producción
resources/
├── views/                    # Vistas Blade jerarquizadas (layouts, components, módulos)
routes/
├── web.php                   # Definición de rutas protegidas y públicas
```

---

## 🧩 2. Componentes Clave del Sistema

### 2.1. Sistema de Inicialización Guiada (Onboarding)
* **Objetivo**: Bloquear la operativa del sistema hasta que existan los 4 pilares básicos:
  1. Configuración de Empresa (`empresa_configs`).
  2. Al menos un Proyecto (`proyectos`).
  3. Al menos un Almacén (`almacenes`).
  4. Al menos una Categoría (`categorias`).
* **Middleware**: `App\Http\Middleware\CheckOnboardingComplete`
  * Si el usuario autenticado intenta acceder a cualquier ruta operativa sin haber completado los 4 pilares, es redirigido a la ruta `/onboarding`.
  * Si ya está completado e intenta ingresar a `/onboarding`, es redirigido automáticamente a su `/dashboard`.
* **Personalización del Logotipo/Ícono**:
  * Implementado en el modelo `EmpresaConfig` mediante el accesor `icono_url`.
  * Si no hay archivo cargado en base de datos, toma por defecto el activo oficial `storage/app/public/sistema/icono.webp`.

### 2.2. Motor de Inventarios y Kardex PEPS (FIFO)
* **Mecánica**: Todo movimiento de entrada o salida genera un registro inmutable en `movimiento_inventarios` y actualiza la tabla de saldos consolidados `articulo_almacen`.
* **Cálculo de Costo PEPS**:
  * Al despachar stock, el sistema consume las capas de ingreso más antiguas (`lotes` o `ingresos`) para determinar el costo de venta/salida real.
* **Firma Digital en Canvas HTML5**:
  * Las actas de salida capturan la firma manuscrita del técnico receptor mediante un elemento `<canvas>` HTML5 codificado en Base64 PNG.
  * Almacenado de forma segura en `storage/app/public/firmas/`.

### 2.3. Control de Cuadrillas & Régimen 14x7
* **Lógica de Guardia**: Los trabajadores rotan en régimen minero/construcción 14 días de trabajo por 7 días de descanso legal.
* **Validación**: Impide programar personal en turnos de trabajo si su ciclo corresponde a descanso obligatorio.
* **Custodia de Herramientas**: La asignación de herramientas no solo se realiza por trabajador individual, sino por cuadrilla con responsabilidad solidaria.

### 2.4. Seguridad & Control de Accesos (Spatie Permission)
* 5 Roles predefinidos:
  1. `administrador`: Acceso irrestricto.
  2. `almacen`: Control de inventario, ingresos, despachos y catálogos.
  3. `supervisor`: Asignación de personal, cuadrillas y visualización de proyectos.
  4. `tecnico`: Consulta de catálogo y recepción de material con firma.
  5. `auditor`: Solo lectura y trazabilidad de bitácora.
* Para proteger un nuevo método en controlador:
  ```php
  $this->middleware('permission:nombre.permiso');
  ```
  O en Blade:
  ```blade
  @can('nombre.permiso')
      <button class="btn btn-primary">Acción Especial</button>
  @endcan
  ```

---

## 🚀 3. Guía para Adaptaciones y Nuevos Módulos

En el Roadmap del landing page se han proyectado futuras expansiones. A continuación se detalla cómo crear un nuevo módulo siguiendo el estándar de LogisticPCS:

### Ejemplo: Implementación del Módulo "Gestión de Flota" (Vehículos)

#### Paso 1: Crear la Migración y Modelo
```bash
php artisan make:model Vehiculo -m
```
En la migración `database/migrations/xxxx_create_vehiculos_table.php`:
```php
Schema::create('vehiculos', function (Blueprint $table) {
    $table->id();
    $table->string('placa', 15)->unique();
    $table->string('marca', 50);
    $table->string('modelo', 50);
    $table->integer('kilometraje_actual')->default(0);
    $table->date('vencimiento_soat')->nullable();
    $table->date('vencimiento_revision_tecnica')->nullable();
    $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();
    $table->enum('estado', ['operativo', 'en_taller', 'inactivo'])->default('operativo');
    $table->timestamps();
});
```

#### Paso 2: Crear el Controlador y Form Request
```bash
php artisan make:controller VehiculoController --resource
php artisan make:request StoreVehiculoRequest
```

#### Paso 3: Registrar Permisos en el Seeder
En `database/seeders/RolePermissionSeeder.php`:
```php
Permission::create(['name' => 'vehiculos.index', 'guard_name' => 'web']);
Permission::create(['name' => 'vehiculos.create', 'guard_name' => 'web']);
Permission::create(['name' => 'vehiculos.edit', 'guard_name' => 'web']);
Permission::create(['name' => 'vehiculos.delete', 'guard_name' => 'web']);

$admin->givePermissionTo(['vehiculos.index', 'vehiculos.create', 'vehiculos.edit', 'vehiculos.delete']);
$supervisor->givePermissionTo(['vehiculos.index', 'vehiculos.edit']);
```

#### Paso 4: Registrar Rutas
En `routes/web.php` dentro del grupo de middleware `['auth', 'onboarding.complete']`:
```php
Route::resource('vehiculos', VehiculoController::class);
```

#### Paso 5: Vistas Blade
Crear la carpeta `resources/views/vehiculos/` extendiendo siempre del layout maestro:
```blade
@extends('layouts.app')

@section('title', 'Control de Flota y Vehículos')

@section('content')
<div class="container-fluid py-4">
    <!-- Contenido con clases Bootstrap 5.3 -->
</div>
@endsection
```

---

## ⏰ 4. Configuración de Zona Horaria y Registro de Tiempos

Para garantizar que ningún registro sufra desfase horario respecto a la hora local peruana:
1. **Configuración de Aplicación** (`config/app.php`):
   ```php
   'timezone' => 'America/Lima',
   ```
2. **Casting de Fechas en Modelos Eloquent**:
   ```php
   protected function casts(): array
   {
       return [
           'created_at' => 'datetime',
           'updated_at' => 'datetime',
       ];
   }
   ```
3. **Respaldo Automático de Base de Datos**:
   * Los respaldos generados vía `php artisan backup:run` o desde el panel de administración nombran los archivos con timestamp en formato `Y-m-d_H-i-s` según la zona `America/Lima`.

---

## 🧪 5. Pruebas Unitarias para Nuevos Cambios

Cada vez que agregues un nuevo módulo o cambies la lógica de inventario, debes correr y crear pruebas:
```bash
# Crear nueva prueba de funcionalidad
php artisan make:test VehiculoManagementTest

# Ejecutar la suite completa de pruebas
php artisan test
```

---

*Documentación técnica elaborada para el equipo de desarrollo de LogisticPCS.*
