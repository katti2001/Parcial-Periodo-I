@extends('admin.layout')

@section('title', 'Estadísticas')
@section('header', 'Estadísticas Generales')

@push('styles')
<style>
.kpi-card { border-left: 4px solid; }
.kpi-card.ingresos-mes  { border-color: #198754; }
.kpi-card.ingresos-total{ border-color: #212529; }
.kpi-card.pedidos       { border-color: #0dcaf0; }
.kpi-card.clientes      { border-color: #6f42c1; }
.kpi-card.ticket        { border-color: #fd7e14; }
.kpi-card.devoluciones  { border-color: #dc3545; }

/* Barras de meses con altura proporcional al valor */
.bar-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    flex: 1;
    min-width: 0;
}
.bar-fill {
    width: 100%;
    background: #212529;
    border-radius: 3px 3px 0 0;
    transition: height .3s;
    min-height: 2px;
}
.bar-label {
    font-size: 0.6rem;
    color: #6c757d;
    text-align: center;
    margin-top: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}
.bar-value {
    font-size: 0.6rem;
    color: #212529;
    font-weight: 600;
    text-align: center;
    margin-bottom: 2px;
}
.bar-chart-wrap {
    display: flex;
    align-items: flex-end;
    gap: 4px;
    height: 120px;
    padding: 0 4px;
}

@media print {
    .sidebar, .no-print, nav { display: none !important; }
    .main-content { padding: 0 !important; }
    .card { break-inside: avoid; border: 1px solid #dee2e6 !important; box-shadow: none !important; }
    body { background: #fff !important; }
}
</style>
@endpush

@section('content')

{{-- Botón imprimir --}}
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <p class="text-muted small mb-0">Actualizado {{ now()->format('d/m/Y H:i') }}</p>
    <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Imprimir
    </button>
</div>

{{-- ── KPIs ──────────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-2">
        <div class="card shadow-sm kpi-card ingresos-mes h-100">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Ingresos este mes</p>
                <h4 class="fw-bold mb-0">${{ number_format($kpis['ingresos_mes'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card shadow-sm kpi-card ingresos-total h-100">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Ingresos totales</p>
                <h4 class="fw-bold mb-0">${{ number_format($kpis['ingresos_total'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card shadow-sm kpi-card pedidos h-100">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Pedidos este mes</p>
                <h4 class="fw-bold mb-0">{{ $kpis['pedidos_mes'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card shadow-sm kpi-card clientes h-100">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Clientes totales</p>
                <h4 class="fw-bold mb-0">{{ $kpis['clientes_total'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card shadow-sm kpi-card ticket h-100">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Ticket promedio</p>
                <h4 class="fw-bold mb-0">${{ number_format($kpis['ticket_promedio'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card shadow-sm kpi-card devoluciones h-100">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Devoluciones este mes</p>
                <h4 class="fw-bold mb-0">{{ $kpis['devoluciones_mes'] }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- ── Fila 1: Ingresos por mes + Pedidos por estado ────────────────────────── --}}
<div class="row g-3 mb-3">

    {{-- Ingresos por mes — barras verticales con CSS --}}
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3">
                <i class="bi bi-bar-chart-line me-2"></i>Ingresos por mes
                <span class="text-muted fw-normal small">(últimos 12 meses)</span>
            </div>
            <div class="card-body">
                <div class="bar-chart-wrap">
                    @foreach($meses as $m)
                    @php $pct = $maxVenta > 0 ? round(($m['total'] / $maxVenta) * 100) : 0; @endphp
                    <div class="bar-col">
                        <div class="bar-value">
                            @if($m['total'] > 0)${{ number_format($m['total'], 0) }}@endif
                        </div>
                        <div class="bar-fill" style="height:{{ $pct }}%"
                             title="{{ $m['label'] }}: ${{ number_format($m['total'], 2) }} ({{ $m['cantidad'] }} facturas)">
                        </div>
                        <div class="bar-label">{{ $m['label'] }}</div>
                    </div>
                    @endforeach
                </div>
                {{-- Tabla resumen debajo --}}
                <div class="table-responsive mt-3">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Mes</th>
                                <th class="text-center">Facturas</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_reverse($meses) as $m)
                            <tr>
                                <td>{{ $m['label'] }}</td>
                                <td class="text-center">{{ $m['cantidad'] }}</td>
                                <td class="text-end fw-semibold">${{ number_format($m['total'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Pedidos por estado — barras de progreso Bootstrap --}}
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3">
                <i class="bi bi-pie-chart me-2"></i>Pedidos por estado
            </div>
            <div class="card-body">
                @php
                    $estadoColores = [
                        'pendiente'   => 'warning',
                        'procesando'  => 'info',
                        'enviado'     => 'primary',
                        'entregado'   => 'success',
                        'cancelado'   => 'danger',
                    ];
                @endphp
                @forelse($pedidosPorEstado as $row)
                @php
                    $pct   = round(($row->cantidad / $totalPedidos) * 100);
                    $color = $estadoColores[$row->estado_pedido] ?? 'secondary';
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold text-capitalize">{{ $row->estado_pedido }}</span>
                        <span class="text-muted">{{ $row->cantidad }} &nbsp;({{ $pct }}%)</span>
                    </div>
                    <div class="progress" style="height:10px">
                        <div class="progress-bar bg-{{ $color }}" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-muted small text-center py-3">Sin datos</p>
                @endforelse
                <div class="border-top pt-2 mt-2 d-flex justify-content-between small">
                    <span class="text-muted">Total pedidos</span>
                    <span class="fw-bold">{{ $totalPedidos }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Fila 2: Top productos + Devoluciones por motivo ─────────────────────── --}}
<div class="row g-3 mb-3">

    {{-- Top 8 productos — barras de progreso Bootstrap --}}
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3">
                <i class="bi bi-trophy me-2"></i>Top 8 productos más vendidos
            </div>
            <div class="card-body">
                @forelse($topProductos as $i => $item)
                @php $pct = round(($item->total_unidades / $maxUnidades) * 100); @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold">
                            <span class="text-muted me-1">#{{ $i + 1 }}</span>
                            {{ optional($item->producto)->nombre ?? '(eliminado)' }}
                        </span>
                        <span class="text-muted">{{ number_format($item->total_unidades) }} uds · ${{ number_format($item->total_ingresos, 2) }}</span>
                    </div>
                    <div class="progress" style="height:10px">
                        <div class="progress-bar bg-dark" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-muted small text-center py-3">Sin datos</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Devoluciones por motivo — barras de progreso Bootstrap --}}
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3">
                <i class="bi bi-arrow-counterclockwise me-2"></i>Devoluciones por motivo
            </div>
            <div class="card-body">
                @forelse($devolucionesPorMotivo as $dev)
                @php $pct = round(($dev['cantidad'] / $totalDevoluciones) * 100); @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold">{{ $dev['label'] }}</span>
                        <span class="text-muted">{{ $dev['cantidad'] }} &nbsp;({{ $pct }}%)</span>
                    </div>
                    <div class="progress" style="height:10px">
                        <div class="progress-bar bg-danger" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-muted small text-center py-3">Sin devoluciones registradas</p>
                @endforelse
                @if($devolucionesPorMotivo->isNotEmpty())
                <div class="border-top pt-2 mt-2 d-flex justify-content-between small">
                    <span class="text-muted">Total devoluciones</span>
                    <span class="fw-bold">{{ $totalDevoluciones }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>


<div class="mt-2 no-print">
    <a href="{{ route('admin.reportes.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver a reportes
    </a>
</div>
@endsection
