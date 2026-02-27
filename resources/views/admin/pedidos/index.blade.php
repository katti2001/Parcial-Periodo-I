@extends('admin.layout')
@section('title', 'Pedidos')
@section('header', 'Gestión de Pedidos')

@section('content')

{{-- Filtros --}}
<form method="GET" action="{{ route('admin.pedidos.index') }}" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold mb-1">Buscar cliente</label>
                <input type="text" name="cliente" value="{{ request('cliente') }}" placeholder="Nombre o email..." class="form-control form-control-sm">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold mb-1">Estado pedido</label>
                <select name="estado_pedido" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach(['pendiente','procesando','enviado','entregado','cancelado'] as $est)
                        <option value="{{ $est }}" {{ request('estado_pedido') === $est ? 'selected' : '' }}>
                            {{ ucfirst($est) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold mb-1">Estado pago</label>
                <select name="estado_pago" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="pagado"   {{ request('estado_pago') === 'pagado'   ? 'selected' : '' }}>Pagado</option>
                    <option value="pendiente"{{ request('estado_pago') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="form-control form-control-sm">
            </div>
            <div class="col-12 col-md-auto d-flex gap-2 ms-md-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
                @if(request()->hasAny(['cliente','estado_pedido','estado_pago','fecha_desde','fecha_hasta']))
                    <a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
</form>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
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
                @forelse($pedidos as $p)
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
                <tr><td colspan="7" class="text-center text-muted py-4">Sin pedidos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pedidos->hasPages())
    <div class="card-footer bg-white border-0">
        {{ $pedidos->links() }}
    </div>
    @endif
</div>
@endsection
