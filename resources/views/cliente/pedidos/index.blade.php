@extends('layouts.app')

@section('title', 'Mis Pedidos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-bag-heart me-2"></i>Mis Pedidos</h4>
    <a href="{{ route('catalogo.index') }}" class="btn btn-sm btn-outline-dark">
        <i class="bi bi-grid me-1"></i>Seguir comprando
    </a>
</div>

@if($pedidos->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-bag-x display-4"></i>
        <p class="mt-3">Aún no tienes pedidos realizados.</p>
        <a href="{{ route('catalogo.index') }}" class="btn btn-dark mt-2">Ver catálogo</a>
    </div>
@else
    <div class="row g-3">
        @foreach($pedidos as $pedido)
        @php
            $estadoClases = [
                'pendiente'  => 'warning',
                'procesando' => 'info',
                'enviado'    => 'primary',
                'entregado'  => 'success',
                'cancelado'  => 'danger',
            ];
            $color = $estadoClases[$pedido->estado_pedido] ?? 'secondary';

            $dev = $pedido->devolucion;
            $devColor = ['solicitado' => 'warning', 'aprobado' => 'success', 'rechazado' => 'danger'];
        @endphp
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        {{-- Info principal --}}
                        <div class="col-md-4">
                            <p class="mb-1 text-muted small">Pedido</p>
                            <p class="fw-bold mb-0">#{{ $pedido->id_pedido }}</p>
                            <p class="text-muted small mb-0">
                                {{ $pedido->fecha_pedido ? $pedido->fecha_pedido->format('d/m/Y H:i') : '—' }}
                            </p>
                        </div>

                        {{-- Ítems (preview) --}}
                        <div class="col-md-3 d-none d-md-block">
                            <p class="mb-1 text-muted small">Productos</p>
                            <p class="mb-0 small">
                                {{ $pedido->detalle_pedidos->count() }} ítem(s)
                            </p>
                        </div>

                        {{-- Total --}}
                        <div class="col-md-2">
                            <p class="mb-1 text-muted small">Total</p>
                            <p class="fw-bold mb-0">${{ number_format($pedido->total, 2) }}</p>
                        </div>

                        {{-- Estado --}}
                        <div class="col-md-2">
                            <span class="badge bg-{{ $color }} text-capitalize">
                                {{ $pedido->estado_pedido }}
                            </span>
                            @if($dev)
                                <br>
                                <span class="badge bg-{{ $devColor[$dev->estado] ?? 'secondary' }} mt-1">
                                    Dev: {{ $dev->estado }}
                                </span>
                            @endif
                        </div>

                        {{-- Acciones --}}
                        <div class="col-md-1 text-end">
                            <a href="{{ route('pedidos.show', $pedido->id_pedido) }}"
                               class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $pedidos->links() }}
    </div>
@endif
@endsection
