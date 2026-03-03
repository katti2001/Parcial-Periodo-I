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

{{-- Filtros --}}
<form method="GET" action="{{ route('admin.productos.index') }}" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold mb-1">Buscar nombre o SKU</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre o SKU..." class="form-control form-control-sm">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold mb-1">Categoría</label>
                <select name="id_categoria" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id_categoria }}" {{ request('id_categoria') == $cat->id_categoria ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold mb-1">Equipo</label>
                <select name="id_equipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($equipos as $eq)
                        <option value="{{ $eq->id_equipo }}" {{ request('id_equipo') == $eq->id_equipo ? 'selected' : '' }}>
                            {{ $eq->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold mb-1">Estado</label>
                <select name="activo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2 ms-md-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
                @if(request()->hasAny(['search','id_categoria','id_equipo','activo']))
                    <a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary btn-sm">
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
                @php $disp = $stockDisponible[$p->id_producto] ?? 0; @endphp
                <tr class="{{ ($disp > 0 && $disp <= 3) ? 'table-warning' : '' }}">
                    <td><code>{{ $p->sku_base }}</code></td>
                    <td>{{ $p->nombre }}</td>
                    <td>{{ optional($p->categoria)->nombre ?? '—' }}</td>
                    <td>{{ optional($p->equipo)->nombre ?? '—' }}</td>
                    <td>${{ number_format($p->precio_venta_base, 2) }}</td>
                    <td class="text-center">
                        @if($disp > 3)
                            <span class="badge bg-success">{{ $disp }}</span>
                        @elseif($disp > 0)
                            <span class="badge bg-warning text-dark">{{ $disp }}</span>
                        @else
                            <span class="badge bg-danger">{{ $disp }}</span>
                        @endif
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
    <span><span class="badge bg-warning text-dark">N</span> Stock bajo (&le;3) / En camino</span>
    <span><span class="badge bg-danger">0</span> Sin stock</span>
</div>
@endsection

