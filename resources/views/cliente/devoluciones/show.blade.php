@extends('layouts.app')

@section('title', 'Devolución #' . $devolucion->id_devolucion)

@section('content')
<div class="mb-3">
    <a href="{{ route('pedidos.show', $devolucion->id_pedido) }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Volver al pedido
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">

        {{-- Encabezado con estado --}}
        @php
            $cfg = [
                'solicitado' => ['color' => 'warning',  'icono' => 'bi-hourglass-split',       'titulo' => 'Solicitud en revisión'],
                'aprobado'   => ['color' => 'success',  'icono' => 'bi-check-circle-fill',      'titulo' => 'Devolución aprobada'],
                'rechazado'  => ['color' => 'danger',   'icono' => 'bi-x-circle-fill',          'titulo' => 'Devolución rechazada'],
            ];
            $c = $cfg[$devolucion->estado] ?? ['color' => 'secondary', 'icono' => 'bi-circle', 'titulo' => $devolucion->estado];
        @endphp

        <div class="text-center mb-4">
            <div class="display-4 text-{{ $c['color'] }}">
                <i class="bi {{ $c['icono'] }}"></i>
            </div>
            <h5 class="fw-bold mt-2">{{ $c['titulo'] }}</h5>
            <p class="text-muted small">
                Solicitud #{{ $devolucion->id_devolucion }} —
                Pedido #{{ $devolucion->id_pedido }}
            </p>
        </div>

        {{-- Timeline de estados --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    {{-- Solicitado --}}
                    <li class="d-flex gap-3 mb-3">
                        <div class="text-warning fs-5"><i class="bi bi-circle-fill"></i></div>
                        <div>
                            <p class="mb-0 fw-semibold">Solicitud enviada</p>
                            <p class="mb-0 text-muted small">
                                {{ $devolucion->fecha_solicitud->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </li>
                    {{-- Resolución --}}
                    <li class="d-flex gap-3">
                        <div class="fs-5
                            @if($devolucion->estado === 'aprobado') text-success
                            @elseif($devolucion->estado === 'rechazado') text-danger
                            @else text-muted @endif">
                            <i class="bi bi-circle{{ $devolucion->fecha_resolucion ? '-fill' : '' }}"></i>
                        </div>
                        <div>
                            @if($devolucion->fecha_resolucion)
                                <p class="mb-0 fw-semibold text-capitalize">{{ $devolucion->estado }}</p>
                                <p class="mb-0 text-muted small">
                                    {{ $devolucion->fecha_resolucion->format('d/m/Y H:i') }}
                                </p>
                            @else
                                <p class="mb-0 text-muted">Pendiente de revisión</p>
                            @endif
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Detalle de la solicitud --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white fw-semibold">
                <i class="bi bi-info-circle me-2"></i>Detalle de la solicitud
            </div>
            <div class="card-body">
                <p class="mb-1 small">
                    <strong>Motivo:</strong> {{ $devolucion->motivoLegible() }}
                </p>
                @if($devolucion->descripcion)
                <p class="mb-1 small">
                    <strong>Descripción:</strong> {{ $devolucion->descripcion }}
                </p>
                @endif
                @if($devolucion->notas_admin)
                <div class="alert alert-secondary small mb-0 mt-2">
                    <strong><i class="bi bi-chat-left-text me-1"></i>Respuesta de la tienda:</strong><br>
                    {{ $devolucion->notas_admin }}
                </div>
                @endif
            </div>
        </div>

        {{-- Ítems devueltos --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white fw-semibold">
                <i class="bi bi-bag me-2"></i>Ítems solicitados
            </div>
            <ul class="list-group list-group-flush">
                @foreach($devolucion->detalles as $detalle)
                @php $dp = $detalle->detallePedido; @endphp
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 fw-semibold">
                            {{ optional($dp->producto)->nombre ?? '(producto eliminado)' }}
                        </p>
                        <small class="text-muted">
                            Talla: {{ optional($dp->talla)->nombre ?? '—' }}
                        </small>
                    </div>
                    <span class="badge bg-secondary">
                        {{ $detalle->cantidad_devuelta }} ud(s)
                    </span>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Reembolso (si aprobado) --}}
        @if($devolucion->estado === 'aprobado' && $devolucion->monto_reembolso)
        <div class="card border-success border-2 shadow-sm mb-4">
            <div class="card-body text-center">
                <i class="bi bi-cash-coin fs-2 text-success"></i>
                <p class="mt-2 mb-0 fw-bold fs-5 text-success">
                    Reembolso: ${{ number_format($devolucion->monto_reembolso, 2) }}
                </p>
                @if($devolucion->paypal_refund_id)
                <p class="text-muted small mt-1 mb-0">
                    Referencia PayPal: <code>{{ $devolucion->paypal_refund_id }}</code>
                </p>
                @endif
                <p class="text-muted small mt-1">
                    El reembolso será procesado en tu cuenta de PayPal en un plazo de 3 a 5 días hábiles.
                </p>
            </div>
        </div>
        @endif

        <div class="text-center">
            <a href="{{ route('pedidos.historial') }}" class="btn btn-outline-dark">
                <i class="bi bi-bag-heart me-1"></i>Ver todos mis pedidos
            </a>
        </div>

    </div>
</div>
@endsection
