@extends('admin.layout')
@section('title', 'Cupones')
@section('header', 'Gestión de Cupones')

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.cupones.create') }}" class="btn btn-dark">
        <i class="bi bi-plus-lg me-1"></i>Nuevo cupón
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Expira</th>
                    <th>Activo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($cupones as $c)
                <tr>
                    <td><code>{{ $c->codigo }}</code></td>
                    <td>{{ $c->tipo_descuento }}</td>
                    <td>{{ $c->tipo_descuento === 'porcentaje' ? $c->valor . '%' : '$' . number_format($c->valor, 2) }}</td>
                    <td>{{ optional($c->fecha_expiracion)->format('d/m/Y') ?? 'Sin expiración' }}</td>
                    <td>
                        <span class="badge {{ $c->activo ? 'bg-success' : 'bg-secondary' }}">
                            {{ $c->activo ? 'Sí' : 'No' }}
                        </span>
                    </td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('admin.cupones.edit', $c->id_cupon) }}" class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.cupones.destroy', $c->id_cupon) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('¿Eliminar cupón?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No hay cupones.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cupones->hasPages())
    <div class="card-footer bg-white border-0">
        {{ $cupones->links() }}
    </div>
    @endif
</div>
@endsection
