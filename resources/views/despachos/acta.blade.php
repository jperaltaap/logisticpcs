<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Entrega - {{ $despacho->numero_guia }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #1e293b;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .controls {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            background-color: #0284c7;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-secondary {
            background-color: #64748b;
        }

        /* Documento Formato A4 */
        .acta-document {
            width: 210mm;
            min-height: 297mm;
            background: #ffffff;
            padding: 20mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .company-title {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 0.5px;
        }

        .company-sub {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }

        .document-title {
            text-align: right;
        }

        .doc-name {
            font-size: 15px;
            font-weight: 800;
            color: #0284c7;
            text-transform: uppercase;
        }

        .doc-number {
            font-size: 16px;
            font-weight: 900;
            font-family: monospace;
            color: #0f172a;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .info-item {
            margin-bottom: 3px;
        }

        .info-label {
            font-weight: 700;
            color: #475569;
        }

        .info-value {
            font-weight: 600;
            color: #0f172a;
        }

        .table-items {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin-bottom: 20px;
        }

        .table-items th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #0f172a;
        }

        .table-items td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            color: #1e293b;
        }

        .table-items tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .disclaimer {
            font-size: 9.5px;
            color: #475569;
            background-color: #f1f5f9;
            border-left: 3px solid #0284c7;
            padding: 8px 12px;
            margin-bottom: 25px;
            line-height: 1.4;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 15px;
        }

        .sig-box {
            border-top: 1px solid #94a3b8;
            padding-top: 8px;
            text-align: center;
            font-size: 10.5px;
        }

        .sig-img {
            max-height: 70px;
            margin-bottom: 5px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }

            .controls {
                display: none;
            }

            .acta-document {
                box-shadow: none;
                border: none;
                padding: 0;
                width: 100%;
                min-height: auto;
            }

            @page {
                size: A4;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>

    <div class="controls">
        <button onclick="window.print()" class="btn">
            🖨️ Imprimir Acta
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            ✕ Cerrar
        </button>
    </div>

    <div class="acta-document">
        <div>
            <!-- Header -->
            @php $emp = $empresa ?? \App\Models\EmpresaConfig::instancia(); @endphp
            <div class="header">
                <div style="display: flex; align-items: center; gap: 12px;">
                    @if($emp->logotipo_url)
                        <img src="{{ $emp->logotipo_url }}" alt="Logo" style="max-height: 48px; max-width: 140px; object-fit: contain;" onerror="this.style.display='none'">
                    @endif
                    <div>
                        <div class="company-title">{{ $emp->razon_social ?: ($emp->sistema_nombre ?: 'LogisticPCS') }}</div>
                        <div class="company-sub">
                            @if($emp->ruc) RUC: {{ $emp->ruc }} · @endif
                            {{ $emp->direccion ? $emp->direccion . ($emp->ciudad ? ', ' . $emp->ciudad : '') : 'SISTEMA DE GESTIÓN LOGÍSTICA & CONTROL DE ACTIVOS' }}
                        </div>
                    </div>
                </div>
                <div class="document-title">
                    <div class="doc-name">Acta de Entrega / Vale de Despacho</div>
                    <div class="doc-number">{{ $despacho->numero_guia }}</div>
                </div>
            </div>

            <!-- Datos Generales -->
            <div class="info-grid">
                <div>
                    <div class="info-item"><span class="info-label">Proyecto:</span> <span class="info-value">{{ $despacho->proyecto->codigo }} - {{ $despacho->proyecto->nombre }}</span></div>
                    <div class="info-item"><span class="info-label">Cliente:</span> <span class="info-value">{{ $despacho->proyecto->cliente }}</span></div>
                    <div class="info-item"><span class="info-label">Receptor / Custodio:</span> <span class="info-value">{{ $despacho->personal->nombre_completo ?? 'Personal de Campo' }}</span></div>
                    <div class="info-item"><span class="info-label">DNI / CE:</span> <span class="info-value">{{ $despacho->personal->dni ?? 'N/A' }}</span> · <span class="info-label">Cargo:</span> <span class="info-value">{{ $despacho->personal->cargo ?? 'Técnico' }}</span></div>
                </div>
                <div>
                    <div class="info-item"><span class="info-label">Almacén Origen:</span> <span class="info-value">{{ $despacho->ubicacionOrigen->nombre }}</span></div>
                    <div class="info-item"><span class="info-label">Tipo de Movimiento:</span> <span class="info-value">{{ str_replace('_', ' ', $despacho->tipo_movimiento) }}</span></div>
                    <div class="info-item"><span class="info-label">Fecha de Despacho:</span> <span class="info-value">{{ $despacho->fecha_despacho?->format('d/m/Y H:i') }}</span></div>
                    <div class="info-item"><span class="info-label">Retorno Proyectado:</span> <span class="info-value">{{ $despacho->fecha_compromiso_retorno?->format('d/m/Y') ?? 'No aplica' }}</span></div>
                </div>
            </div>

            <!-- Tabla de Bienes Entregados -->
            <table class="table-items">
                <thead>
                    <tr>
                        <th style="width: 25px;" class="text-center">#</th>
                        <th style="width: 90px;">Código SKU</th>
                        <th>Descripción del Bien / Herramienta</th>
                        <th>Identificador (Código QR / Serie)</th>
                        <th style="width: 70px;" class="text-right">Cantidad</th>
                        <th style="width: 50px;" class="text-center">Und</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($despacho->detalles as $idx => $det)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td style="font-family: monospace; font-weight: bold;">{{ $det->articulo->codigo_sku }}</td>
                            <td>
                                <strong>{{ $det->articulo->descripcion }}</strong>
                                @if($det->kit)
                                    <span style="font-size: 9px; color: #64748b;">(De Kit: {{ $det->kit->nombre_kit }})</span>
                                @endif
                            </td>
                            <td style="font-family: monospace;">
                                @if($det->activo)
                                    <strong>{{ $det->activo->codigo_interno }}</strong> (SN: {{ $det->activo->numero_serie ?? 'S/N' }})
                                @else
                                    Consumible / A granel
                                @endif
                            </td>
                            <td class="text-right" style="font-weight: bold;">{{ number_format($det->cantidad, 0) }}</td>
                            <td class="text-center">{{ $det->articulo->unidad_medida }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Declaración de Conformidad -->
            <div class="disclaimer">
                <strong>DECLARACIÓN DE RECEPCIÓN Y CUSTODIA:</strong> El personal receptor declara haber recibido en perfectas condiciones físicas y operativas las herramientas, equipos y materiales detallados en la presente acta. Asume la responsabilidad de su correcto uso, custodia y devolución oportuna según las directivas internas de seguridad y logística de la empresa.
            </div>
        </div>

        <!-- Bloque de Firmas -->
        <div class="signatures">
            <div class="sig-box">
                @if($despacho->firma_digital_base64)
                    <img src="{{ $despacho->firma_digital_base64 }}" alt="Firma Receptor" class="sig-img">
                @else
                    <div style="height: 60px;"></div>
                @endif
                <div><strong>{{ $despacho->personal->nombre_completo ?? 'FIRMA DEL RECEPTOR' }}</strong></div>
                <div>DNI: {{ $despacho->personal->dni ?? '_____________' }}</div>
                <div style="font-size: 9px; color: #64748b;">Personal Receptor / Custodio</div>
            </div>

            <div class="sig-box">
                <div style="height: 60px;"></div>
                <div><strong>{{ $despacho->usuarioRegistro->name ?? 'RESPONSABLE DE LOGÍSTICA' }}</strong></div>
                <div>LOGÍSTICA / DESPACHO DE ALMACÉN</div>
                <div style="font-size: 9px; color: #64748b;">Emitido vía LogisticPCS</div>
            </div>
        </div>

        <div style="margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 6px; font-size: 8.5px; color: #64748b; display: flex; justify-content: space-between;">
            <div>
                {{ $emp->razon_social }} @if($emp->ruc) · RUC: {{ $emp->ruc }} @endif @if($emp->telefono) · Tel: {{ $emp->telefono }} @endif
            </div>
            <div>
                {{ $emp->sistema_nombre ?: 'LogisticPCS' }} · Documento Oficial de Custodia Patrimonial
            </div>
        </div>
    </div>

</body>
</html>
