<?php

namespace App\Exports;

use App\Models\InventarioStock;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarioStockExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?int $ubicacionId = null,
        protected ?int $categoriaId = null,
        protected ?string $search = null
    ) {}

    public function collection(): Collection
    {
        $proyectoActivoId = session('proyecto_activo_id');

        return InventarioStock::with(['articulo.categoria', 'ubicacion'])
            ->when($proyectoActivoId, function ($q) use ($proyectoActivoId) {
                $q->where(function ($sub) use ($proyectoActivoId) {
                    $sub->where('inventario_stock.proyecto_id', $proyectoActivoId)
                        ->orWhereHas('articulo', fn ($aq) => $aq->where('proyecto_id', $proyectoActivoId))
                        ->orWhereHas('ubicacion', fn ($uq) => $uq->where('proyecto_id', $proyectoActivoId));
                });
            })
            ->when($this->ubicacionId, fn ($q) => $q->where('ubicacion_id', $this->ubicacionId))
            ->when($this->categoriaId, fn ($q) => $q->whereHas('articulo', fn ($qa) => $qa->where('categoria_id', $this->categoriaId)))
            ->when($this->search, function ($q) {
                $q->whereHas('articulo', function ($qa) {
                    $qa->where('codigo_sku', 'like', "%{$this->search}%")
                        ->orWhere('descripcion', 'like', "%{$this->search}%")
                        ->orWhere('marca', 'like', "%{$this->search}%");
                });
            })
            ->get();
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Artículo / Descripción',
            'Categoría',
            'Marca',
            'Modelo',
            'Unidad Medida',
            'Tipo',
            'Control Serie',
            'Almacén / Ubicación',
            'Stock Actual',
            'Stock Mínimo',
            'Estado Stock',
        ];
    }

    /**
     * @param  InventarioStock  $stock
     */
    public function map($stock): array
    {
        $articulo = $stock->articulo;
        $esBajoStock = $stock->cantidad_actual <= ($articulo?->stock_minimo ?? 0);

        return [
            $articulo?->codigo_sku ?? 'N/A',
            $articulo?->descripcion ?? 'N/A',
            $articulo?->categoria?->nombre ?? 'Sin Categoría',
            $articulo?->marca ?? '-',
            $articulo?->modelo ?? '-',
            $articulo?->unidad_medida ?? 'UND',
            $articulo?->tipo_articulo ?? 'BIEN_CONTROLADO',
            ($articulo?->control_serie ?? false) ? 'SI' : 'NO',
            $stock->ubicacion?->nombre ?? 'N/A',
            $stock->cantidad_actual,
            $articulo?->stock_minimo ?? 0,
            $esBajoStock ? 'BAJO STOCK MÍNIMO' : 'NORMAL / ÓPTIMO',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A365D'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Inventario y Existencias';
    }
}
