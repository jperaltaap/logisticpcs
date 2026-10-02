<?php

namespace Database\Seeders;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\InventarioStock;
use App\Models\KardexMovimiento;
use App\Models\Kit;
use App\Models\MantenimientoCalibracion;
use App\Models\NotificacionAlerta;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use App\Services\InventarioStockService;
use Illuminate\Database\Seeder;

class SyncProyectosDataSeeder extends Seeder
{
    public function run(): void
    {
        $proyectos = Proyecto::where('estado', 'ACTIVO')->get();
        if ($proyectos->isEmpty()) {
            return;
        }

        $pIds = $proyectos->pluck('id')->toArray();
        $pCount = count($pIds);

        // 1. Ubicaciones
        // Almacenes centrales / globales compartidos
        Ubicacion::whereIn('codigo', ['ALM-CEN', 'TALLER-REP', 'CAMPO'])->update(['proyecto_id' => null]);

        // Crear o asegurar Centros de Acopio por cada proyecto activo
        $centrosPorProyecto = [
            1 => [
                'codigo' => 'CENTRO-LIMA',
                'nombre' => 'Centro de Acopio Lima Sur (Villa El Salvador)',
                'descripcion' => 'Centro de acopio y custodia de materiales/herramientas para el proyecto Expansión Troncal Fibra Óptica.',
                'tipo' => 'CENTRO_ACOPIO',
                'estado' => 'ACTIVO',
            ],
            2 => [
                'codigo' => 'CENTRO-CENTRO',
                'nombre' => 'Centro de Acopio Junín - Morococha',
                'descripcion' => 'Centro de acopio de obra y logística para mantenimiento de enlaces de microondas.',
                'tipo' => 'CENTRO_ACOPIO',
                'estado' => 'ACTIVO',
            ],
            3 => [
                'codigo' => 'CENTRO-VIRU',
                'nombre' => 'Centro de Acopio Virú - Chao',
                'descripcion' => 'Centro de acopio de campo para despliegue de redes FTTH rurales Norte.',
                'tipo' => 'CENTRO_ACOPIO',
                'estado' => 'ACTIVO',
            ],
        ];

        foreach ($proyectos as $pry) {
            if (isset($centrosPorProyecto[$pry->id])) {
                $cData = $centrosPorProyecto[$pry->id];
                Ubicacion::firstOrCreate(
                    ['codigo' => $cData['codigo']],
                    array_merge($cData, ['proyecto_id' => $pry->id])
                );
            }
        }

        // 2. Articulos
        $articulos = Articulo::all();
        foreach ($articulos as $idx => $art) {
            if (! $art->proyecto_id) {
                // Distribuir entre proyectos para que cada proyecto tenga sus artículos
                $art->update(['proyecto_id' => $pIds[$idx % $pCount]]);
            }
        }

        // 3. Kits
        $kits = Kit::all();
        foreach ($kits as $idx => $kit) {
            $pryId = $kit->proyecto_id ?: $pIds[$idx % $pCount];
            $ubId = $kit->ubicacion_id ?: Ubicacion::where('proyecto_id', $pryId)->value('id') ?: Ubicacion::value('id');
            $kit->update([
                'proyecto_id' => $pryId,
                'ubicacion_id' => $ubId,
            ]);
        }

        // 4. Inventario Stock
        // Sincronizar existencias físicas de todos los artículos serializados en base a los activos registrados
        app(InventarioStockService::class)->syncAllSerializedStock();

        $stocks = InventarioStock::with(['articulo', 'ubicacion'])->get();
        foreach ($stocks as $stock) {
            $targetPry = $stock->ubicacion?->proyecto_id ?? $stock->articulo?->proyecto_id ?? $pIds[0];
            $stock->update(['proyecto_id' => $targetPry]);
        }

        // 5. Kardex Movimientos
        $kardexs = KardexMovimiento::with(['despacho', 'ubicacion', 'articulo'])->get();
        foreach ($kardexs as $k) {
            if (! $k->proyecto_id) {
                $targetPry = $k->despacho?->proyecto_id ?? $k->ubicacion?->proyecto_id ?? $k->articulo?->proyecto_id ?? $pIds[0];
                $k->update(['proyecto_id' => $targetPry]);
            }
        }

        // 6. Mantenimientos / Calibraciones
        $mants = MantenimientoCalibracion::with('activo')->get();
        foreach ($mants as $m) {
            if (! $m->proyecto_id) {
                $targetPry = $m->activo?->proyecto_actual_id ?? $pIds[0];
                $m->update(['proyecto_id' => $targetPry]);
            }
        }

        // 7. Notificaciones / Alertas
        $alertas = NotificacionAlerta::all();
        foreach ($alertas as $idx => $alerta) {
            if (! $alerta->proyecto_id) {
                $alerta->update(['proyecto_id' => $pIds[$idx % $pCount]]);
            }
        }

        // 8. Asegurar que personal y activos estén asignados a los proyectos
        $personal = Personal::all();
        foreach ($personal as $idx => $per) {
            if (! $per->proyecto_id) {
                $per->update(['proyecto_id' => $pIds[$idx % $pCount]]);
            }
        }

        $activos = Activo::all();
        foreach ($activos as $idx => $act) {
            if (! $act->proyecto_actual_id) {
                $act->update(['proyecto_actual_id' => $pIds[$idx % $pCount]]);
            }
        }
    }
}
