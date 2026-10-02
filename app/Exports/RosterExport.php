<?php

namespace App\Exports;

use App\Models\RosterTurno;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RosterExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?int $proyectoId = null,
        protected ?int $mes = null,
        protected ?int $anio = null,
        protected ?string $grupoGuardia = null,
        protected ?string $condicionLaboral = null
    ) {}

    public function collection(): Collection
    {
        return RosterTurno::with(['personal.proyecto', 'proyecto'])
            ->when($this->proyectoId, fn ($q) => $q->where('proyecto_id', $this->proyectoId))
            ->when($this->mes, fn ($q) => $q->whereMonth('fecha', $this->mes))
            ->when($this->anio, fn ($q) => $q->whereYear('fecha', $this->anio))
            ->when($this->grupoGuardia, fn ($q) => $q->where('grupo_guardia', $this->grupoGuardia))
            ->when($this->condicionLaboral, fn ($q) => $q->where('condicion_laboral', $this->condicionLaboral))
            ->orderBy('fecha', 'asc')
            ->orderBy('personal_id', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID Turno',
            'DNI / Documento',
            'Apellidos y Nombres',
            'Cargo',
            'Proyecto Base',
            'Grupo Guardia',
            'Fecha Turno',
            'Condición Laboral',
            'Observaciones',
        ];
    }

    /**
     * @param  RosterTurno  $turno
     */
    public function map($turno): array
    {
        $personal = $turno->personal;

        return [
            $turno->id,
            $personal?->dni ?? '-',
            $personal ? "{$personal->apellidos}, {$personal->nombres}" : '-',
            $personal?->cargo ?? '-',
            $turno->proyecto?->nombre ?? ($personal?->proyecto?->nombre ?? '-'),
            $turno->grupo_guardia ?? '-',
            $turno->fecha ? $turno->fecha->format('Y-m-d') : '-',
            str_replace('_', ' ', $turno->condicion_laboral),
            $turno->observaciones ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF065F46'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Roster 14x7';
    }
}
