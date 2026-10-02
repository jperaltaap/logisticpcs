<?php

namespace App\Exports;

use App\Models\Activo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivosExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?string $estadoOperativo = null,
        protected ?string $condicionPrestamo = null,
        protected ?int $ubicacionId = null,
        protected ?int $proyectoId = null,
        protected ?string $search = null
    ) {}

    public function collection(): Collection
    {
        return Activo::with(['articulo.categoria', 'ubicacion', 'proyecto', 'responsable', 'cuadrilla'])
            ->when($this->estadoOperativo, fn ($q) => $q->where('estado_operativo', $this->estadoOperativo))
            ->when($this->condicionPrestamo, fn ($q) => $q->where('condicion_prestamo', $this->condicionPrestamo))
            ->when($this->ubicacionId, fn ($q) => $q->where('ubicacion_actual_id', $this->ubicacionId))
            ->when($this->proyectoId, fn ($q) => $q->where('proyecto_actual_id', $this->proyectoId))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('codigo_interno', 'like', "%{$this->search}%")
                        ->orWhere('numero_serie', 'like', "%{$this->search}%")
                        ->orWhereHas('articulo', function ($qa) {
                            $qa->where('descripcion', 'like', "%{$this->search}%")
                                ->orWhere('marca', 'like', "%{$this->search}%");
                        });
                });
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            'Placa / Código Interno',
            'Número de Serie',
            'Artículo / Equipo',
            'Categoría',
            'Marca',
            'Modelo',
            'Almacén Actual',
            'Proyecto Asignado',
            'Custodio / Responsable',
            'Cuadrilla Actual',
            'Estado Operativo',
            'Condición Préstamo',
            'Fecha Última Asignación',
        ];
    }

    /**
     * @param  Activo  $activo
     */
    public function map($activo): array
    {
        $articulo = $activo->articulo;
        $custodio = $activo->responsable;

        return [
            $activo->codigo_interno,
            $activo->numero_serie ?? '-',
            $articulo?->descripcion ?? '-',
            $articulo?->categoria?->nombre ?? '-',
            $articulo?->marca ?? '-',
            $articulo?->modelo ?? '-',
            $activo->ubicacion?->nombre ?? '-',
            $activo->proyecto?->nombre ?? '-',
            $custodio ? "{$custodio->apellidos}, {$custodio->nombres}" : '-',
            $activo->cuadrilla?->nombre ?? '-',
            $activo->estado_operativo,
            $activo->condicion_prestamo,
            $activo->fecha_ultima_asignacion ? $activo->fecha_ultima_asignacion->format('Y-m-d H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF312E81'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Padrón de Activos';
    }
}
