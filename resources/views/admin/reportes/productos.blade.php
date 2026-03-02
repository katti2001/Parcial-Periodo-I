@extends('admin.layout')

@section('title', 'Productos Más Vendidos')
@section('header', 'Productos Más Vendidos')

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
        <form method="GET" action="{{ route('admin.reportes.productos') }}" class="row g-3 align-items-end">
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

{{-- Encabezado del reporte --}}
<div class="mb-4">
    <h5 class="fw-bold mb-0">Productos Más Vendidos</h5>
    <p class="text-muted small mb-0">Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>
    <p class="text-muted small">Generado el {{ now()->format('d/m/Y H:i') }}</p>
</div>

{{-- Tabla --}}
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white fw-bold">
        <i class="bi bi-box-seam me-2"></i>Ranking de productos
        <span class="badge bg-secondary ms-2">{{ $productos->count() }}</span>
    </div>
    @if($productos->isEmpty())
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            No hay ventas registradas en este período.
        </div>
    @else
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width:50px">#</th>
                    <th>Producto</th>
                    <th class="text-center">Unidades vendidas</th>
                    <th class="text-end">Ingresos totales</th>
                    <th class="text-end">Precio promedio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $i => $item)
                <tr>
                    <td class="text-center fw-bold text-muted">{{ $i + 1 }}</td>
                    <td>
                        <span class="fw-semibold">
                            {{ optional($item->producto)->nombre ?? '(producto eliminado)' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-dark">{{ number_format($item->total_unidades) }}</span>
                    </td>
                    <td class="text-end fw-bold">${{ number_format($item->total_ingresos, 2) }}</td>
                    <td class="text-end text-muted">
                        ${{ $item->total_unidades > 0 ? number_format($item->total_ingresos / $item->total_unidades, 2) : '0.00' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2">TOTALES</td>
                    <td class="text-center">{{ number_format($productos->sum('total_unidades')) }}</td>
                    <td class="text-end">${{ number_format($productos->sum('total_ingresos'), 2) }}</td>
                    <td></td>
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
