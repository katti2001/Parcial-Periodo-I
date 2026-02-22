@extends('almacen.layout')
@section('title', 'Compras')
@section('header', 'Compras / Entradas de Inventario')
@section('header-actions')
    <a href="{{ route('almacen.compras.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nueva compra
    </a>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th>Factura</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($compras as $c)
                <tr>
                    <td class="text-muted small">{{ $c->id_compra }}</td>
                    <td>{{ optional($c->proveedor)->nombre_empresa ?? '—' }}</td>
                    <td>{{ $c->fecha_compra ? $c->fecha_compra->format('d/m/Y') : '—' }}</td>
                    <td><code>{{ $c->numero_factura_proveedor ?? '—' }}</code></td>
                    <td>${{ number_format($c->total_compra, 2) }}</td>
                    <td>
                        @php
                            $badge = match($c->estado) {
                                'recibido'   => 'success',
                                'solicitado' => 'warning',
                                'cancelado'  => 'danger',
                                default      => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}">{{ ucfirst($c->estado) }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('almacen.compras.show', $c->id_compra) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay compras registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $compras->links() }}</div>
@endsection
