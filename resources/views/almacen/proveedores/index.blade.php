@extends('almacen.layout')
@section('title', 'Proveedores')
@section('header', 'Proveedores')
@section('header-actions')
    <a href="{{ route('almacen.proveedores.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nuevo proveedor
    </a>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Empresa</th>
                    <th>Contacto</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proveedores as $p)
                <tr>
                    <td class="text-muted small">{{ $p->id_proveedor }}</td>
                    <td class="fw-semibold">{{ $p->nombre_empresa }}</td>
                    <td>{{ $p->contacto ?? '—' }}</td>
                    <td>{{ $p->telefono ?? '—' }}</td>
                    <td>{{ $p->email ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('almacen.proveedores.edit', $p->id_proveedor) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST"
                              action="{{ route('almacen.proveedores.destroy', $p->id_proveedor) }}"
                              class="d-inline"
                              onsubmit="return confirm('¿Eliminar este proveedor?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No hay proveedores registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $proveedores->links() }}</div>
@endsection
