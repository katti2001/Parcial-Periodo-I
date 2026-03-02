@extends('layouts.app')

@section('title', 'Pedido #' . $pedido->id_pedido)

@section('content')
<div class="mb-3">
    <a href="{{ route('pedidos.historial') }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Volver a mis pedidos
    </a>
</div>

<div class="row g-4">
    {{-- Columna izquierda --}}
    <div class="col-lg-8">

        {{-- Cabecera --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="fw-bold mb-1">Pedido #{{ $pedido->id_pedido }}</h5>
                        <p class="text-muted small mb-0">
                            Realizado el {{ $pedido->fecha_pedido?->format('d/m/Y H:i') ?? '—' }}
                        </p>
                    </div>
                    @php
                        $estadoClases = [
                            'pendiente'  => 'warning',
                            'procesando' => 'info',
                            'enviado'    => 'primary',
                            'entregado'  => 'success',
                            'cancelado'  => 'danger',
                        ];
                        $color = $estadoClases[$pedido->estado_pedido] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $color }} fs-6 text-capitalize">
                        {{ $pedido->estado_pedido }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Productos --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white fw-semibold">
                <i class="bi bi-bag me-2"></i>Productos
            </div>
            <ul class="list-group list-group-flush">
                @foreach($pedido->detalle_pedidos as $detalle)
                @php
                    $imagen = optional($detalle->producto->imagenes->first())->url_imagen;
                @endphp
                <li class="list-group-item">
                    <div class="d-flex align-items-center gap-3">
                        @if($imagen)
                            <img src="{{ $imagen }}" alt="" width="55" height="55"
                                 class="rounded object-fit-cover border">
                        @else
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                 style="width:55px;height:55px;">
                                <i class="bi bi-shirt text-white fs-4"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <p class="mb-0 fw-semibold">
                                {{ optional($detalle->producto)->nombre ?? '(producto eliminado)' }}
                            </p>
                            <small class="text-muted">
                                Talla: {{ optional($detalle->talla)->nombre ?? '—' }}
                                &nbsp;|&nbsp; Cant.: {{ $detalle->cantidad }}
                            </small>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 fw-semibold">
                                ${{ number_format($detalle->precio_unitario * $detalle->cantidad, 2) }}
                            </p>
                            <small class="text-muted">
                                ${{ number_format($detalle->precio_unitario, 2) }} c/u
                            </small>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Devolución activa --}}
        @if($pedido->devolucion)
        @php $dev = $pedido->devolucion; @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header fw-semibold
                @if($dev->estado === 'aprobado') bg-success text-white
                @elseif($dev->estado === 'rechazado') bg-danger text-white
                @else bg-warning text-dark @endif">
                <i class="bi bi-arrow-counterclockwise me-2"></i>
                Solicitud de devolución — <span class="text-capitalize">{{ $dev->estado }}</span>
            </div>
            <div class="card-body">
                <p class="mb-1 small text-muted">
                    Solicitada el {{ $dev->fecha_solicitud->format('d/m/Y H:i') }}
                </p>
                <p class="mb-1 small">
                    <strong>Motivo:</strong> {{ $dev->motivoLegible() }}
                </p>
                @if($dev->notas_admin)
                    <p class="mb-1 small">
                        <strong>Respuesta de la tienda:</strong> {{ $dev->notas_admin }}
                    </p>
                @endif
                @if($dev->monto_reembolso)
                    <p class="mb-0 fw-bold text-success small">
                        Reembolso: ${{ number_format($dev->monto_reembolso, 2) }}
                    </p>
                @endif
                <a href="{{ route('devoluciones.show', $dev->id_devolucion) }}"
                   class="btn btn-sm btn-outline-secondary mt-2">
                    Ver detalle de la devolución
                </a>
            </div>
        </div>
        @endif

    </div>

    {{-- Columna derecha —— resumen + acciones --}}
    <div class="col-lg-4">

        {{-- Resumen de pago --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white fw-semibold">
                <i class="bi bi-receipt me-2"></i>Resumen
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Subtotal</span>
                    <span>${{ number_format($pedido->subtotal, 2) }}</span>
                </div>
                @if($pedido->monto_descuento > 0)
                <div class="d-flex justify-content-between small mb-1 text-success">
                    <span>Descuento ({{ optional($pedido->cupon)->codigo }})</span>
                    <span>-${{ number_format($pedido->monto_descuento, 2) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Envío</span>
                    <span>
                        @if($pedido->costo_envio > 0)
                            ${{ number_format($pedido->costo_envio, 2) }}
                        @else
                            <span class="text-success">Gratis</span>
                        @endif
                    </span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span>${{ number_format($pedido->total, 2) }} {{ $pedido->moneda }}</span>
                </div>
                <div class="d-flex justify-content-between small mt-2 text-muted">
                    <span>Estado pago</span>
                    <span class="badge bg-success">{{ $pedido->estado_pago }}</span>
                </div>
            </div>
        </div>

        {{-- Acción: solicitar devolución --}}
        @if($puedeDevolver)
        <div class="card border-warning border-2 shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-arrow-counterclockwise fs-2 text-warning"></i>
                <p class="mt-2 mb-1 fw-semibold">¿Necesitas devolver algo?</p>
                <p class="small text-muted mb-3">
                    Tienes hasta 30 días desde la entrega para solicitar una devolución.
                </p>
                <a href="{{ route('devoluciones.create', $pedido->id_pedido) }}"
                   class="btn btn-warning w-100">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Solicitar devolución
                </a>
            </div>
        </div>
        @elseif($pedido->estado_pedido === 'entregado' && !$pedido->devolucion)
        <div class="alert alert-secondary small">
            <i class="bi bi-clock me-1"></i>
            El plazo de 30 días para solicitar devolución ha vencido.
        </div>
        @endif

    </div>
</div>
@endsection
