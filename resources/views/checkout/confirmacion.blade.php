@extends('layouts.app')

@section('title', 'Pedido Confirmado')

@section('content')
<div class="text-center mb-5">
    <div class="display-1 text-success"><i class="bi bi-check-circle-fill"></i></div>
    <h2 class="mt-3">¡Pago realizado con éxito!</h2>
    <p class="text-muted">Pedido #{{ $pedido->id_pedido }} — {{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-bag-check me-2"></i>Detalle del pedido
            </div>
            <ul class="list-group list-group-flush">
                @foreach($pedido->detalle_pedidos as $detalle)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-semibold">{{ $detalle->producto->nombre }}</span>
                        <small class="text-muted ms-2">Talla: {{ $detalle->talla->nombre }} × {{ $detalle->cantidad }}</small>
                    </div>
                    <span>${{ number_format($detalle->precio_venta_unitario * $detalle->cantidad, 2) }}</span>
                </li>
                @endforeach
            </ul>
            <div class="card-footer">
                <div class="d-flex justify-content-between text-muted small">
                    <span>Subtotal</span><span>${{ number_format($pedido->subtotal, 2) }}</span>
                </div>
                @if($pedido->monto_descuento > 0)
                <div class="d-flex justify-content-between text-success small">
                    <span>Descuento</span><span>-${{ number_format($pedido->monto_descuento, 2) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between text-muted small">
                    <span>Envío</span><span>${{ number_format($pedido->costo_envio, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between fw-bold mt-2">
                    <span>Total pagado</span><span>${{ number_format($pedido->total, 2) }} {{ $pedido->moneda }}</span>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-paypal me-2 text-primary"></i>Información de pago</h6>
                <p class="mb-1 small text-muted">Order ID: <code>{{ $pedido->paypal_order_id }}</code></p>
                <p class="mb-0 small text-muted">Payer ID: <code>{{ $pedido->paypal_payer_id }}</code></p>
            </div>
        </div>

        <div class="d-flex gap-3 justify-content-center">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                <i class="bi bi-house me-1"></i>Ir al inicio
            </a>
            <a href="{{ route('catalogo.index') }}" class="btn btn-dark">
                <i class="bi bi-grid me-1"></i>Seguir comprando
            </a>
        </div>
    </div>
</div>
@endsection
