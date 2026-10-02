<?php

namespace Database\Seeders;

use App\Models\Activo;
use App\Models\Articulo;
use App\Models\Personal;
use App\Models\Proyecto;
use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

class ActivoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $almacenCentral = Ubicacion::where('codigo', 'ALM-CEN-01')->first() ?? Ubicacion::first();
        $almacenNorte = Ubicacion::where('codigo', 'ALM-NORTE')->first() ?? $almacenCentral;
        $tallerCentral = Ubicacion::where('codigo', 'TAL-REP-01')->first() ?? $almacenCentral;

        $proyectoMetro = Proyecto::where('codigo', 'PRY-2026-001')->first();
        $proyectoRed = Proyecto::where('codigo', 'PRY-2026-002')->first();

        $tecnicoEmpalme = Personal::where('cargo', 'Técnico Empalmador de Fibra Óptica')->first() ?? Personal::first();
        $tecnicoLiniero = Personal::where('cargo', 'Liniero Electricista de Alta Tensión')->first() ?? Personal::first();

        $articulosSerializados = Articulo::where('control_serie', true)->get();

        $contador = 1;

        foreach ($articulosSerializados as $articulo) {
            // Generate 2 or 3 physical assets per serialized article
            $cantidad = match ($articulo->codigo_sku) {
                'SKU-ARN-001' => 6,
                'SKU-FUS-001', 'SKU-TAL-001', 'SKU-MULT-001', 'SKU-CORT-001' => 3,
                default => 2,
            };

            for ($i = 1; $i <= $cantidad; $i++) {
                $codigoInterno = sprintf('ACT-%05d', $contador);
                $numeroSerie = strtoupper(substr($articulo->marca, 0, 3)).'-'.date('Y').'-'.sprintf('%04d', $contador + 100);

                // Assign status and location
                $estadoOperativo = 'OPERATIVO';
                $condicionPrestamo = 'DISPONIBLE';
                $ubicacionActual = $almacenCentral;
                $responsable = null;
                $proyecto = null;
                $fechaAsignacion = null;

                if ($i === 2 && $tecnicoEmpalme && $proyectoMetro) {
                    $condicionPrestamo = 'PRESTADO_CAMPO';
                    $ubicacionActual = $almacenNorte;
                    $responsable = $tecnicoEmpalme;
                    $proyecto = $proyectoMetro;
                    $fechaAsignacion = now()->subDays(5);
                } elseif ($i === 3 && $articulo->codigo_sku === 'SKU-FUS-001') {
                    $estadoOperativo = 'EN_MANTENIMIENTO';
                    $condicionPrestamo = 'DISPONIBLE';
                    $ubicacionActual = $tallerCentral;
                }

                Activo::firstOrCreate(
                    ['codigo_interno' => $codigoInterno],
                    [
                        'articulo_id' => $articulo->id,
                        'numero_serie' => $numeroSerie,
                        'ubicacion_actual_id' => $ubicacionActual->id,
                        'responsable_personal_id' => $responsable?->id,
                        'cuadrilla_actual_id' => null,
                        'proyecto_actual_id' => $proyecto?->id,
                        'estado_operativo' => $estadoOperativo,
                        'condicion_prestamo' => $condicionPrestamo,
                        'fecha_ingreso' => now()->subMonths(rand(1, 12))->format('Y-m-d'),
                        'fecha_ultima_asignacion' => $fechaAsignacion,
                        'fecha_ultimo_retorno' => null,
                        'observaciones' => "Activo registrado con etiqueta QR y código {$codigoInterno}.",
                    ]
                );

                $contador++;
            }
        }
    }
}
