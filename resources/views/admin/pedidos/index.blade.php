@extends('admin.layout')
@section('title', 'Pedidos')
@section('header', 'Gestión de Pedidos')

@section('content')
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
