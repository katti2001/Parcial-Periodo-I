@extends('almacen.layout')
@section('title', 'Compras')
@section('header', 'Compras / Entradas de Inventario')
@section('header-actions')
    <a href="{{ route('almacen.compras.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nueva compra
    </a>
@endsection

@push('styles')
<style>
.compra-card {
    transition: box-shadow .15s, transform .15s;
    border-left: 4px solid transparent;
    cursor: pointer;
}
.compra-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.1); transform: translateY(-1px); }
.compra-card.estado-recibido   { border-left-color: #198754; }
.compra-card.estado-solicitado { border-left-color: #ffc107; }
.compra-card.estado-cancelado  { border-left-color: #dc3545; }
.badge-estado { font-size: .72rem; padding: .35em .65em; }
.stat-pill { background:#f1f3f8; border-radius: 2rem; padding: 4px 14px; font-size:.8rem; }
</style>
@endpush

@section('content')

{{-- Estadísticas rápidas --}}
@php
    $total      = $compras->total();
    $recibidas  = $compras->getCollection()->where('estado','recibido')->count();
    $solicitadas= $compras->getCollection()->where('estado','solicitado')->count();
    $canceladas = $compras->getCollection()->where('estado','cancelado')->count();
    $montoTotal = $compras->getCollection()->sum('total_compra');
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-primary">{{ $compras->total() }}</div>
            <div class="small text-muted">Total compras</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-success">{{ $recibidas }}</div>
            <div class="small text-muted">Recibidas (pág. actual)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-warning">{{ $solicitadas }}</div>
            <div class="small text-muted">Pendientes (pág. actual)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-dark">${{ number_format($montoTotal, 2) }}</div>
            <div class="small text-muted">Monto (pág. actual)</div>
        </div>
    </div>
</div>

{{-- Listado --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @forelse($compras as $c)
        @php
            $estadoClass = match($c->estado) {
                'recibido'   => 'estado-recibido',
                'solicitado' => 'estado-solicitado',
                'cancelado'  => 'estado-cancelado',
                default      => '',
            };
            $badgeColor = match($c->estado) {
                'recibido'   => 'success',
                'solicitado' => 'warning text-dark',
                'cancelado'  => 'danger',
                default      => 'secondary',
            };
            $iconEstado = match($c->estado) {
                'recibido'   => 'bi-check-circle-fill text-success',
                'solicitado' => 'bi-clock-fill text-warning',
                'cancelado'  => 'bi-x-circle-fill text-danger',
                default      => 'bi-circle text-secondary',
            };
        @endphp
        <a href="{{ route('almacen.compras.show', $c->id_compra) }}"
           class="d-block text-decoration-none text-reset">
            <div class="compra-card {{ $estadoClass }} px-4 py-3 border-bottom">
                <div class="row align-items-center g-2">

                    {{-- Ícono de estado --}}
                    <div class="col-auto d-none d-md-block">
                        <i class="bi {{ $iconEstado }} fs-4"></i>
                    </div>

                    {{-- ID + Proveedor --}}
                    <div class="col">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-bold">#{{ $c->id_compra }}</span>
                            <span class="text-muted">·</span>
                            <span class="fw-semibold">{{ optional($c->proveedor)->nombre_empresa ?? '—' }}</span>
                            @if($c->numero_factura_proveedor)
                                <span class="stat-pill text-secondary">
                                    <i class="bi bi-receipt me-1"></i>{{ $c->numero_factura_proveedor }}
                                </span>
                            @endif
                        </div>
                        <div class="small text-muted mt-1">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ $c->fecha_compra ? $c->fecha_compra->format('d/m/Y') : '—' }}
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="col-auto text-end">
                        <div class="fw-bold fs-6">${{ number_format($c->total_compra, 2) }}</div>
                        <span class="badge bg-{{ $badgeColor }} badge-estado">{{ ucfirst($c->estado) }}</span>
                    </div>

                    {{-- Flecha --}}
                    <div class="col-auto text-muted d-none d-sm-block">
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </div>
            </div>
        </a>
        @empty
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox display-4 d-block mb-3 opacity-25"></i>
            No hay compras registradas todavía.
            <a href="{{ route('almacen.compras.create') }}" class="d-block mt-2">Registrar primera compra</a>
        </div>
        @endforelse
    </div>
</div>

<div class="mt-3">{{ $compras->links() }}</div>
@endsection
