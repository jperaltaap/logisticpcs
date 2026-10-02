<?php

namespace Tests\Feature;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Cuadrilla;
use App\Models\EmpresaConfig;
use App\Models\InventarioStock;
use App\Models\Kit;
use App\Models\NotificacionAlerta;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\RosterTurno;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarModulesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        EmpresaConfig::instancia()->update([
            'razon_social' => 'Empresa Test S.A.C.',
            'ruc' => '20601234567',
        ]);
        $this->admin = User::where('rol', 'ADMINISTRADOR')->first() ?? User::factory()->create(['rol' => 'ADMINISTRADOR']);
        $this->admin->syncRoles(['ADMINISTRADOR']);
    }

    public function test_all_sidebar_links_are_accessible_for_authenticated_user(): void
    {
        $rutas = [
            route('dashboard'),
            route('personal.index'),
            route('cuadrillas.index'),
            route('roster.index'),
            route('articulos.index'),
            route('activos.index'),
            route('kits.index'),
            route('ingresos.index'),
            route('despachos.index'),
            route('movimientos.index'),
            route('inventario.stock'),
            route('alertas.index'),
            route('kardex.index'),
            route('mantenimientos.index'),
            route('reportes.index'),
            route('configuracion.empresa'),
            route('proyectos.index'),
            route('ubicaciones.index'),
            route('categorias.index'),
            route('users.index'),
            route('auditoria.index'),
            route('backups.index'),
            route('documentacion.index'),
        ];

        foreach ($rutas as $url) {
            $response = $this->actingAs($this->admin)->get($url);
            $response->assertStatus(200);
        }
    }

    public function test_logistico_cannot_manage_personal_cuadrillas_or_roster(): void
    {
        $logistico = User::factory()->create(['rol' => 'LOGISTICO']);
        $personal = Personal::first();
        $proyecto = Proyecto::first();

        // Personal restrictions for LOGISTICO
        $this->actingAs($logistico)->get(route('personal.create'))->assertStatus(403);
        $this->actingAs($logistico)->post(route('personal.store'), [
            'dni' => '99887766',
            'nombres' => 'Test',
            'apellidos' => 'Logistico',
            'cargo' => 'Tecnico',
            'area' => 'Campo',
            'estado' => 'ACTIVO',
        ])->assertStatus(403);
        $this->actingAs($logistico)->get(route('personal.edit', $personal))->assertStatus(403);
        $this->actingAs($logistico)->put(route('personal.update', $personal), [
            'dni' => $personal->dni,
            'nombres' => 'Modificado',
            'apellidos' => $personal->apellidos,
            'cargo' => $personal->cargo,
            'area' => $personal->area,
            'estado' => 'ACTIVO',
        ])->assertStatus(403);
        $this->actingAs($logistico)->delete(route('personal.destroy', $personal))->assertStatus(403);

        // Cuadrillas restrictions for LOGISTICO
        $this->actingAs($logistico)->get(route('cuadrillas.create'))->assertStatus(403);
        $this->actingAs($logistico)->post(route('cuadrillas.store'), [
            'codigo_cuadrilla' => 'CUA-LOG-99',
            'nombre' => 'Cuadrilla Test',
            'proyecto_id' => $proyecto->id,
            'lider_personal_id' => $personal->id,
            'regimen_laboral' => '14x7',
            'estado' => 'ACTIVA',
        ])->assertStatus(403);

        // Roster restrictions for LOGISTICO
        $this->actingAs($logistico)->post(route('roster.store'), [
            'personal_id' => $personal->id,
            'proyecto_id' => $proyecto->id,
            'grupo_guardia' => '14x7',
            'fecha' => now()->toDateString(),
            'condicion_laboral' => 'TRABAJO_CAMPO',
        ])->assertStatus(403);
        $this->actingAs($logistico)->post(route('roster.generar-ciclo'), [
            'personal_ids' => [$personal->id],
            'proyecto_id' => $proyecto->id,
            'grupo_guardia' => '14x7',
            'fecha_inicio' => now()->toDateString(),
            'dias_trabajo' => 14,
            'dias_descanso' => 7,
            'ciclos' => 1,
        ])->assertStatus(403);
    }

    public function test_auditoria_logs_actions_and_backups_generation(): void
    {
        $proyecto = Proyecto::first();

        // Store a warehouse without 'tipo', using active project session
        $response = $this->actingAs($this->admin)
            ->withSession(['proyecto_activo_id' => $proyecto->id])
            ->post(route('ubicaciones.store'), [
                'codigo' => 'ALM-PROY-ACT',
                'nombre' => 'Almacén Proyecto Activo',
                'descripcion' => 'Av. Industrial 450 - Trujillo',
                'estado' => 'ACTIVO',
            ]);

        $response->assertRedirect(route('ubicaciones.index'));
        $this->assertDatabaseHas('ubicaciones', [
            'codigo' => 'ALM-PROY-ACT',
            'proyecto_id' => $proyecto->id,
            'tipo' => 'ALMACEN_CENTRAL',
        ]);

        // Verify SystemLog recorded the creation
        $this->assertDatabaseHas('system_logs', [
            'accion' => 'CREACION',
            'modulo' => 'ALMACENES',
            'user_id' => $this->admin->id,
        ]);

        // Filter audit log
        $auditResponse = $this->actingAs($this->admin)->get(route('auditoria.index', [
            'accion' => 'CREACION',
            'modulo' => 'ALMACENES',
            'user_id' => $this->admin->id,
        ]));
        $auditResponse->assertStatus(200);
        $auditResponse->assertSee('ALM-PROY-ACT');

        // Generate database backup
        $backupResponse = $this->actingAs($this->admin)->post(route('backups.generar'));
        $backupResponse->assertRedirect(route('backups.index'));

        $this->assertDatabaseHas('system_logs', [
            'accion' => 'BACKUP',
            'modulo' => 'BACKUPS_BD',
        ]);
    }

    public function test_multi_project_and_multi_responsible_assignments(): void
    {
        $personal1 = Personal::first();
        $personal2 = Personal::skip(1)->first() ?? $personal1;

        $response = $this->actingAs($this->admin)->post(route('proyectos.store'), [
            'codigo' => 'PRY-MULTI-01',
            'nombre' => 'Proyecto Sucursal Multi Responsable',
            'cliente' => 'Minera del Sur S.A.C.',
            'ubicacion_direccion' => 'Arequipa',
            'fecha_inicio' => now()->format('Y-m-d'),
            'estado' => 'ACTIVO',
            'responsables_ids' => array_unique([$personal1->id, $personal2->id]),
        ]);

        $response->assertRedirect(route('proyectos.index'));
        $proyecto = Proyecto::where('codigo', 'PRY-MULTI-01')->firstOrFail();
        $this->assertTrue($proyecto->responsables->contains('id', $personal1->id));

        // Assign multiple projects to a user
        $otroProyecto = Proyecto::where('id', '!=', $proyecto->id)->first();
        $logistico = User::factory()->create(['rol' => 'LOGISTICO']);
        $logistico->proyectosAsignados()->sync([$proyecto->id, $otroProyecto->id]);

        $permitidos = $logistico->fresh()->getProyectosPermitidosIds();
        $this->assertContains($proyecto->id, $permitidos);
        $this->assertContains($otroProyecto->id, $permitidos);
    }

    public function test_categorias_crud_operations(): void
    {
        // Index
        $response = $this->actingAs($this->admin)->get(route('categorias.index'));
        $response->assertStatus(200);

        // Create
        $response = $this->actingAs($this->admin)->get(route('categorias.create'));
        $response->assertStatus(200);

        // Store
        $response = $this->actingAs($this->admin)->post(route('categorias.store'), [
            'codigo' => 'CAT-TEST',
            'nombre' => 'Categoría de Prueba',
            'descripcion' => 'Descripción de prueba',
        ]);
        $response->assertRedirect(route('categorias.index'));
        $this->assertDatabaseHas('categorias', ['codigo' => 'CAT-TEST']);

        $cat = Categoria::where('codigo', 'CAT-TEST')->first();

        // Edit
        $response = $this->actingAs($this->admin)->get(route('categorias.edit', $cat));
        $response->assertStatus(200);

        // Update
        $response = $this->actingAs($this->admin)->put(route('categorias.update', $cat), [
            'codigo' => 'CAT-TEST',
            'nombre' => 'Categoría Actualizada',
        ]);
        $response->assertRedirect(route('categorias.index'));
        $this->assertDatabaseHas('categorias', ['nombre' => 'Categoría Actualizada']);

        // Destroy
        $response = $this->actingAs($this->admin)->delete(route('categorias.destroy', $cat));
        $response->assertRedirect(route('categorias.index'));
        $this->assertDatabaseMissing('categorias', ['id' => $cat->id]);
    }

    public function test_ubicaciones_crud_operations(): void
    {
        // Index
        $response = $this->actingAs($this->admin)->get(route('ubicaciones.index'));
        $response->assertStatus(200);

        // Store
        $response = $this->actingAs($this->admin)->post(route('ubicaciones.store'), [
            'codigo' => 'ALM-SEC',
            'nombre' => 'Almacén Secundario',
            'estado' => 'ACTIVO',
        ]);
        $response->assertRedirect(route('ubicaciones.index'));
        $this->assertDatabaseHas('ubicaciones', ['codigo' => 'ALM-SEC']);
    }

    public function test_mantenimientos_operations(): void
    {
        $activo = Activo::first();

        // Create
        $response = $this->actingAs($this->admin)->get(route('mantenimientos.create'));
        $response->assertStatus(200);

        // Store
        $response = $this->actingAs($this->admin)->post(route('mantenimientos.store'), [
            'activo_id' => $activo->id,
            'tipo' => 'CALIBRACION_LAB',
            'fecha_ingreso' => now()->format('Y-m-d'),
            'resultado' => 'EN_PROCESO',
            'descripcion_falla_o_trabajo' => 'Prueba de calibración metrológica',
        ]);
        $response->assertRedirect(route('mantenimientos.index'));

        $this->assertDatabaseHas('mantenimientos_calibraciones', [
            'activo_id' => $activo->id,
            'tipo' => 'CALIBRACION_LAB',
        ]);

        $this->assertEquals('EN_MANTENIMIENTO', $activo->fresh()->estado_operativo);
    }

    public function test_alertas_operations(): void
    {
        // Index
        $response = $this->actingAs($this->admin)->get(route('alertas.index'));
        $response->assertStatus(200);

        // Escanear
        $response = $this->actingAs($this->admin)->post(route('alertas.escanear'));
        $response->assertRedirect();

        // Marcar todas leídas
        $response = $this->actingAs($this->admin)->post(route('alertas.marcarTodasLeidas'));
        $response->assertRedirect();
        $this->assertEquals(0, NotificacionAlerta::where('leida', false)->count());
    }

    public function test_cuadrillas_only_allow_personal_from_same_project(): void
    {
        $proyecto1 = Proyecto::find(1) ?? Proyecto::first();
        $proyecto2 = Proyecto::where('id', '!=', $proyecto1->id)->first();

        $personalP1 = Personal::where('proyecto_id', $proyecto1->id)->first();
        $personalP2 = Personal::where('proyecto_id', $proyecto2->id)->first();

        // Intentar crear cuadrilla en Proyecto 1 con líder del Proyecto 2 -> debe fallar validación
        $failResponse = $this->actingAs($this->admin)->post(route('cuadrillas.store'), [
            'codigo_cuadrilla' => 'CD-FAIL-01',
            'nombre' => 'Cuadrilla Proyecto Cruzado',
            'proyecto_id' => $proyecto1->id,
            'lider_personal_id' => $personalP2->id,
            'regimen_laboral' => '14x7',
            'estado' => 'ACTIVA',
        ]);
        $failResponse->assertSessionHasErrors('lider_personal_id');
        $this->assertDatabaseMissing('cuadrillas', ['codigo_cuadrilla' => 'CD-FAIL-01']);

        // Crear cuadrilla en Proyecto 1 con líder del Proyecto 1 -> debe tener éxito
        $okResponse = $this->actingAs($this->admin)->post(route('cuadrillas.store'), [
            'codigo_cuadrilla' => 'CD-OK-01',
            'nombre' => 'Cuadrilla Proyecto Uno',
            'proyecto_id' => $proyecto1->id,
            'lider_personal_id' => $personalP1->id,
            'regimen_laboral' => '14x7',
            'estado' => 'ACTIVA',
        ]);
        $cuadrilla = Cuadrilla::where('codigo_cuadrilla', 'CD-OK-01')->firstOrFail();
        $okResponse->assertRedirect(route('cuadrillas.show', $cuadrilla));

        // Intentar agregar miembro del Proyecto 2 a la cuadrilla del Proyecto 1 -> debe fallar
        $addFailResponse = $this->actingAs($this->admin)->post(route('cuadrillas.miembros.add', $cuadrilla), [
            'personal_id' => $personalP2->id,
            'rol_en_cuadrilla' => 'Técnico Apoyo',
            'fecha_incorporacion' => now()->toDateString(),
        ]);
        $addFailResponse->assertSessionHasErrors('personal_id');
    }

    public function test_roster_cycle_scheduling_per_personal(): void
    {
        $personal = Personal::whereNotNull('proyecto_id')->firstOrFail();
        $fechaInicio = now()->startOfMonth()->toDateString();

        $response = $this->actingAs($this->admin)
            ->withSession(['proyecto_activo_id' => $personal->proyecto_id])
            ->post(route('roster.generar-ciclo'), [
                'personal_id' => $personal->id,
                'grupo_guardia' => '14x7',
                'fecha_inicio' => $fechaInicio,
                'dias_trabajo' => 14,
                'dias_descanso' => 7,
                'ciclos' => 1,
            ]);

        $response->assertRedirect();
        $this->assertTrue(
            RosterTurno::where('personal_id', $personal->id)
                ->where('proyecto_id', $personal->proyecto_id)
                ->whereDate('fecha', $fechaInicio)
                ->where('condicion_laboral', 'TRABAJO_CAMPO')
                ->exists()
        );
    }

    public function test_articulos_serialized_activo_vs_consumible_stock_minimo_and_vida_util_rules(): void
    {
        $categoria = Categoria::first();
        $proyecto = Proyecto::first();

        // 1. Serializado como Activo: no considera stock_minimo (se guarda en 0), pero exige vida_util_meses
        $failActivo = $this->actingAs($this->admin)->post(route('articulos.store'), [
            'categoria_id' => $categoria->id,
            'proyecto_id' => $proyecto->id,
            'codigo_sku' => 'SKU-SER-ACT-01',
            'descripcion' => 'OTDR Reflectómetro Óptico',
            'unidad_medida' => 'UND',
            'tipo_articulo' => 'EQUIPO',
            'control_serie' => '1',
            'es_instalable' => '0',
            'stock_minimo' => '10',
            'vida_util_meses' => '',
            'estado' => 'ACTIVO',
        ]);
        $failActivo->assertSessionHasErrors('vida_util_meses');

        $okActivo = $this->actingAs($this->admin)->post(route('articulos.store'), [
            'categoria_id' => $categoria->id,
            'proyecto_id' => $proyecto->id,
            'codigo_sku' => 'SKU-SER-ACT-01',
            'descripcion' => 'OTDR Reflectómetro Óptico',
            'unidad_medida' => 'UND',
            'tipo_articulo' => 'EQUIPO',
            'control_serie' => '1',
            'es_instalable' => '0',
            'stock_minimo' => '10',
            'vida_util_meses' => '48',
            'estado' => 'ACTIVO',
        ]);
        $okActivo->assertSessionHasNoErrors();
        $this->assertDatabaseHas('articulos', [
            'codigo_sku' => 'SKU-SER-ACT-01',
            'control_serie' => true,
            'es_instalable' => false,
            'stock_minimo' => 0,
            'vida_util_meses' => 48,
        ]);

        // 2. Serializado y Consumible: considera tanto stock_minimo como vida_util_meses
        $okConsumibleSeriado = $this->actingAs($this->admin)->post(route('articulos.store'), [
            'categoria_id' => $categoria->id,
            'proyecto_id' => $proyecto->id,
            'codigo_sku' => 'SKU-SER-CON-01',
            'descripcion' => 'ONT Dual Band GPON Instalable',
            'unidad_medida' => 'UND',
            'tipo_articulo' => 'CONSUMIBLE',
            'control_serie' => '1',
            'es_instalable' => '1',
            'stock_minimo' => '15',
            'vida_util_meses' => '24',
            'estado' => 'ACTIVO',
        ]);
        $okConsumibleSeriado->assertSessionHasNoErrors();
        $this->assertDatabaseHas('articulos', [
            'codigo_sku' => 'SKU-SER-CON-01',
            'control_serie' => true,
            'es_instalable' => true,
            'stock_minimo' => 15,
            'vida_util_meses' => 24,
        ]);
    }

    public function test_activos_serialized_registration_requires_project_and_warehouse_without_initial_assignment(): void
    {
        $proyecto = Proyecto::first();
        $ubicacion = Ubicacion::create([
            'codigo' => 'ALM-P1-TEST',
            'nombre' => 'Almacén Test Proyecto 1',
            'tipo' => 'ALMACEN_CENTRAL',
            'proyecto_id' => $proyecto->id,
            'estado' => 'ACTIVO',
        ]);
        $articulo = Articulo::where('control_serie', true)->first();
        $personal = Personal::first();

        $response = $this->actingAs($this->admin)->post(route('activos.store'), [
            'articulo_id' => $articulo->id,
            'codigo_interno' => 'ACT-99999',
            'numero_serie' => 'SN-TEST-99999',
            'proyecto_actual_id' => $proyecto->id,
            'ubicacion_actual_id' => $ubicacion->id,
            'responsable_personal_id' => $personal->id,
            'estado_operativo' => 'OPERATIVO',
            'condicion_prestamo' => 'PRESTADO_CAMPO',
            'fecha_ingreso' => now()->toDateString(),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('activos', [
            'codigo_interno' => 'ACT-99999',
            'proyecto_actual_id' => $proyecto->id,
            'ubicacion_actual_id' => $ubicacion->id,
            'responsable_personal_id' => null,
            'cuadrilla_actual_id' => null,
            'condicion_prestamo' => 'DISPONIBLE',
        ]);
    }

    public function test_kits_monitoring_by_warehouse_same_warehouse_restriction_and_unlink_component(): void
    {
        $proyecto = Proyecto::first();
        $categoria = Categoria::first();

        $almacenA = Ubicacion::create([
            'codigo' => 'ALM-KIT-A',
            'nombre' => 'Almacén Kits Norte',
            'tipo' => 'ALMACEN_CENTRAL',
            'proyecto_id' => $proyecto->id,
            'estado' => 'ACTIVO',
        ]);

        $almacenB = Ubicacion::create([
            'codigo' => 'ALM-KIT-B',
            'nombre' => 'Almacén Kits Sur',
            'tipo' => 'ALMACEN_CENTRAL',
            'proyecto_id' => $proyecto->id,
            'estado' => 'ACTIVO',
        ]);

        $itemEnA1 = Articulo::create([
            'categoria_id' => $categoria->id,
            'proyecto_id' => $proyecto->id,
            'codigo_sku' => 'SKU-KITA-01',
            'descripcion' => 'Cortadora de Precisión Almacén A',
            'unidad_medida' => 'UND',
            'tipo_articulo' => 'HERRAMIENTA',
            'control_serie' => false,
            'es_instalable' => false,
            'stock_minimo' => 2,
            'estado' => 'ACTIVO',
        ]);

        $itemEnA2 = Articulo::create([
            'categoria_id' => $categoria->id,
            'proyecto_id' => $proyecto->id,
            'codigo_sku' => 'SKU-KITA-02',
            'descripcion' => 'Peladora de Fibra Almacén A',
            'unidad_medida' => 'UND',
            'tipo_articulo' => 'HERRAMIENTA',
            'control_serie' => false,
            'es_instalable' => false,
            'stock_minimo' => 2,
            'estado' => 'ACTIVO',
        ]);

        $itemEnB = Articulo::create([
            'categoria_id' => $categoria->id,
            'proyecto_id' => $proyecto->id,
            'codigo_sku' => 'SKU-KITB-01',
            'descripcion' => 'Linterna Frontal Almacén B',
            'unidad_medida' => 'UND',
            'tipo_articulo' => 'EQUIPO',
            'control_serie' => false,
            'es_instalable' => false,
            'stock_minimo' => 2,
            'estado' => 'ACTIVO',
        ]);

        InventarioStock::create([
            'articulo_id' => $itemEnA1->id,
            'ubicacion_id' => $almacenA->id,
            'cantidad_actual' => 10,
            'cantidad_reservada' => 0,
            'costo_promedio' => 0,
        ]);

        InventarioStock::create([
            'articulo_id' => $itemEnA2->id,
            'ubicacion_id' => $almacenA->id,
            'cantidad_actual' => 8,
            'cantidad_reservada' => 0,
            'costo_promedio' => 0,
        ]);

        InventarioStock::create([
            'articulo_id' => $itemEnB->id,
            'ubicacion_id' => $almacenB->id,
            'cantidad_actual' => 6,
            'cantidad_reservada' => 0,
            'costo_promedio' => 0,
        ]);

        // 1. Intentar crear Kit en Almacén A con un ítem que solo existe en Almacén B -> debe fallar
        $failResponse = $this->actingAs($this->admin)->post(route('kits.store'), [
            'codigo_kit' => 'KIT-CROSS-FAIL',
            'ubicacion_id' => $almacenA->id,
            'nombre_kit' => 'Kit Almacén Cruzado',
            'tipo_kit' => 'KIT_HERRAMIENTAS',
            'estado' => 'ACTIVO',
            'componentes' => [
                ['articulo_id' => $itemEnA1->id, 'cantidad' => 1],
                ['articulo_id' => $itemEnB->id, 'cantidad' => 1],
            ],
        ]);
        $failResponse->assertSessionHasErrors('componentes');
        $this->assertDatabaseMissing('kits', ['codigo_kit' => 'KIT-CROSS-FAIL']);

        // 2. Crear Kit en Almacén A solo con ítems del Almacén A -> debe tener éxito
        $okResponse = $this->actingAs($this->admin)->post(route('kits.store'), [
            'codigo_kit' => 'KIT-ALMA-OK',
            'ubicacion_id' => $almacenA->id,
            'nombre_kit' => 'Kit Homologado Almacén A',
            'tipo_kit' => 'KIT_HERRAMIENTAS',
            'estado' => 'ACTIVO',
            'componentes' => [
                ['articulo_id' => $itemEnA1->id, 'cantidad' => 2],
                ['articulo_id' => $itemEnA2->id, 'cantidad' => 1],
            ],
        ]);
        $okResponse->assertSessionHasNoErrors();
        $kit = Kit::where('codigo_kit', 'KIT-ALMA-OK')->firstOrFail();
        $this->assertEquals($almacenA->id, $kit->ubicacion_id);
        $this->assertCount(2, $kit->componentes);

        // 3. Monitorear kits disponibles filtrando por almacén y disponibilidad
        $monitorResponse = $this->actingAs($this->admin)->get(route('kits.index', [
            'ubicacion_id' => $almacenA->id,
            'disponibilidad' => 'COMPLETO',
        ]));
        $monitorResponse->assertStatus(200);
        $monitorResponse->assertSee('KIT-ALMA-OK');

        // 4. Desvincular un ítem del kit para darle salida de préstamo o traslado independiente
        $unlinkResponse = $this->actingAs($this->admin)->delete(
            route('kits.componentes.desvincular', [$kit, $itemEnA2]),
            ['ir_a_despacho' => '1', 'tipo_movimiento' => 'SALIDA_PRESTAMO']
        );
        $unlinkResponse->assertRedirect(route('despachos.create', [
            'ubicacion_origen_id' => $almacenA->id,
            'articulo_id' => $itemEnA2->id,
            'tipo_movimiento' => 'SALIDA_PRESTAMO',
        ]));

        $this->assertDatabaseMissing('componentes_kit', [
            'kit_id' => $kit->id,
            'articulo_id' => $itemEnA2->id,
        ]);
        $this->assertDatabaseHas('componentes_kit', [
            'kit_id' => $kit->id,
            'articulo_id' => $itemEnA1->id,
        ]);
    }
}
