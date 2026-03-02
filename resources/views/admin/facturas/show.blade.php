@extends('layouts.admin')

@section('title', 'Factura ' . $factura->numero)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Factura {{ $factura->numero }}</h3>
        <p class="text-muted mb-0">Pedido #{{ $factura->id_pedido }} — Cliente ID {{ $factura->id_usuario }}</p>
    </div>
    <span class="badge bg-dark text-uppercase">{{ $factura->estado }}</span>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-receipt me-2"></i>Detalle de la factura
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Talla</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($factura->detalles as $detalle)
                        <tr>
                            <td>{{ optional($detalle->producto)->nombre ?? '(producto eliminado)' }}</td>
                            <td class="text-center">{{ optional($detalle->talla)->nombre ?? '—' }}</td>
                            <td class="text-center">{{ $detalle->cantidad }}</td>
                            <td class="text-end">${{ number_format($detalle->precio_unitario, 2) }}</td>
                            <td class="text-end">${{ number_format($detalle->total_linea, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between text-muted small">
                    <span>Subtotal</span><span>${{ number_format($factura->subtotal, 2) }}</span>
                </div>
                @if($factura->descuento > 0)
                <div class="d-flex justify-content-between text-success small">
                    <span>Descuento</span><span>-${{ number_format($factura->descuento, 2) }}</span>
                </div>
                @endif
                @if($factura->impuesto > 0)
                <div class="d-flex justify-content-between text-muted small">
                    <span>Impuesto</span><span>${{ number_format($factura->impuesto, 2) }}</span>
                </div>
                @endif
                @if($factura->costo_envio > 0)
                <div class="d-flex justify-content-between text-muted small">
                    <span>Envío</span><span>${{ number_format($factura->costo_envio, 2) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between fw-bold mt-2">
                    <span>Total</span><span>${{ number_format($factura->total, 2) }} {{ $factura->moneda }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold">Cliente</h6>
                <p class="mb-0">{{ optional($factura->usuario)->nombre }} {{ optional($factura->usuario)->apellido }}</p>
                <p class="text-muted small mb-2">{{ optional($factura->usuario)->email }}</p>
                <h6 class="fw-bold">Pedido</h6>
                <p class="text-muted small mb-0">#{{ $factura->id_pedido }}</p>
                <p class="text-muted small mb-2">{{ optional($factura->pedido?->fecha_pedido)->format('d/m/Y H:i') }}</p>
                <h6 class="fw-bold">Fechas</h6>
                <p class="text-muted small mb-1">Emisión: {{ optional($factura->fecha_emision)->format('d/m/Y H:i') }}</p>
                <p class="text-muted small mb-1">Vencimiento: {{ optional($factura->fecha_vencimiento)->format('d/m/Y') ?? '—' }}</p>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-1">Acciones</h6>
                        <p class="text-muted small mb-0">Exportar o imprimir</p>
                    </div>
                    <button class="btn btn-outline-dark btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
