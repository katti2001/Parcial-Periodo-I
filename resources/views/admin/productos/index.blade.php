@extends('admin.layout')
@section('title', 'Productos')
@section('header', 'Gestión de Productos')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.productos.create') }}" class="btn btn-dark">
        <i class="bi bi-plus-lg me-1"></i>Nuevo producto
    </a>
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
                    <th>Activo</th>
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
                    <td>
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
                <tr><td colspan="7" class="text-center text-muted py-4">No hay productos.</td></tr>
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
@endsection
