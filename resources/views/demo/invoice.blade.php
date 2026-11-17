<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice['number'] }}</title>
    <style>
        @page { margin: 42px; }
        body { color: #0f172a; font-family: "DejaVu Sans", sans-serif; font-size: 13px; }
        .header { border-bottom: 3px solid #2563eb; margin-bottom: 28px; padding-bottom: 16px; }
        .brand { color: #2563eb; font-size: 26px; font-weight: bold; }
        .demo { color: #b91c1c; font-size: 15px; font-weight: bold; margin-top: 6px; }
        .meta { margin: 18px 0 28px; width: 100%; }
        .meta td { padding: 6px 0; }
        .label { color: #64748b; width: 38%; }
        .total { background: #eff6ff; border-radius: 8px; font-size: 20px; padding: 18px; text-align: right; }
        .footer { bottom: 0; color: #64748b; font-size: 10px; position: fixed; text-align: center; width: 100%; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">HubSport</div>
        <div class="demo">DEMO — SIN VALOR FISCAL</div>
    </div>

    <h1>Factura {{ $invoice['number'] }}</h1>

    <table class="meta">
        <tr><td class="label">Fecha de emisión</td><td>{{ $invoice['issued_at'] }}</td></tr>
        <tr><td class="label">Descripción</td><td>{{ $invoice['description'] }}</td></tr>
        <tr><td class="label">Estado</td><td>{{ strtoupper($invoice['status']) }}</td></tr>
        <tr><td class="label">Plan</td><td>{{ $subscription['name'] }}</td></tr>
        <tr><td class="label">Próxima renovación</td><td>{{ $subscription['renews_at'] }}</td></tr>
    </table>

    <div class="total">
        Total: {{ number_format((float) $invoice['amount'], 2) }} {{ $invoice['currency'] }}
    </div>

    <div class="footer">
        DEMO — SIN VALOR FISCAL · Este documento no representa un cobro ni una obligación de pago.
    </div>
</body>
</html>
