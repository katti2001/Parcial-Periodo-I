@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@push('styles')
<style>
    .card-producto { transition: transform .2s, box-shadow .2s; }
    .card-producto:hover { transform: translateY(-4px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15); }
    .precio { font-size: 1.25rem; font-weight: 700; color: #198754; }
    .img-producto { height: 220px; object-fit: cover; width: 100%; }
    .badge-equipo { font-size: .75rem; }
    .filtros-activos .badge { font-size: .8rem; }
    /* Carrusel dentro de la tarjeta */
    .card-carousel .carousel-item img { height: 220px; object-fit: cover; width: 100%; }
    .card-carousel .carousel-control-prev,
    .card-carousel .carousel-control-next { width: 28px; opacity: .7; }
    .card-carousel .carousel-indicators { bottom: 4px; }
    .card-carousel .carousel-indicators [data-bs-target] {
        width: 7px; height: 7px; border-radius: 50%;
    }
    .placeholder-img { height: 220px; background: #f1f3f8; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="bi bi-grid me-2"></i>Catálogo</h2>
    <span class="text-muted">{{ $productos->total() }} producto(s) encontrado(s)</span>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('catalogo.index') }}" class="card border-0 shadow-sm p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small text-muted mb-1"><i class="bi bi-search me-1"></i>Buscar</label>
            <input type="text" name="buscar" class="form-control" placeholder="Nombre o descripción..."
                   value="{{ request('buscar') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small text-muted mb-1"><i class="bi bi-tag me-1"></i>Categoría</label>
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
            <label class="form-label small text-muted mb-1"><i class="bi bi-shield me-1"></i>Equipo</label>
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
            <button type="submit" class="btn btn-dark w-100">
                <i class="bi bi-funnel me-1"></i>Filtrar
            </button>
            @if(request()->hasAny(['buscar','categoria','equipo']))
                <a href="{{ route('catalogo.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>

    {{-- Filtros activos --}}
    @if(request()->hasAny(['buscar','categoria','equipo']))
    <div class="filtros-activos mt-2 d-flex flex-wrap gap-2">
        @if(request('buscar'))
            <span class="badge bg-secondary">
                <i class="bi bi-search me-1"></i>{{ request('buscar') }}
            </span>
        @endif
        @if(request('categoria'))
            @php $catActiva = $categorias->firstWhere('id_categoria', request('categoria')); @endphp
            @if($catActiva)
                <span class="badge bg-secondary">
                    <i class="bi bi-tag me-1"></i>{{ $catActiva->nombre }}
                </span>
            @endif
        @endif
        @if(request('equipo'))
            @php $eqActivo = $equipos->firstWhere('id_equipo', request('equipo')); @endphp
            @if($eqActivo)
                <span class="badge bg-dark">
                    <i class="bi bi-shield me-1"></i>{{ $eqActivo->nombre }}
                </span>
            @endif
        @endif
    </div>
    @endif
</form>

{{-- Grid de productos --}}
@if($productos->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox display-4"></i>
        <p class="mt-3">No se encontraron productos.</p>
        <a href="{{ route('catalogo.index') }}" class="btn btn-outline-secondary btn-sm">Ver todos</a>
    </div>
@else
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @foreach($productos as $producto)
            <div class="col">
                <div class="card h-100 card-producto border-0 shadow-sm">
                    @php $imagenes = $producto->imagenes_productos; @endphp
                    @if($imagenes->isNotEmpty())
                        @if($imagenes->count() === 1)
                            {{-- Una sola imagen: img estático --}}
                            <img src="{{ $imagenes->first()->url_imagen }}"
                                 class="card-img-top img-producto"
                                 alt="{{ $producto->nombre }}">
                        @else
                            {{-- Múltiples imágenes: carrusel --}}
                            @php $cid = 'c-' . $producto->id_producto; @endphp
                            <div id="{{ $cid }}" class="carousel slide card-carousel"
                                 data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach($imagenes as $i => $img)
                                        <button type="button"
                                                data-bs-target="#{{ $cid }}"
                                                data-bs-slide-to="{{ $i }}"
                                                class="{{ $i === 0 ? 'active' : '' }}"
                                                aria-label="Imagen {{ $i + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div class="carousel-inner">
                                    @foreach($imagenes as $i => $img)
                                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                            <img src="{{ $img->url_imagen }}"
                                                 alt="{{ $producto->nombre }}">
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button"
                                        data-bs-target="#{{ $cid }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button"
                                        data-bs-target="#{{ $cid }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="card-img-top placeholder-img d-flex align-items-center justify-content-center text-muted">
                            <i class="bi bi-image display-4"></i>
                        </div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title mb-1 fw-semibold">{{ $producto->nombre }}</h6>
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @if($producto->equipo)
                                <span class="badge bg-dark badge-equipo">
                                    <i class="bi bi-shield me-1"></i>{{ $producto->equipo->nombre }}
                                </span>
                            @endif
                            @if($producto->categoria)
                                <span class="badge bg-secondary badge-equipo">
                                    <i class="bi bi-tag me-1"></i>{{ $producto->categoria->nombre }}
                                </span>
                            @endif
                        </div>
                        <p class="precio mt-auto mb-0">${{ number_format($producto->precio_calculado, 2) }}</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-3">
                        <a href="{{ route('catalogo.show', $producto->id_producto) }}" class="btn btn-dark btn-sm w-100">
                            <i class="bi bi-eye me-1"></i>Ver tallas y comprar
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
