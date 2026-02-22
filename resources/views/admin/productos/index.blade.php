@extends('admin.layout')
@section('title', 'Productos')
@section('header', 'Gestión de Productos')

@section('content')

<div class="alert alert-info d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-info-circle-fill fs-5"></i>
    <div>
        El stock se gestiona desde
        <a href="{{ route('almacen.compras.index') }}" class="alert-link">Almacén → Compras</a>.
        Registra una compra y márcala como <strong>Recibida</strong> para que el stock esté disponible.
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>SKU</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Equipo</th>
                    <th>Precio</th>
                    <th class="text-center">Disponible</th>
                    <th class="text-center">En camino</th>
                    <th class="text-center">Activo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $p)
                <tr>
                    <td><code>{{ $p->sku_base }}</code></td>
                    <td>{{ $p->nombre }}</td>
                    <td>{{ optional($p->categoria)->nombre ?? '—' }}</td>
                    <td>{{ optional($p->equipo)->nombre ?? '—' }}</td>
                    <td>${{ number_format($p->precio_venta_base, 2) }}</td>
                    <td class="text-center">
                        @php $disp = $stockDisponible[$p->id_producto] ?? 0; @endphp
                        <span class="badge {{ $disp > 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ $disp }}
                        </span>
                    </td>
                    <td class="text-center">
                        @php $camino = $stockEnCamino[$p->id_producto] ?? 0; @endphp
                        @if($camino > 0)
                            <span class="badge bg-warning text-dark">{{ $camino }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($p->activo)
                            <span class="badge bg-success">Sí</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('admin.productos.edit', $p->id_producto) }}"
                           class="btn btn-sm btn-outline-dark"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.productos.destroy', $p->id_producto) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('¿Desactivar este producto?')">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No hay productos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($productos->hasPages())
    <div class="card-footer bg-white border-0">
        {{ $productos->links() }}
    </div>
    @endif
</div>

<div class="d-flex gap-3 mt-3 small text-muted">
    <span><span class="badge bg-success">N</span> Stock disponible (compra recibida)</span>
    <span><span class="badge bg-warning text-dark">N</span> En camino (compra solicitada)</span>
    <span><span class="badge bg-danger">0</span> Sin stock</span>
</div>
@endsection

