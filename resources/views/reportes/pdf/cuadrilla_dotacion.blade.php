<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de Cargo - Cuadrilla {{ $cuadrilla->codigo_cuadrilla }} - LogisticPCS</title>
    <style>
        @page {
            margin: 25px 30px;
            size: A4 portrait;
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
            margin-bottom: 12px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 6px;
        }
        .logo-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
        }
        .logo-title span {
            color: #3b82f6;
        }
        .doc-title {
            font-size: 13px;
            font-weight: bold;
            text-align: right;
            color: #1e293b;
            text-transform: uppercase;
        }
        .meta-info {
            font-size: 8px;
            color: #64748b;
            text-align: right;
        }
        .cuadrilla-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .cuadrilla-card table {
            width: 100%;
            font-size: 9px;
        }
        .cuadrilla-card td {
            padding: 2px 4px;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-top: 12px;
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 8.5px;
            text-align: left;
            padding: 5px 6px;
            border: 1px solid #1e293b;
        }
        table.data-table td {
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 4px;
            font-size: 7.5px;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-info {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .declaration {
            background-color: #fefce8;
            border: 1px solid #fef08a;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 8px;
            color: #713f12;
            margin-top: 15px;
            margin-bottom: 25px;
            line-height: 1.35;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .sig-box {
            text-align: center;
            vertical-align: bottom;
            padding: 0 10px;
        }
        .sig-line {
            border-top: 1px solid #475569;
            width: 85%;
            margin: 0 auto 4px auto;
        }
        .sig-name {
            font-size: 8.5px;
            font-weight: bold;
            color: #0f172a;
        }
        .sig-role {
            font-size: 8px;
            color: #64748b;
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
                            <td style="padding-right: 10px; vertical-align: middle;">
                                <img src="{{ $empresa->getLogotipoBase64() }}" alt="Logo" style="max-height: 44px; max-width: 130px;">
                            </td>
                        @endif
                        <td style="vertical-align: middle;">
                            <div class="logo-title">{{ $empresa->razon_social ?: ($empresa->sistema_nombre ?: 'LogisticPCS') }}</div>
                            @if($empresa->ruc)
                                <div style="font-size: 8px; color: #475569; font-weight: bold;">RUC: {{ $empresa->ruc }}</div>
                            @endif
                            <div style="font-size: 7.5px; color: #64748b;">
                                {{ $empresa->direccion ? $empresa->direccion . ($empresa->ciudad ? ', ' . $empresa->ciudad : '') : 'Gestión Logística & Asignación de Cuadrillas' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div class="doc-title">{{ $cuadrilla->codigo_cuadrilla }}</div>
                <div style="font-size: 8.5px; color: #3b82f6; font-weight: bold;">HOJA OFICIAL DE CARGO Y ASIGNACIÓN</div>
                <div class="meta-info">Fecha de Emisión: {{ now()->format('d/m/Y H:i') }}</div>
                <div class="meta-info">Estado Cuadrilla: {{ $cuadrilla->estado }}</div>
            </td>
        </tr>
    </table>

    <div class="cuadrilla-card">
        <table>
            <tr>
                <td style="width: 25%;"><strong>Nombre Cuadrilla:</strong></td>
                <td style="width: 25%;">{{ $cuadrilla->nombre }}</td>
                <td style="width: 25%;"><strong>Proyecto Asignado:</strong></td>
                <td style="width: 25%;">{{ $cuadrilla->proyecto?->nombre ?? 'Sin Proyecto' }}</td>
            </tr>
            <tr>
                <td><strong>Líder Responsable:</strong></td>
                <td>{{ $cuadrilla->lider ? "{$cuadrilla->lider->apellidos}, {$cuadrilla->lider->nombres}" : 'No asignado' }}</td>
                <td><strong>Régimen de Trabajo:</strong></td>
                <td><span class="badge badge-info">{{ $cuadrilla->regimen_laboral ?? '14x7' }}</span></td>
            </tr>
            <tr>
                <td><strong>DNI / Doc. Líder:</strong></td>
                <td>{{ $cuadrilla->lider?->dni ?? '-' }}</td>
                <td><strong>Dotación Activa:</strong></td>
                <td>{{ $miembrosActivos->count() }} personas</td>
            </tr>
        </table>
    </div>

    <div class="section-title">1. Nómina y Dotación del Personal de Campo</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">DNI / CE</th>
                <th style="width: 35%;">Apellidos y Nombres</th>
                <th style="width: 25%;">Rol en Cuadrilla</th>
                <th style="width: 20%;">F. Incorporación</th>
            </tr>
        </thead>
        <tbody>
            @forelse($miembrosActivos as $index => $m)
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                    <td><strong>{{ $m->personal?->dni ?? '-' }}</strong></td>
                    <td>{{ $m->personal ? "{$m->personal->apellidos}, {$m->personal->nombres}" : '-' }}</td>
                    <td>
                        @if($m->rol_en_cuadrilla === 'LIDER DE CUADRILLA')
                            <strong>{{ $m->rol_en_cuadrilla }}</strong>
                        @else
                            {{ $m->rol_en_cuadrilla }}
                        @endif
                    </td>
                    <td>{{ $m->fecha_incorporacion ? \Carbon\Carbon::parse($m->fecha_incorporacion)->format('d/m/Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b;">No registra miembros activos en la dotación.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">2. Equipos y Activos Serializados en Custodia Actual</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 18%;">Placa / Cód. Interno</th>
                <th style="width: 18%;">N° Serie Fabricante</th>
                <th style="width: 34%;">Descripción del Equipo</th>
                <th style="width: 15%;">Marca / Modelo</th>
                <th style="width: 10%; text-align: center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($activosCustodia as $index => $act)
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                    <td><strong>{{ $act->codigo_interno }}</strong></td>
                    <td>{{ $act->numero_serie ?? '-' }}</td>
                    <td>{{ $act->articulo?->descripcion ?? '-' }}</td>
                    <td>{{ $act->articulo?->marca ?? '-' }} {{ $act->articulo?->modelo ?? '' }}</td>
                    <td style="text-align: center;">
                        <span class="badge badge-success">{{ $act->estado_operativo }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b;">La cuadrilla no tiene activos serializados asignados actualmente.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="declaration">
        <strong>DECLARACIÓN DE CUSTODIA Y COMPROMISO:</strong> El líder de cuadrilla y los integrantes suscritos certifican haber recibido y verificado los activos y herramientas descritos en este documento, los cuales se encuentran operativos y en óptimo estado de conservación para la ejecución de las labores en obra bajo el régimen asignado. La cuadrilla asume la responsabilidad patrimonial del resguardo, uso adecuado y devolución correspondiente al culminar el ciclo de faena.
    </div>

    <table class="signatures-table">
        <tr>
            <td class="sig-box" style="width: 33%;">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $cuadrilla->lider ? "{$cuadrilla->lider->nombres} {$cuadrilla->lider->apellidos}" : 'LÍDER DE CUADRILLA' }}</div>
                <div class="sig-role">Líder Receptor (Firma y DNI)</div>
            </td>
            <td class="sig-box" style="width: 33%;">
                <div class="sig-line"></div>
                <div class="sig-name">{{ auth()->user()->name ?? 'ALMACÉN CENTRAL' }}</div>
                <div class="sig-role">Responsable de Almacén (Emisor)</div>
            </td>
            <td class="sig-box" style="width: 33%;">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $empresa->representante_legal ?: 'SUPERVISOR / RESIDENTE' }}</div>
                <div class="sig-role">{{ $empresa->cargo_representante ?: 'V°B° Frente de Obra' }}</div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 25px; border-top: 1px solid #cbd5e1; padding-top: 4px; font-size: 7.5px; color: #64748b;">
        <tr>
            <td style="width: 60%;">
                {{ $empresa->razon_social }} @if($empresa->ruc) · RUC {{ $empresa->ruc }} @endif @if($empresa->direccion) · {{ $empresa->direccion }} @endif
            </td>
            <td style="width: 40%; text-align: right;">
                {{ $empresa->sistema_nombre ?: 'LogisticPCS' }} · Hoja Oficial de Dotación y Custodia
            </td>
        </tr>
    </table>

</body>
</html>
