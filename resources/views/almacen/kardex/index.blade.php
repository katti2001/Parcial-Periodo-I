@extends('almacen.layout')
@section('title', 'Kardex')
@section('header', 'Kardex — Historial de Movimientos')

@section('content')
{{-- Filtros --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('almacen.kardex.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Producto</label>
                <select name="id_producto" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($productos as $p)
                        <option value="{{ $p->id_producto }}"
                            {{ request('id_producto') == $p->id_producto ? 'selected' : '' }}>
                            {{ $p->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Talla</label>
                <select name="id_talla" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($tallas as $t)
                        <option value="{{ $t->id_talla }}"
                            {{ request('id_talla') == $t->id_talla ? 'selected' : '' }}>
                            {{ $t->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach(['compra','venta','devolucion','ajuste_inventario'] as $tipo)
                        <option value="{{ $tipo }}" {{ request('tipo') === $tipo ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $tipo)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Desde</label>
                <input type="date" name="desde" class="form-control form-control-sm"
                       value="{{ request('desde') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Hasta</label>
                <input type="date" name="hasta" class="form-control form-control-sm"
                       value="{{ request('hasta') }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('almacen.kardex.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabla de movimientos --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Talla</th>
                    <th>Tipo</th>
                    <th class="text-center">Cantidad</th>
                    <th>Referencia</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movimientos as $m)
                <tr>
                    <td class="text-muted small">{{ $m->id_movimiento }}</td>
                    <td class="small">{{ $m->fecha ? $m->fecha->format('d/m/Y H:i') : '—' }}</td>
                    <td>{{ optional($m->producto)->nombre ?? '—' }}</td>
                    <td>{{ optional($m->talla)->nombre ?? '—' }}</td>
                    <td>
                        @php
                            $badge = match($m->tipo_movimiento) {
                                'compra'             => 'success',
                                'venta'              => 'primary',
                                'devolucion'         => 'warning',
                                'ajuste_inventario'  => 'secondary',
                                default              => 'light',
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}">
                            {{ ucfirst(str_replace('_', ' ', $m->tipo_movimiento)) }}
                        </span>
                    </td>
                    <td class="text-center fw-semibold
                        {{ in_array($m->tipo_movimiento, ['compra','devolucion']) ? 'text-success' : 'text-danger' }}">
                        {{ in_array($m->tipo_movimiento, ['compra','devolucion']) ? '+' : '-' }}{{ $m->cantidad }}
                    </td>
                    <td class="small text-muted">{{ $m->referencia ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay movimientos que coincidan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $movimientos->links() }}</div>
@endsection
