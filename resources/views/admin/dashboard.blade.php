@extends('admin.layout')
@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
{{-- Tarjetas de estadísticas --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary fs-4">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <div class="text-muted small">Productos activos</div>
                    <div class="fs-4 fw-bold">{{ $stats['productos'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success fs-4">
                    <i class="bi bi-bag-check"></i>
                </div>
                <div>
                    <div class="text-muted small">Pedidos totales</div>
                    <div class="fs-4 fw-bold">{{ $stats['pedidos'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info fs-4">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <div class="text-muted small">Clientes</div>
                    <div class="fs-4 fw-bold">{{ $stats['usuarios'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div>
                    <div class="text-muted small">Ingresos totales</div>
                    <div class="fs-4 fw-bold">${{ number_format($stats['ingresos'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pedidos recientes --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold border-0 pt-3">
        <i class="bi bi-clock-history me-2"></i>Pedidos recientes
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado pago</th>
                    <th>Estado pedido</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos_recientes as $p)
                <tr>
                    <td>{{ $p->id_pedido }}</td>
                    <td>{{ optional($p->usuario)->nombre }} {{ optional($p->usuario)->apellido }}</td>
                    <td>${{ number_format($p->total, 2) }}</td>
                    <td>
                        <span class="badge {{ $p->estado_pago === 'pagado' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $p->estado_pago ?? 'pendiente' }}
                        </span>
                    </td>
                    <td><span class="badge bg-secondary">{{ $p->estado_pedido ?? '—' }}</span></td>
                    <td>{{ optional($p->fecha_pedido)->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.pedidos.show', $p->id_pedido) }}" class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Sin pedidos aún.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-0">
        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-sm btn-outline-dark">
            Ver todos los pedidos <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
</div>
@endsection
