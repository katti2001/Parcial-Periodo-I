@extends('admin.layout')

@section('title', 'Reportes')
@section('header', 'Reportes')

@section('content')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="mb-3">
                    <span class="bg-dark text-white rounded p-2 d-inline-block mb-2">
                        <i class="bi bi-bar-chart-line fs-4"></i>
                    </span>
                    <h5 class="fw-bold mb-1">Ventas por período</h5>
                    <p class="text-muted small mb-0">Resumen de facturas emitidas y pagadas en un rango de fechas. Incluye subtotales, descuentos, impuestos y totales.</p>
                </div>
                <a href="{{ route('admin.reportes.ventas') }}" class="btn btn-dark mt-auto">
                    <i class="bi bi-arrow-right me-1"></i> Ver reporte
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="mb-3">
                    <span class="bg-dark text-white rounded p-2 d-inline-block mb-2">
                        <i class="bi bi-box-seam fs-4"></i>
                    </span>
                    <h5 class="fw-bold mb-1">Productos más vendidos</h5>
                    <p class="text-muted small mb-0">Ranking de productos por unidades vendidas e ingresos generados en el período seleccionado.</p>
                </div>
                <a href="{{ route('admin.reportes.productos') }}" class="btn btn-dark mt-auto">
                    <i class="bi bi-arrow-right me-1"></i> Ver reporte
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="mb-3">
                    <span class="bg-dark text-white rounded p-2 d-inline-block mb-2">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </span>
                    <h5 class="fw-bold mb-1">Devoluciones</h5>
                    <p class="text-muted small mb-0">Listado de devoluciones por período con estado, motivo y monto de reembolso.</p>
                </div>
                <a href="{{ route('admin.reportes.devoluciones') }}" class="btn btn-dark mt-auto">
                    <i class="bi bi-arrow-right me-1"></i> Ver reporte
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="mb-3">
                    <span class="bg-dark text-white rounded p-2 d-inline-block mb-2">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </span>
                    <h5 class="fw-bold mb-1">Estadísticas generales</h5>
                    <p class="text-muted small mb-0">KPIs del negocio: ingresos por mes, pedidos por estado, top productos y resumen de devoluciones.</p>
                </div>
                <a href="{{ route('admin.reportes.estadisticas') }}" class="btn btn-dark mt-auto">
                    <i class="bi bi-arrow-right me-1"></i> Ver estadísticas
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
