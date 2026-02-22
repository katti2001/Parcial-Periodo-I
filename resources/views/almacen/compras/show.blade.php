@extends('almacen.layout')
@section('title', 'Compra #' . $compra->id_compra)
@section('header', 'Compra #' . $compra->id_compra)

@section('header-actions')
    @if($compra->estado === 'solicitado')
        <form method="POST" action="{{ route('almacen.compras.recibir', $compra->id_compra) }}"
              onsubmit="return confirm('¿Marcar como recibida e ingresar al inventario?')">
            @csrf @method('PATCH')
            <button class="btn btn-success btn-sm">
                <i class="bi bi-check2-circle me-1"></i>Marcar como recibida
            </button>
        </form>
    @endif
@endsection

@section('content')
<div class="row g-4">
    {{-- Detalles de la compra --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-0 pt-3">
                <i class="bi bi-list-ul me-2"></i>Productos comprados
            </div>
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th>Talla</th>
                        <th class="text-center">Cant. comprada</th>
                        <th class="text-center">Stock restante</th>
                        <th class="text-end">Costo unit.</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compra->detalle_compras as $d)
                    <tr>
                        <td>{{ optional($d->producto)->nombre ?? '(eliminado)' }}</td>
                        <td>{{ optional($d->talla)->nombre ?? '—' }}</td>
                        <td class="text-center">{{ $d->cantidad_comprada }}</td>
                        <td class="text-center">
                            <span class="badge {{ $d->cantidad_restante > 0 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $d->cantidad_restante }}
                            </span>
                        </td>
                        <td class="text-end">${{ number_format($d->costo_unitario, 2) }}</td>
                        <td class="text-end">${{ number_format($d->cantidad_comprada * $d->costo_unitario, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Total:</td>
                        <td class="text-end fw-bold">${{ number_format($compra->total_compra, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Resumen --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Información</h6>
                <p class="mb-1 small"><strong>Proveedor:</strong><br>
                    {{ optional($compra->proveedor)->nombre_empresa ?? '—' }}</p>
                <p class="mb-1 small"><strong>Fecha:</strong><br>
                    {{ $compra->fecha_compra ? $compra->fecha_compra->format('d/m/Y') : '—' }}</p>
                <p class="mb-1 small"><strong>Factura:</strong><br>
                    <code>{{ $compra->numero_factura_proveedor ?? '—' }}</code></p>
                <p class="mb-0 small"><strong>Estado:</strong><br>
                    @php
                        $badge = match($compra->estado) {
                            'recibido'   => 'success',
                            'solicitado' => 'warning',
                            'cancelado'  => 'danger',
                            default      => 'secondary',
                        };
                    @endphp
                    <span class="badge bg-{{ $badge }}">{{ ucfirst($compra->estado) }}</span>
                </p>
            </div>
        </div>
    </div>
</div>

<a href="{{ route('almacen.compras.index') }}" class="btn btn-outline-secondary mt-3">
    <i class="bi bi-arrow-left me-1"></i>Volver
</a>
@endsection
