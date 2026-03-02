@extends('admin.layout')

@section('title', 'Reporte de Ventas')
@section('header', 'Reporte de Ventas por Período')

@push('styles')
<style>
@media print {
    .sidebar, .no-print, .btn, nav, form { display: none !important; }
    .main-content { padding: 0 !important; }
    .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
    body { background: #fff !important; }
}
</style>
@endpush

@section('content')

{{-- Filtros --}}
<div class="card shadow-sm mb-4 no-print">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reportes.ventas') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}" class="form-control">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark flex-grow-1">
                    <i class="bi bi-search me-1"></i> Filtrar
                </button>
                <button type="button" class="btn btn-outline-dark" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Imprimir
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Encabezado del reporte (visible al imprimir) --}}
<div class="mb-4">
    <h5 class="fw-bold mb-0">Reporte de Ventas</h5>
    <p class="text-muted small mb-0">Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>
    <p class="text-muted small">Generado el {{ now()->format('d/m/Y H:i') }}</p>
</div>

{{-- Tarjetas resumen --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Facturas</p>
                <h4 class="fw-bold mb-0">{{ $totales['cantidad'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Subtotal</p>
                <h4 class="fw-bold mb-0">${{ number_format($totales['subtotal'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Descuentos</p>
                <h4 class="fw-bold mb-0 text-success">-${{ number_format($totales['descuento'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Total cobrado</p>
                <h4 class="fw-bold mb-0">${{ number_format($totales['total'], 2) }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- Tabla de facturas --}}
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white fw-bold">
        <i class="bi bi-receipt me-2"></i>Detalle de facturas
        <span class="badge bg-secondary ms-2">{{ $totales['cantidad'] }}</span>
    </div>
    @if($facturas->isEmpty())
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            No hay facturas en este período.
        </div>
    @else
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead class="table-light">
                <tr>
                    <th>N° Factura</th>
                    <th>Cliente</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Emisión</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Descuento</th>
                    <th class="text-end">Impuesto</th>
                    <th class="text-end">Envío</th>
                    <th class="text-end fw-bold">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facturas as $factura)
                <tr>
                    <td>
                        <a href="{{ route('admin.facturas.show', $factura->id_factura) }}" class="text-dark fw-semibold text-decoration-none no-print">
                            {{ $factura->numero }}
                        </a>
                        <span class="d-none d-print-inline">{{ $factura->numero }}</span>
                    </td>
                    <td>
                        {{ optional($factura->usuario)->nombre }} {{ optional($factura->usuario)->apellido }}
                        <div class="text-muted small">{{ optional($factura->usuario)->email }}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $factura->estado === 'pagada' ? 'success' : 'secondary' }}">
                            {{ ucfirst($factura->estado) }}
                        </span>
                    </td>
                    <td class="text-center text-muted small">
                        {{ optional($factura->fecha_emision)->format('d/m/Y') }}
                    </td>
                    <td class="text-end">${{ number_format($factura->subtotal, 2) }}</td>
                    <td class="text-end text-success">
                        @if($factura->descuento > 0)-${{ number_format($factura->descuento, 2) }}@else —@endif
                    </td>
                    <td class="text-end">${{ number_format($factura->impuesto, 2) }}</td>
                    <td class="text-end">${{ number_format($factura->costo_envio, 2) }}</td>
                    <td class="text-end fw-bold">${{ number_format($factura->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="4">TOTALES</td>
                    <td class="text-end">${{ number_format($totales['subtotal'], 2) }}</td>
                    <td class="text-end text-success">-${{ number_format($totales['descuento'], 2) }}</td>
                    <td class="text-end">${{ number_format($totales['impuesto'], 2) }}</td>
                    <td class="text-end">${{ number_format($totales['costo_envio'], 2) }}</td>
                    <td class="text-end">${{ number_format($totales['total'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>

<div class="mt-3 no-print">
    <a href="{{ route('admin.reportes.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a reportes
    </a>
</div>
@endsection
