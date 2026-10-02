<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta QR - {{ $activo->codigo_interno }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
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
            padding: 8px 16px;
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

        /* Dimensiones exactas de etiqueta térmica estándar 70mm x 40mm */
        .etiqueta-sticker {
            width: 76mm;
            height: 48mm;
            background: #ffffff;
            border: 1px dashed #94a3b8;
            border-radius: 4px;
            padding: 3mm 4mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #000000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        .header-sticker {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #000000;
            padding-bottom: 1.5mm;
        }

        .company-name {
            font-size: 9pt;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .category-name {
            font-size: 6.5pt;
            font-weight: 600;
            text-transform: uppercase;
            color: #333333;
        }

        .body-sticker {
            display: flex;
            align-items: center;
            gap: 3mm;
            margin: 1.5mm 0;
            flex-grow: 1;
        }

        .qr-box {
            width: 25mm;
            height: 25mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-box svg {
            width: 100% !important;
            height: 100% !important;
        }

        .info-box {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.2;
        }

        .codigo-interno {
            font-size: 11pt;
            font-weight: 900;
            font-family: monospace;
            color: #000000;
        }

        .articulo-desc {
            font-size: 7.5pt;
            font-weight: 700;
            color: #111111;
            margin: 1mm 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .articulo-meta {
            font-size: 6.5pt;
            color: #333333;
        }

        .footer-sticker {
            border-top: 1px solid #000000;
            padding-top: 1mm;
            display: flex;
            justify-content: space-between;
            font-size: 6pt;
            font-family: monospace;
        }

        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
                justify-content: flex-start;
            }

            .controls {
                display: none;
            }

            .etiqueta-sticker {
                border: none;
                box-shadow: none;
                page-break-after: avoid;
            }

            @page {
                size: 80mm 50mm;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div class="controls">
        <button onclick="window.print()" class="btn">
            🖨️ Imprimir Etiqueta
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            ✕ Cerrar
        </button>
    </div>

    <!-- Etiqueta física para pegar en el activo -->
    <div class="etiqueta-sticker">
        <div class="header-sticker">
            <span class="company-name">LOGISTIC PCS</span>
            <span class="category-name">{{ $activo->articulo->categoria->nombre ?? 'ACTIVO FIJO' }}</span>
        </div>

        <div class="body-sticker">
            <div class="qr-box">
                {!! $qrSvg !!}
            </div>
            <div class="info-box">
                <div class="codigo-interno">{{ $activo->codigo_interno }}</div>
                <div class="articulo-desc">{{ $activo->articulo->descripcion }}</div>
                <div class="articulo-meta">
                    <strong>SKU:</strong> {{ $activo->articulo->codigo_sku }}<br>
                    <strong>SERIE:</strong> {{ $activo->numero_serie ?? 'N/A' }}<br>
                    <strong>MARCA:</strong> {{ $activo->articulo->marca ?? 'S/M' }}
                </div>
            </div>
        </div>

        <div class="footer-sticker">
            <span>REG: {{ $activo->fecha_ingreso?->format('d/m/Y') }}</span>
            <span>PROPIEDAD DE LA EMPRESA</span>
        </div>
    </div>

</body>
</html>
