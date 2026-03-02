@extends('admin.layout')

@section('title', 'Reporte de Devoluciones')
@section('header', 'Reporte de Devoluciones')

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
        <form method="GET" action="{{ route('admin.reportes.devoluciones') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Desde</label>
                <input type="date" name="desde" value="{{ $desde }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="solicitado"  {{ $estado === 'solicitado'  ? 'selected' : '' }}>Solicitado</option>
                    <option value="aprobado"    {{ $estado === 'aprobado'    ? 'selected' : '' }}>Aprobado</option>
                    <option value="rechazado"   {{ $estado === 'rechazado'   ? 'selected' : '' }}>Rechazado</option>
                </select>
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
    <h5 class="fw-bold mb-0">Reporte de Devoluciones</h5>
    <p class="text-muted small mb-0">Período: {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>
    <p class="text-muted small">Generado el {{ now()->format('d/m/Y H:i') }}</p>
</div>

{{-- Tarjetas resumen --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Total solicitudes</p>
                <h4 class="fw-bold mb-0">{{ $totales['cantidad'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Aprobadas</p>
                <h4 class="fw-bold mb-0 text-success">{{ $totales['aprobadas'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Rechazadas</p>
                <h4 class="fw-bold mb-0 text-danger">{{ $totales['rechazadas'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Monto reembolsado</p>
                <h4 class="fw-bold mb-0">${{ number_format($totales['monto_reembolso'], 2) }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- Tabla de devoluciones --}}
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white fw-bold">
        <i class="bi bi-arrow-counterclockwise me-2"></i>Detalle de devoluciones
        <span class="badge bg-secondary ms-2">{{ $totales['cantidad'] }}</span>
    </div>
    @if($devoluciones->isEmpty())
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            No hay devoluciones en este período.
        </div>
    @else
    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Pedido</th>
                    <th>Motivo</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Solicitud</th>
                    <th class="text-center">Resolución</th>
                    <th class="text-end">Reembolso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($devoluciones as $dev)
                <tr>
                    <td>
                        <a href="{{ route('admin.devoluciones.show', $dev->id_devolucion) }}" class="text-dark fw-semibold text-decoration-none no-print">
                            #{{ $dev->id_devolucion }}
                        </a>
                        <span class="d-none d-print-inline">#{{ $dev->id_devolucion }}</span>
                    </td>
                    <td>
                        {{ optional($dev->usuario)->nombre }} {{ optional($dev->usuario)->apellido }}
                        <div class="text-muted small">{{ optional($dev->usuario)->email }}</div>
                    </td>
                    <td class="text-muted small">#{{ $dev->id_pedido }}</td>
                    <td class="small">{{ $dev->motivoLegible() }}</td>
                    <td class="text-center">
                        @php
                            $badgeColor = match($dev->estado) {
                                'aprobado'   => 'success',
                                'rechazado'  => 'danger',
                                default      => 'warning text-dark',
                            };
                        @endphp
                        <span class="badge bg-{{ $badgeColor }}">{{ ucfirst($dev->estado) }}</span>
                    </td>
                    <td class="text-center text-muted small">
                        {{ optional($dev->fecha_solicitud)->format('d/m/Y') }}
                    </td>
                    <td class="text-center text-muted small">
                        {{ optional($dev->fecha_resolucion)->format('d/m/Y') ?? '—' }}
                    </td>
                    <td class="text-end">
                        @if($dev->monto_reembolso)
                            ${{ number_format($dev->monto_reembolso, 2) }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="7">TOTALES</td>
                    <td class="text-end">${{ number_format($totales['monto_reembolso'], 2) }}</td>
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
