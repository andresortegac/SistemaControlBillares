<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $venta->folio }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', monospace;
        }
        body {
            width: 80mm;
            padding: 5mm;
            font-size: 12px;
        }
        .center {
            text-align: center;
        }
        .header {
            margin-bottom: 10px;
        }
        .header h2 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .info {
            margin-bottom: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            text-align: left;
            padding: 3px 0;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 10px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
        }
        @media print {
            body {
                width: 80mm;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header center">
        <h2>BILLAR PRO</h2>
        <p>Ticket de Venta</p>
    </div>
    
    <div class="divider"></div>
    
    <div class="info">
        <div class="info-row">
            <span>Folio:</span>
            <span><strong>{{ $venta->folio }}</strong></span>
        </div>
        <div class="info-row">
            <span>Fecha:</span>
            <span>{{ $venta->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span>Vendedor:</span>
            <span>{{ $venta->usuario->name }}</span>
        </div>
        <div class="info-row">
            <span>Cliente:</span>
            <span>{{ $venta->cliente ? $venta->cliente->nombre : 'General' }}</span>
        </div>
    </div>
    
    <div class="divider"></div>
    
    <table>
        <thead>
            <tr>
                <th>Cant</th>
                <th>Descripción</th>
                <th class="text-right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
            <tr>
                <td>{{ $detalle->cantidad }}</td>
                <td>
                    @if($detalle->producto)
                        {{ Str::limit($detalle->producto->nombre, 20) }}
                    @else
                        {{ Str::limit($detalle->descripcion, 20) }}
                    @endif
                </td>
                <td class="text-right">${{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="divider"></div>
    
    <div class="totals">
        <div class="totals-row">
            <span>Subtotal:</span>
            <span>${{ number_format($venta->subtotal, 2) }}</span>
        </div>
        @if($venta->descuento > 0)
        <div class="totals-row">
            <span>Descuento:</span>
            <span>-${{ number_format($venta->descuento, 2) }}</span>
        </div>
        @endif
        <div class="totals-row" style="font-size: 14px; font-weight: bold; margin-top: 5px;">
            <span>TOTAL:</span>
            <span>${{ number_format($venta->total, 2) }}</span>
        </div>
        @if($venta->efectivo_recibido)
        <div class="totals-row" style="margin-top: 5px;">
            <span>Efectivo:</span>
            <span>${{ number_format($venta->efectivo_recibido, 2) }}</span>
        </div>
        <div class="totals-row">
            <span>Cambio:</span>
            <span>${{ number_format($venta->cambio, 2) }}</span>
        </div>
        @endif
    </div>
    
    <div class="divider"></div>
    
    <div class="footer">
        <p>¡Gracias por su preferencia!</p>
        <p style="margin-top: 10px;">---</p>
    </div>
    
    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">
            🖨️ Imprimir
        </button>
    </div>
</body>
</html>
