@extends('almacen.layout')
@section('title', 'Compra #' . $compra->id_compra)
@section('header', 'Compra #' . $compra->id_compra)

@section('header-actions')
    <div class="d-flex gap-2">
        @if($compra->estado === 'solicitado')
            <form method="POST" action="{{ route('almacen.compras.recibir', $compra->id_compra) }}"
                  onsubmit="return confirm('¿Marcar como recibida e ingresar al inventario?')">
                @csrf @method('PATCH')
                <button class="btn btn-success btn-sm">
                    <i class="bi bi-check2-circle me-1"></i>Marcar como recibida
                </button>
            </form>
        @endif
        <a href="{{ route('almacen.compras.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>
@endsection

@push('styles')
<style>
/* ── Estado banner ──────────────────────────────── */
.estado-banner {
    border-radius: .5rem;
    padding: .6rem 1.2rem;
    font-weight: 600;
    font-size: .9rem;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
}
.estado-banner.recibido   { background:#d1f0e0; color:#0f5132; }
.estado-banner.solicitado { background:#fff3cd; color:#856404; }
.estado-banner.cancelado  { background:#f8d7da; color:#842029; }

/* ── Carrusel de imágenes ───────────────────────── */
.producto-carousel .carousel-inner { border-radius: .5rem; overflow: hidden; }
.producto-carousel .carousel-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}
.producto-carousel .carousel-control-prev,
.producto-carousel .carousel-control-next {
    width: 28px;
    opacity: .7;
}
.producto-carousel .carousel-indicators [data-bs-target] {
    width: 8px; height: 8px; border-radius: 50%;
}
.sin-imagen {
    height: 120px;
    background: #f1f3f8;
    border-radius: .5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
}

/* ── Tabla de productos ─────────────────────────── */
.detalle-table th { font-size: .78rem; text-transform: uppercase; letter-spacing: .03em; color: #6c757d; }
.detalle-table td { vertical-align: middle; }
.stock-badge { min-width: 42px; text-align: center; font-size: .78rem; }
.producto-cell { max-width: 260px; }
.producto-cell .sku { font-size: .72rem; color: #6c757d; }
</style>
@endpush

@section('content')

{{-- Banner de estado --}}
@php
    $estadoLabel = match($compra->estado) {
        'recibido'   => 'Recibida · inventario actualizado',
        'solicitado' => 'Pendiente de recepción',
        'cancelado'  => 'Cancelada',
        default      => ucfirst($compra->estado),
    };
    $estadoIcon = match($compra->estado) {
        'recibido'   => 'bi-check-circle-fill',
        'solicitado' => 'bi-clock-fill',
        'cancelado'  => 'bi-x-circle-fill',
        default      => 'bi-circle',
    };
@endphp

<div class="mb-4">
    <span class="estado-banner {{ $compra->estado }}">
        <i class="bi {{ $estadoIcon }}"></i>{{ $estadoLabel }}
    </span>
</div>

<div class="row g-4">

    {{-- ── Tabla de productos ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-0 pt-3 pb-2">
                <i class="bi bi-box-seam me-2 text-primary"></i>Productos comprados
                <span class="badge bg-secondary ms-1" style="font-size:.75rem;">{{ $compra->detalle_compras->count() }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table detalle-table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:90px">Imagen</th>
                            <th>Producto</th>
                            <th>Talla / Lote</th>
                            <th class="text-center">Comprado</th>
                            <th class="text-center">Stock<br>restante</th>
                            <th class="text-end">Costo</th>
                            <th class="text-end pe-3">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($compra->detalle_compras as $d)
                        @php
                            $producto  = $d->producto;
                            $imagenes  = $producto ? $producto->imagenes_productos : collect();
                            $carouselId = 'carousel-' . $d->id_detalle_compra;
                            $stockPct  = $d->cantidad_comprada > 0
                                ? ($d->cantidad_restante / $d->cantidad_comprada) * 100
                                : 0;
                            $stockColor = $stockPct > 50 ? 'success' : ($stockPct > 0 ? 'warning' : 'secondary');
                        @endphp
                        <tr>
                            {{-- Carrusel de imágenes --}}
                            <td class="ps-3">
                                @if($imagenes->isNotEmpty())
                                    <div id="{{ $carouselId }}" class="carousel slide producto-carousel"
                                         data-bs-ride="{{ $imagenes->count() > 1 ? 'carousel' : 'false' }}"
                                         style="width:80px;">

                                        {{-- Indicadores (solo si > 1 imagen) --}}
                                        @if($imagenes->count() > 1)
                                        <div class="carousel-indicators" style="bottom:-14px;">
                                            @foreach($imagenes as $i => $img)
                                                <button type="button"
                                                        data-bs-target="#{{ $carouselId }}"
                                                        data-bs-slide-to="{{ $i }}"
                                                        class="{{ $i === 0 ? 'active' : '' }}"
                                                        aria-label="Imagen {{ $i + 1 }}">
                                                </button>
                                            @endforeach
                                        </div>
                                        @endif

                                        <div class="carousel-inner" style="border-radius:.375rem;">
                                            @foreach($imagenes as $i => $img)
                                                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                                    <img src="{{ $img->url_imagen }}"
                                                         alt="{{ optional($producto)->nombre }}"
                                                         style="width:80px;height:80px;object-fit:cover;display:block;">
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Controles (solo si > 1 imagen) --}}
                                        @if($imagenes->count() > 1)
                                        <button class="carousel-control-prev" type="button"
                                                data-bs-target="#{{ $carouselId }}" data-bs-slide="prev"
                                                style="width:20px;">
                                            <span class="carousel-control-prev-icon" style="width:14px;height:14px;"></span>
                                        </button>
                                        <button class="carousel-control-next" type="button"
                                                data-bs-target="#{{ $carouselId }}" data-bs-slide="next"
                                                style="width:20px;">
                                            <span class="carousel-control-next-icon" style="width:14px;height:14px;"></span>
                                        </button>
                                        @endif
                                    </div>
                                @else
                                    <div class="sin-imagen" style="width:80px;height:80px;font-size:1.4rem;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </td>

                            {{-- Producto --}}
                            <td class="producto-cell">
                                @if($producto)
                                    <div class="fw-semibold">{{ $producto->nombre }}</div>
                                    <div class="sku">SKU: {{ $producto->sku_base }}</div>
                                    @if($producto->categoria)
                                        <span class="badge bg-light text-secondary border" style="font-size:.68rem;">
                                            {{ $producto->categoria->nombre }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted fst-italic">(producto eliminado)</span>
                                @endif
                            </td>

                            {{-- Talla --}}
                            <td>
                                <span class="badge bg-dark" style="font-size:.8rem;">
                                    {{ optional($d->talla)->nombre ?? '—' }}
                                </span>
                                @if($d->sku_lote)
                                    <div class="mt-1 small text-muted">Lote: {{ $d->sku_lote }}</div>
                                @endif
                            </td>

                            {{-- Cantidad comprada --}}
                            <td class="text-center fw-semibold">{{ $d->cantidad_comprada }}</td>

                            {{-- Stock restante --}}
                            <td class="text-center">
                                <span class="badge bg-{{ $stockColor }} stock-badge">
                                    {{ $d->cantidad_restante }}
                                </span>
                                @if($d->cantidad_comprada > 0)
                                <div class="progress mt-1" style="height:3px;width:42px;margin:0 auto;">
                                    <div class="progress-bar bg-{{ $stockColor }}"
                                         style="width:{{ $stockPct }}%"></div>
                                </div>
                                @endif
                            </td>

                            {{-- Costo --}}
                            <td class="text-end">${{ number_format($d->costo_unitario, 2) }}</td>

                            {{-- Subtotal --}}
                            <td class="text-end pe-3 fw-semibold">
                                ${{ number_format($d->cantidad_comprada * $d->costo_unitario, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end fw-bold pe-3">Total compra:</td>
                            <td class="text-end fw-bold pe-3 text-success fs-6">
                                ${{ number_format($compra->total_compra, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Panel de información ── --}}
    <div class="col-lg-4 d-flex flex-column gap-3">

        {{-- Datos generales --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-0 pt-3 pb-2">
                <i class="bi bi-info-circle me-2 text-primary"></i>Información
            </div>
            <div class="card-body">
                <dl class="row mb-0" style="font-size:.88rem;">
                    <dt class="col-5 text-muted fw-normal">Proveedor</dt>
                    <dd class="col-7 fw-semibold mb-2">{{ optional($compra->proveedor)->nombre_empresa ?? '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Fecha</dt>
                    <dd class="col-7 mb-2">{{ $compra->fecha_compra ? $compra->fecha_compra->format('d/m/Y') : '—' }}</dd>

                    <dt class="col-5 text-muted fw-normal">Factura</dt>
                    <dd class="col-7 mb-2">
                        @if($compra->numero_factura_proveedor)
                            <code>{{ $compra->numero_factura_proveedor }}</code>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </dd>

                    <dt class="col-5 text-muted fw-normal">Estado</dt>
                    <dd class="col-7 mb-0">
                        <span class="estado-banner {{ $compra->estado }}" style="padding:.25em .7em;font-size:.78rem;">
                            <i class="bi {{ $estadoIcon }}"></i>{{ ucfirst($compra->estado) }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Resumen de unidades --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-0 pt-3 pb-2">
                <i class="bi bi-bar-chart me-2 text-primary"></i>Resumen de stock
            </div>
            <div class="card-body">
                @php
                    $totalComprado  = $compra->detalle_compras->sum('cantidad_comprada');
                    $totalRestante  = $compra->detalle_compras->sum('cantidad_restante');
                    $totalVendido   = $totalComprado - $totalRestante;
                    $pctVendido     = $totalComprado > 0 ? ($totalVendido / $totalComprado) * 100 : 0;
                @endphp
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Vendido / Comprado</span>
                    <span class="fw-semibold">{{ $totalVendido }} / {{ $totalComprado }}</span>
                </div>
                <div class="progress mb-3" style="height:8px;">
                    <div class="progress-bar bg-success" style="width:{{ $pctVendido }}%"></div>
                </div>

                <div class="row text-center g-2">
                    <div class="col-4">
                        <div class="fw-bold text-primary">{{ $totalComprado }}</div>
                        <div class="small text-muted">Compradas</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-success">{{ $totalVendido }}</div>
                        <div class="small text-muted">Vendidas</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-warning">{{ $totalRestante }}</div>
                        <div class="small text-muted">En stock</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
