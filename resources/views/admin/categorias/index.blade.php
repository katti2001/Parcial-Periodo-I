@extends('admin.layout')
@section('title', 'Categorías')
@section('header', 'Gestión de Categorías')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.categorias.create') }}" class="btn btn-dark">
        <i class="bi bi-plus-lg me-1"></i>Nueva categoría
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Productos</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($categorias as $cat)
                <tr>
                    <td class="text-muted small">{{ $cat->id_categoria }}</td>
                    <td class="fw-semibold">{{ $cat->nombre }}</td>
                    <td class="text-muted">{{ $cat->descripcion ?? '—' }}</td>
                    <td>
                        <span class="badge bg-secondary">{{ $cat->productos_count }}</span>
                    </td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('admin.categorias.edit', $cat->id_categoria) }}"
                           class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.categorias.destroy', $cat->id_categoria) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('¿Eliminar la categoría «{{ $cat->nombre }}»?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No hay categorías registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
