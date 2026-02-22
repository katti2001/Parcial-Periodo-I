@extends('admin.layout')
@section('title', 'Detalle Pedido #' . $pedido->id_pedido)
@section('header', 'Pedido #' . $pedido->id_pedido)

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold border-0 pt-3">
                <i class="bi bi-bag me-2"></i>Productos
            </div>
            <ul class="list-group list-group-flush">
                @foreach($pedido->detalle_pedidos as $d)
                <li class="list-group-item d-flex justify-content-between">
                    <div>
                        <span class="fw-semibold">{{ optional($d->producto)->nombre ?? '(producto eliminado)' }}</span>
                        <small class="text-muted ms-2">Talla: {{ optional($d->talla)->nombre ?? '—' }} × {{ $d->cantidad }}</small>
                    </div>
                    <span>${{ number_format($d->precio_venta_unitario * $d->cantidad, 2) }}</span>
                </li>
                @endforeach
            </ul>
            <div class="card-footer bg-white">
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
                <div class="d-flex justify-content-between fw-bold mt-1">
                    <span>Total</span><span>${{ number_format($pedido->total, 2) }} {{ $pedido->moneda }}</span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-2"><i class="bi bi-paypal me-2 text-primary"></i>PayPal</h6>
                <p class="mb-1 small">Order ID: <code>{{ $pedido->paypal_order_id ?? '—' }}</code></p>
                <p class="mb-0 small">Payer ID: <code>{{ $pedido->paypal_payer_id ?? '—' }}</code></p>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Cliente</h6>
                <p class="mb-1">{{ optional($pedido->usuario)->nombre }} {{ optional($pedido->usuario)->apellido }}</p>
                <p class="mb-0 text-muted small">{{ optional($pedido->usuario)->email }}</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Estado del pedido</h6>
                <p class="mb-1">
                    <span class="badge {{ $pedido->estado_pago === 'pagado' ? 'bg-success' : 'bg-warning text-dark' }}">
                        Pago: {{ $pedido->estado_pago ?? 'pendiente' }}
                    </span>
                </p>
                <p class="mb-3">
                    <span class="badge bg-secondary">Pedido: {{ $pedido->estado_pedido ?? '—' }}</span>
                </p>

                <div class="d-flex flex-column gap-2">
                    @foreach(['procesando','enviado','entregado','cancelado'] as $estado)
                    <form method="POST"
                          action="{{ route('admin.pedidos.estado', [$pedido->id_pedido, $estado]) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-secondary w-100">
                            Marcar como: {{ ucfirst($estado) }}
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-secondary mt-3">
    <i class="bi bi-arrow-left me-1"></i>Volver
</a>
@endsection
