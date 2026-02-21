@extends('layouts.app')

@section('title', $producto->nombre)

@push('styles')
<style>
    .img-principal { max-height: 420px; object-fit: cover; width: 100%; border-radius: .5rem; }
    .precio-grande { font-size: 2rem; font-weight: 700; color: #198754; }
    .thumbnail { width: 70px; height: 70px; object-fit: cover; cursor: pointer;
                 border: 2px solid transparent; border-radius: .25rem; }
    .thumbnail:hover, .thumbnail.active { border-color: #212529; }
</style>
@endpush

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('catalogo.index') }}">Catálogo</a></li>
        <li class="breadcrumb-item active">{{ $producto->nombre }}</li>
    </ol>
</nav>

<div class="row g-5">
    {{-- Galería --}}
    <div class="col-md-6">
        @php $imagenes = $producto->imagenes_productos; @endphp
        @if($imagenes->isNotEmpty())
            <img id="imgPrincipal" src="{{ $imagenes->first()->url_imagen }}"
                 alt="{{ $producto->nombre }}" class="img-principal mb-3">
            @if($imagenes->count() > 1)
                <div class="d-flex gap-2 flex-wrap">
                    @foreach($imagenes as $img)
                        <img src="{{ $img->url_imagen }}" class="thumbnail {{ $loop->first ? 'active' : '' }}"
                             onclick="cambiarImagen(this)" alt="">
                    @endforeach
                </div>
            @endif
        @else
            <div class="img-principal bg-secondary d-flex align-items-center justify-content-center text-white mb-3">
                <i class="bi bi-image display-1"></i>
            </div>
        @endif
    </div>

    {{-- Info --}}
    <div class="col-md-6">
        <h1 class="h2 mb-1">{{ $producto->nombre }}</h1>
        <p class="text-muted mb-1">SKU: <code>{{ $producto->sku_base }}</code></p>

        @if($producto->categoria)
            <span class="badge bg-secondary mb-1"><i class="bi bi-tag me-1"></i>{{ $producto->categoria->nombre }}</span>
        @endif
        @if($producto->equipo)
            <span class="badge bg-dark mb-1"><i class="bi bi-shield me-1"></i>{{ $producto->equipo->nombre }}</span>
        @endif

        <p class="precio-grande mt-3">${{ number_format($producto->precio_venta_base, 2) }}</p>

        @if($producto->descripcion)
            <p class="text-muted">{{ $producto->descripcion }}</p>
        @endif

        <hr>

        {{-- Botón agregar al carrito (habilitado en Phase 4) --}}
        @auth
            <a href="#" class="btn btn-success btn-lg w-100 disabled">
                <i class="bi bi-cart-plus me-2"></i>Agregar al carrito
                <small class="d-block" style="font-size:.7rem">(disponible próximamente)</small>
            </a>
        @else
            <a href="{{ route('login') }}" class="btn btn-dark btn-lg w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Inicia sesión para comprar
            </a>
        @endauth

        <a href="{{ route('catalogo.index') }}" class="btn btn-outline-secondary w-100 mt-2">
            <i class="bi bi-arrow-left me-1"></i>Volver al catálogo
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function cambiarImagen(el) {
        document.getElementById('imgPrincipal').src = el.src;
        document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }
</script>
@endpush
