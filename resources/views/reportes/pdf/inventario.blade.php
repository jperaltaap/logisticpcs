<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario y Existencias - LogisticPCS</title>
    <style>
        @page {
            margin: 25px 30px;
            size: A4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 8px;
        }
        .logo-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
        }
        .logo-title span {
            color: #0284c7;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            color: #1e293b;
            text-transform: uppercase;
        }
        .meta-info {
            font-size: 9px;
            color: #64748b;
            text-align: right;
        }
        .summary-boxes {
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            text-align: center;
        }
        .summary-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            font-size: 9px;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #0f172a;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .footer-signatures {
            margin-top: 40px;
            width: 100%;
            border-collapse: collapse;
        }
        .sig-line {
            border-top: 1px solid #475569;
            width: 80%;
            margin: 0 auto 5px auto;
        }
        .sig-text {
            font-size: 9px;
            text-align: center;
            color: #475569;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: middle;">
                <table style="border-collapse: collapse;">
                    <tr>
                        @if($empresa->getLogotipoBase64())
                            <td style="padding-right: 12px; vertical-align: middle;">
                                <img src="{{ $empresa->getLogotipoBase64() }}" alt="Logo" style="max-height: 48px; max-width: 140px;">
                            </td>
                        @endif
                        <td style="vertical-align: middle;">
                            <div class="logo-title">{{ $empresa->razon_social ?: ($empresa->sistema_nombre ?: 'LogisticPCS') }}</div>
                            @if($empresa->ruc)
                                <div style="font-size: 8.5px; color: #475569; font-weight: bold;">RUC: {{ $empresa->ruc }}</div>
                            @endif
                            <div style="font-size: 8px; color: #64748b; margin-top: 2px;">
                                {{ $empresa->direccion ? $empresa->direccion . ($empresa->ciudad ? ', ' . $empresa->ciudad : '') : 'Sistema de Gestión Logística & Control de Activos' }}
                                @if($empresa->telefono) · Tel: {{ $empresa->telefono }} @endif
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div class="doc-title">Reporte Consolidado de Inventario</div>
                @if(isset($proyectoActivo) && $proyectoActivo)
                    <div style="font-size: 9px; font-weight: bold; color: #0284c7; margin-top: 2px;">
                        PROYECTO: {{ $proyectoActivo->codigo }} - {{ $proyectoActivo->nombre }}
                    </div>
                @endif
                <div class="meta-info">Fecha de Emisión: {{ now()->format('d/m/Y H:i:s') }}</div>
                <div class="meta-info">Generado por: {{ auth()->user()->name ?? 'Administrador' }}</div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 10px; font-size: 9px; color: #475569;">
        <tr>
            <td><strong>Almacén Filtro:</strong> {{ $ubicacionNombre ?? 'Todos los Almacenes' }}</td>
            <td style="text-align: right;"><strong>Categoría Filtro:</strong> {{ $categoriaNombre ?? 'Todas las Categorías' }}</td>
        </tr>
    </table>

    <table class="summary-boxes">
        <tr>
            <td style="width: 32%; padding-right: 5px;">
                <div class="summary-card">
                    <div class="summary-label">Total Registros de Stock</div>
                    <div class="summary-value">{{ number_format($stocks->count()) }}</div>
                </div>
            </td>
            <td style="width: 32%; padding: 0 5px;">
                <div class="summary-card">
                    <div class="summary-label">Unidades Físicas Totales</div>
                    <div class="summary-value">{{ number_format($stocks->sum('cantidad_actual'), 2) }}</div>
                </div>
            </td>
            <td style="width: 32%; padding-left: 5px;">
                <div class="summary-card" style="border-left: 3px solid #f59e0b;">
                    <div class="summary-label">Ítems Bajo Stock Mínimo</div>
                    <div class="summary-value" style="color: #b45309;">
                        {{ $stocks->filter(fn($s) => $s->cantidad_actual <= ($s->articulo?->stock_minimo ?? 0))->count() }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">#</th>
                <th style="width: 12%;">SKU</th>
                <th style="width: 28%;">Descripción del Artículo</th>
                <th style="width: 14%;">Categoría</th>
                <th style="width: 16%;">Almacén / Ubicación</th>
                <th style="width: 8%; text-align: right;">Stock Act.</th>
                <th style="width: 8%; text-align: right;">Stock Mín.</th>
                <th style="width: 10%; text-align: center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $index => $item)
                @php
                    $isLow = $item->cantidad_actual <= ($item->articulo?->stock_minimo ?? 0);
                @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                    <td><strong>{{ $item->articulo?->codigo_sku ?? 'N/A' }}</strong></td>
                    <td>
                        {{ $item->articulo?->descripcion ?? '-' }}
                        @if($item->articulo?->marca)
                            <span style="color: #64748b;">({{ $item->articulo->marca }})</span>
                        @endif
                    </td>
                    <td>{{ $item->articulo?->categoria?->nombre ?? 'Sin Categoría' }}</td>
                    <td>{{ $item->ubicacion?->nombre ?? 'N/A' }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($item->cantidad_actual, 2) }}</td>
                    <td style="text-align: right; color: #64748b;">{{ number_format($item->articulo?->stock_minimo ?? 0, 2) }}</td>
                    <td style="text-align: center;">
                        @if($isLow)
                            <span class="badge badge-warning">ALERTA MÍNIMO</span>
                        @else
                            <span class="badge badge-success">ÓPTIMO</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #64748b;">
                        No se encontraron registros de inventario con los criterios seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-signatures">
        <tr>
            <td style="width: 33%; text-align: center;">
                <div class="sig-line"></div>
                <div class="sig-text">
                    <strong>RESPONSABLE DE ALMACÉN</strong><br>
                    Firma y Sello Oficial
                </div>
            </td>
            <td style="width: 34%; text-align: center;">
                <div class="sig-line"></div>
                <div class="sig-text">
                    <strong>SUPERVISOR DE INVENTARIOS</strong><br>
                    Auditoría / Residencia de Obra
                </div>
            </td>
            <td style="width: 33%; text-align: center;">
                <div class="sig-line"></div>
                <div class="sig-text">
                    <strong>{{ $empresa->representante_legal ?: 'GERENCIA / DIRECCIÓN' }}</strong><br>
                    {{ $empresa->cargo_representante ?: 'Representante Legal' }}
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 25px; border-top: 1px solid #cbd5e1; padding-top: 5px; font-size: 8px; color: #64748b;">
        <tr>
            <td style="width: 60%;">
                {{ $empresa->razon_social }} @if($empresa->ruc) · RUC: {{ $empresa->ruc }} @endif @if($empresa->direccion) · {{ $empresa->direccion }} @endif
            </td>
            <td style="width: 40%; text-align: right;">
                {{ $empresa->sistema_nombre ?: 'LogisticPCS' }} · Documento de Control Interno Confidencial
            </td>
        </tr>
    </table>

</body>
</html>
