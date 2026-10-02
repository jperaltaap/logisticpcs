<?php

namespace Tests\Feature;

use App\Models\Activo;
use App\Models\MantenimientoCalibracion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MantenimientoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('rol', 'ADMINISTRADOR')->first() ?? User::factory()->create(['rol' => 'ADMINISTRADOR']);
        $this->admin->syncRoles(['ADMINISTRADOR']);
    }

    public function test_changing_activo_to_en_mantenimiento_automatically_creates_open_service_record(): void
    {
        $activo = Activo::where('estado_operativo', 'OPERATIVO')->first();
        $this->assertNotNull($activo);

        $response = $this->actingAs($this->admin)->put(route('activos.update', $activo), [
            'articulo_id' => $activo->articulo_id,
            'codigo_interno' => $activo->codigo_interno,
            'ubicacion_actual_id' => $activo->ubicacion_actual_id,
            'proyecto_actual_id' => $activo->proyecto_actual_id,
            'estado_operativo' => 'EN_MANTENIMIENTO',
            'fecha_ingreso' => $activo->fecha_ingreso->format('Y-m-d'),
            'observaciones' => 'Equipo requiere calibración óptica anual.',
        ]);

        $response->assertRedirect(route('activos.show', $activo));

        $this->assertEquals('EN_MANTENIMIENTO', $activo->fresh()->estado_operativo);

        // Verificamos que se enlistó en Calibraciones & Taller
        $this->assertDatabaseHas('mantenimientos_calibraciones', [
            'activo_id' => $activo->id,
            'resultado' => 'EN_PROCESO',
        ]);
    }

    public function test_cannot_discharge_activo_to_operativo_directly_from_activos_module_when_service_is_open(): void
    {
        $activo = Activo::where('estado_operativo', 'OPERATIVO')->first();
        $activo->update(['estado_operativo' => 'EN_MANTENIMIENTO']);

        MantenimientoCalibracion::create([
            'activo_id' => $activo->id,
            'proyecto_id' => $activo->proyecto_actual_id,
            'tipo' => 'CALIBRACION_LAB',
            'proveedor_taller' => 'Laboratorio Metrológico',
            'fecha_ingreso' => now()->toDateString(),
            'resultado' => 'EN_PROCESO',
            'descripcion_falla_o_trabajo' => 'Calibración en laboratorio externo',
            'user_id' => $this->admin->id,
        ]);

        // Intentar pasar a OPERATIVO desde activos.update debe ser rechazado
        $response = $this->actingAs($this->admin)->from(route('activos.edit', $activo))->put(route('activos.update', $activo), [
            'articulo_id' => $activo->articulo_id,
            'codigo_interno' => $activo->codigo_interno,
            'ubicacion_actual_id' => $activo->ubicacion_actual_id,
            'proyecto_actual_id' => $activo->proyecto_actual_id,
            'estado_operativo' => 'OPERATIVO',
            'fecha_ingreso' => $activo->fecha_ingreso->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('estado_operativo');
        $this->assertEquals('EN_MANTENIMIENTO', $activo->fresh()->estado_operativo);
    }

    public function test_adding_equipment_in_mantenimientos_automatically_updates_activo_state(): void
    {
        $activo = Activo::where('estado_operativo', 'OPERATIVO')->first();

        $response = $this->actingAs($this->admin)->post(route('mantenimientos.store'), [
            'activo_id' => $activo->id,
            'tipo' => 'PREVENTIVO',
            'fecha_ingreso' => now()->toDateString(),
            'proveedor_taller' => 'Taller Electromecánico Central',
            'costo' => 250.00,
            'resultado' => 'EN_PROCESO',
            'descripcion_falla_o_trabajo' => 'Mantenimiento preventivo general y limpieza de conectores.',
        ]);

        $response->assertRedirect(route('mantenimientos.index'));

        // El activo cambia automáticamente a EN_MANTENIMIENTO
        $this->assertEquals('EN_MANTENIMIENTO', $activo->fresh()->estado_operativo);

        // Se muestra en la pestaña de taller activo
        $indexResponse = $this->actingAs($this->admin)->get(route('mantenimientos.index', ['tab' => 'en_taller']));
        $indexResponse->assertSee($activo->codigo_interno);
    }

    public function test_dar_alta_from_mantenimientos_discharges_activo_to_operativo_and_removes_from_workshop_list(): void
    {
        $activo = Activo::where('estado_operativo', 'OPERATIVO')->first();
        $activo->update(['estado_operativo' => 'EN_MANTENIMIENTO']);

        $mantenimiento = MantenimientoCalibracion::create([
            'activo_id' => $activo->id,
            'proyecto_id' => $activo->proyecto_actual_id,
            'tipo' => 'CALIBRACION_LAB',
            'proveedor_taller' => 'Certificadora INACAL',
            'fecha_ingreso' => now()->subDays(5)->toDateString(),
            'resultado' => 'EN_PROCESO',
            'descripcion_falla_o_trabajo' => 'Calibración periódica',
            'user_id' => $this->admin->id,
        ]);

        // Dar de alta desde el módulo de mantenimientos
        $response = $this->actingAs($this->admin)->post(route('mantenimientos.dar-alta', $mantenimiento), [
            'fecha_salida' => now()->toDateString(),
            'resultado' => 'CONFORME_OPERATIVO',
            'proxima_calibracion_sugerida' => now()->addYear()->toDateString(),
            'costo' => 380.00,
            'descripcion_falla_o_trabajo' => 'Calibración aprobada con certificado vigente.',
        ]);

        $response->assertRedirect(route('mantenimientos.index'));

        // El activo ahora es OPERATIVO y está DISPONIBLE
        $activoRefrescado = $activo->fresh();
        $this->assertEquals('OPERATIVO', $activoRefrescado->estado_operativo);
        $this->assertEquals('DISPONIBLE', $activoRefrescado->condicion_prestamo);

        // El registro de mantenimiento pasa a CONFORME_OPERATIVO
        $this->assertEquals('CONFORME_OPERATIVO', $mantenimiento->fresh()->resultado);

        // Ya NO se encuentra en la lista activa de taller
        $indexResponse = $this->actingAs($this->admin)->get(route('mantenimientos.index', ['tab' => 'en_taller']));
        $this->assertFalse($indexResponse->viewData('mantenimientos')->pluck('activo_id')->contains($activo->id));

        // Se encuentra en el historial de concluidos
        $concluidosResponse = $this->actingAs($this->admin)->get(route('mantenimientos.index', ['tab' => 'concluidos']));
        $this->assertTrue($concluidosResponse->viewData('mantenimientos')->pluck('activo_id')->contains($activo->id));
        $concluidosResponse->assertSee($activo->codigo_interno);
    }
}
