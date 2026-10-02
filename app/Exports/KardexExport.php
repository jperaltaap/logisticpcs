<?php

namespace App\Exports;

use App\Models\KardexMovimiento;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KardexExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?int $articuloId = null,
        protected ?int $ubicacionId = null,
        protected ?string $tipoMovimiento = null,
        protected ?string $fechaInicio = null,
        protected ?string $fechaFin = null
    ) {}

    public function collection(): Collection
    {
        $proyectoActivoId = session('proyecto_activo_id');

        return KardexMovimiento::with(['articulo', 'ubicacion', 'despacho', 'usuario'])
            ->when($proyectoActivoId, fn ($q) => $q->where('proyecto_id', $proyectoActivoId))
            ->when($this->articuloId, fn ($q) => $q->where('articulo_id', $this->articuloId))
            ->when($this->ubicacionId, fn ($q) => $q->where('ubicacion_id', $this->ubicacionId))
            ->when($this->tipoMovimiento, fn ($q) => $q->where('tipo_movimiento', $this->tipoMovimiento))
            ->when($this->fechaInicio, fn ($q) => $q->whereDate('fecha_movimiento', '>=', $this->fechaInicio))
            ->when($this->fechaFin, fn ($q) => $q->whereDate('fecha_movimiento', '<=', $this->fechaFin))
            ->orderBy('fecha_movimiento', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID Mov.',
            'Fecha y Hora',
            'N° Guía / Vale',
            'Tipo de Movimiento',
            'Código SKU',
            'Descripción Artículo',
            'Almacén / Ubicación',
            'Cantidad Operada',
            'Saldo Anterior',
            'Saldo Posterior',
            'Usuario Responsable',
            'Motivo / Observación',
        ];
    }

    /**
     * @param  KardexMovimiento  $mov
     */
    public function map($mov): array
    {
        return [
            $mov->id,
            $mov->fecha_movimiento ? $mov->fecha_movimiento->format('Y-m-d H:i') : '-',
            $mov->despacho?->numero_guia ?? '-',
            str_replace('_', ' ', $mov->tipo_movimiento),
            $mov->articulo?->codigo_sku ?? '-',
            $mov->articulo?->descripcion ?? '-',
            $mov->ubicacion?->nombre ?? '-',
            $mov->cantidad,
            $mov->stock_anterior,
            $mov->stock_posterior,
            $mov->usuario?->name ?? 'Sistema',
            $mov->motivo ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E293B'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Movimientos de Kardex';
    }
}
