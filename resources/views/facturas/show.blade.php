@extends('layouts.app')

@section('title', 'Factura ' . $factura->numero)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Factura {{ $factura->numero }}</h3>
                <p class="text-muted mb-0">Emitida el {{ optional($factura->fecha_emision)->format('d/m/Y H:i') }} @if($factura->fecha_vencimiento)- Vence {{ optional($factura->fecha_vencimiento)->format('d/m/Y') }}@endif</p>
            </div>
            <span class="badge bg-dark text-uppercase">{{ $factura->estado }}</span>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="fw-bold">Cliente</h6>
                        <p class="mb-0">{{ optional($factura->usuario)->nombre }} {{ optional($factura->usuario)->apellido }}</p>
                        <p class="text-muted small mb-0">{{ optional($factura->usuario)->email }}</p>
                    </div>
                    <div class="text-end">
                        <h6 class="fw-bold">Pedido #{{ $factura->id_pedido }}</h6>
                        <p class="text-muted small mb-0">{{ optional($factura->pedido?->fecha_pedido)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

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

        <div class="d-flex gap-2 justify-content-between">
            <a href="{{ route('pedidos.historial') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Mis pedidos
            </a>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-dark" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <a href="{{ route('catalogo.index') }}" class="btn btn-dark">
                    <i class="bi bi-grid"></i> Seguir comprando
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
