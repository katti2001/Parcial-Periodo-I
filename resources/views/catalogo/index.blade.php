@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@push('styles')
<style>
    .card-producto { transition: transform .2s, box-shadow .2s; }
    .card-producto:hover { transform: translateY(-4px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15); }
    .precio { font-size: 1.25rem; font-weight: 700; color: #198754; }
    .img-producto { height: 220px; object-fit: cover; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-grid me-2"></i>Catálogo</h2>
    <span class="text-muted">{{ $productos->total() }} producto(s) encontrado(s)</span>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('catalogo.index') }}" class="row g-2 mb-4">
    <div class="col-md-4">
        <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre..."
               value="{{ request('buscar') }}">
    </div>
    <div class="col-md-3">
        <select name="categoria" class="form-select">
            <option value="">Todas las categorías</option>
            @foreach($categorias as $cat)
                <option value="{{ $cat->id_categoria }}" {{ request('categoria') == $cat->id_categoria ? 'selected' : '' }}>
                    {{ $cat->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="equipo" class="form-select">
            <option value="">Todos los equipos</option>
            @foreach($equipos as $eq)
                <option value="{{ $eq->id_equipo }}" {{ request('equipo') == $eq->id_equipo ? 'selected' : '' }}>
                    {{ $eq->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search"></i> Filtrar</button>
        @if(request()->hasAny(['buscar','categoria','equipo']))
            <a href="{{ route('catalogo.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
        @endif
    </div>
</form>

{{-- Grid de productos --}}
@if($productos->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox display-4"></i>
        <p class="mt-3">No se encontraron productos.</p>
    </div>
@else
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @foreach($productos as $producto)
            <div class="col">
                <div class="card h-100 card-producto">
                    @php $imagen = $producto->imagenes_productos->first(); @endphp
                    @if($imagen)
                        <img src="{{ $imagen->url_imagen }}" class="card-img-top img-producto" alt="{{ $producto->nombre }}">
                    @else
                        <div class="card-img-top img-producto bg-secondary d-flex align-items-center justify-content-center text-white">
                            <i class="bi bi-image display-4"></i>
                        </div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title mb-1">{{ $producto->nombre }}</h6>
                        @if($producto->equipo)
                            <small class="text-muted mb-1"><i class="bi bi-shield me-1"></i>{{ $producto->equipo->nombre }}</small>
                        @endif
                        @if($producto->categoria)
                            <small class="text-muted mb-2"><i class="bi bi-tag me-1"></i>{{ $producto->categoria->nombre }}</small>
                        @endif
                        <p class="precio mt-auto">${{ number_format($producto->precio_venta_base, 2) }}</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-3">
                        <a href="{{ route('catalogo.show', $producto->id_producto) }}" class="btn btn-dark btn-sm w-100">
                            <i class="bi bi-eye me-1"></i>Ver detalle
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Paginación --}}
    <div class="mt-4 d-flex justify-content-center">
        {{ $productos->links() }}
    </div>
@endif
@endsection
