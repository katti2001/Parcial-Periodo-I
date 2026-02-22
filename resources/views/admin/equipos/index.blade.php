@extends('admin.layout')
@section('title', 'Equipos')
@section('header', 'Gestión de Equipos')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.equipos.create') }}" class="btn btn-dark">
        <i class="bi bi-plus-lg me-1"></i>Nuevo equipo
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>País</th>
                    <th>Productos</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipos as $eq)
                <tr>
                    <td class="text-muted small">{{ $eq->id_equipo }}</td>
                    <td class="fw-semibold">{{ $eq->nombre }}</td>
                    <td class="text-muted">{{ $eq->pais ?? '—' }}</td>
                    <td>
                        <span class="badge bg-secondary">{{ $eq->productos_count }}</span>
                    </td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('admin.equipos.edit', $eq->id_equipo) }}"
                           class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.equipos.destroy', $eq->id_equipo) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('¿Eliminar el equipo «{{ $eq->nombre }}»?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No hay equipos registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
