@extends('layouts.app')

@section('title', 'Mi Carrito')

@section('content')
<h2 class="mb-4"><i class="bi bi-cart3 me-2"></i>Mi Carrito</h2>

@if(empty($carrito))
    <div class="text-center py-5 text-muted">
        <i class="bi bi-cart-x display-3"></i>
        <p class="mt-3">Tu carrito está vacío.</p>
        <a href="{{ route('catalogo.index') }}" class="btn btn-dark">
            <i class="bi bi-grid me-1"></i>Ver catálogo
        </a>
    </div>
@else
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Producto</th>
                                <th>Talla</th>
                                <th>Precio</th>
                                <th style="width:120px">Cantidad</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($carrito as $clave => $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($item['imagen'])
                                            <img src="{{ $item['imagen'] }}" width="55" height="55"
                                                 style="object-fit:cover;border-radius:.25rem" alt="">
                                        @else
                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white"
                                                 style="width:55px;height:55px">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        @endif
                                        <span class="fw-semibold">{{ $item['nombre'] }}</span>
                                    </div>
                                </td>
                                <td>{{ $item['talla'] }}</td>
                                <td>${{ number_format($item['precio'], 2) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('carrito.actualizar', $clave) }}">
                                        @csrf @method('PATCH')
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="cantidad" class="form-control"
                                                   value="{{ $item['cantidad'] }}" min="1" max="10"
                                                   onchange="this.form.submit()">
                                        </div>
                                    </form>
                                </td>
                                <td class="fw-bold">${{ number_format($item['precio'] * $item['cantidad'], 2) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('carrito.eliminar', $clave) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('catalogo.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Seguir comprando
                </a>
                <form method="POST" action="{{ route('carrito.vaciar') }}">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>Vaciar carrito
                    </button>
                </form>
            </div>
        </div>

        {{-- Resumen --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">
                    <i class="bi bi-receipt me-2"></i>Resumen del pedido
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>${{ number_format($total, 2) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total estimado</span>
                        <span>${{ number_format($total, 2) }}</span>
                    </div>
                    <a href="{{ route('checkout.index') }}" class="btn btn-success w-100 mt-3 btn-lg">
                        <i class="bi bi-lock me-2"></i>Proceder al pago
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
