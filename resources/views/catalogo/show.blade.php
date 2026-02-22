@extends('layouts.app')

@section('title', $producto->nombre)

@push('styles')
<style>
    .precio-grande { font-size: 2rem; font-weight: 700; color: #198754; }
    /* Galería principal */
    #carouselProducto .carousel-item img {
        height: 420px;
        object-fit: cover;
        width: 100%;
        border-radius: .5rem;
    }
    /* Tira de miniaturas */
    .thumb-strip { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .75rem; }
    .thumb-strip .thumbnail {
        width: 70px; height: 70px; object-fit: cover;
        cursor: pointer; border-radius: .375rem;
        border: 2px solid transparent;
        transition: border-color .15s, opacity .15s;
        opacity: .7;
    }
    .thumb-strip .thumbnail:hover { opacity: 1; }
    .thumb-strip .thumbnail.active { border-color: #212529; opacity: 1; }
    /* Placeholder sin imagen */
    .img-placeholder {
        height: 420px; background: #f1f3f8; border-radius: .5rem;
        display: flex; align-items: center; justify-content: center;
        color: #adb5bd;
    }
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
            {{-- Carrusel principal --}}
            <div id="carouselProducto" class="carousel slide" data-bs-ride="false">
                <div class="carousel-inner">
                    @foreach($imagenes as $i => $img)
                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                            <img src="{{ $img->url_imagen }}" alt="{{ $producto->nombre }}">
                        </div>
                    @endforeach
                </div>
                @if($imagenes->count() > 1)
                    <button class="carousel-control-prev" type="button"
                            data-bs-target="#carouselProducto" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button"
                            data-bs-target="#carouselProducto" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                @endif
            </div>

            {{-- Tira de miniaturas --}}
            @if($imagenes->count() > 1)
                <div class="thumb-strip">
                    @foreach($imagenes as $i => $img)
                        <img src="{{ $img->url_imagen }}"
                             class="thumbnail {{ $i === 0 ? 'active' : '' }}"
                             data-bs-target="#carouselProducto"
                             data-bs-slide-to="{{ $i }}"
                             alt="Imagen {{ $i + 1 }}">
                    @endforeach
                </div>
            @endif
        @else
            <div class="img-placeholder">
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

        {{-- Agregar al carrito --}}
        @auth
            <form method="POST" action="{{ route('carrito.agregar', $producto->id_producto) }}" id="formCarrito">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Talla</label>
                    <select name="id_talla" id="selTalla" class="form-select" required>
                        <option value="">Selecciona una talla</option>
                        @foreach($tallas as $talla)
                            @php $stock = $stockPorTalla[$talla->id_talla] ?? 0; @endphp
                            <option value="{{ $talla->id_talla }}"
                                    data-stock="{{ $stock }}"
                                    {{ $stock <= 0 ? 'disabled' : '' }}>
                                {{ $talla->nombre }}
                                @if($stock > 0)
                                    ({{ $stock }} disp.)
                                @else
                                    (sin stock)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Cantidad <span class="text-muted fw-normal small">(máx. 5)</span></label>
                    <input type="number" name="cantidad" id="inputCantidad"
                           class="form-control" value="1" min="1" max="5">
                    <div id="msgCantidad" class="text-danger small mt-1 d-none"></div>
                </div>
                <button type="submit" id="btnAgregar" class="btn btn-success btn-lg w-100">
                    <i class="bi bi-cart-plus me-2"></i>Agregar al carrito
                </button>
            </form>
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
    // ── Miniaturas sincronizan con el carrusel Bootstrap ────────────────────────
    const carouselEl = document.getElementById('carouselProducto');
    if (carouselEl) {
        const bsCarousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);

        // Clic en miniatura → ir al slide correspondiente
        document.querySelectorAll('.thumb-strip .thumbnail').forEach(thumb => {
            thumb.addEventListener('click', () => {
                bsCarousel.to(parseInt(thumb.dataset.bsSlideTo));
            });
        });

        // Al cambiar slide → resaltar la miniatura correspondiente
        carouselEl.addEventListener('slid.bs.carousel', e => {
            document.querySelectorAll('.thumb-strip .thumbnail').forEach((t, i) => {
                t.classList.toggle('active', i === e.to);
            });
        });
    }

    // ── Validación carrito ───────────────────────────────────────────────────────
    const MAX_CARRITO = 5;
    const selTalla     = document.getElementById('selTalla');
    const inputCantidad = document.getElementById('inputCantidad');
    const msgCantidad  = document.getElementById('msgCantidad');
    const btnAgregar   = document.getElementById('btnAgregar');

    function validarCantidad() {
        const opt   = selTalla.options[selTalla.selectedIndex];
        const stock = (opt && opt.value) ? parseInt(opt.dataset.stock || 0) : 0;
        const maxPermitido = Math.min(stock, MAX_CARRITO);
        const cantidad = parseInt(inputCantidad.value) || 0;

        inputCantidad.max = maxPermitido > 0 ? maxPermitido : 1;

        if (!opt || !opt.value || stock === 0) {
            msgCantidad.classList.add('d-none');
            btnAgregar.disabled = (!opt || !opt.value);
            return;
        }

        if (cantidad > maxPermitido) {
            let razon;
            if (cantidad > MAX_CARRITO && cantidad > stock) {
                razon = `Máximo permitido: ${MAX_CARRITO} por pedido y solo hay ${stock} en stock.`;
            } else if (cantidad > stock) {
                razon = `La cantidad de camisas sobrepasa la disponible. Máximo disponible: ${stock} unidades.`;
            } else {
                razon = `El máximo por pedido es ${MAX_CARRITO} unidades.`;
            }
            msgCantidad.textContent = razon;
            msgCantidad.classList.remove('d-none');
            btnAgregar.disabled = true;
        } else {
            msgCantidad.classList.add('d-none');
            btnAgregar.disabled = false;
        }
    }

    if (selTalla) {
        selTalla.addEventListener('change', () => {
            inputCantidad.value = 1;
            validarCantidad();
        });
        inputCantidad.addEventListener('input', validarCantidad);
        validarCantidad();
    }
</script>
@endpush
