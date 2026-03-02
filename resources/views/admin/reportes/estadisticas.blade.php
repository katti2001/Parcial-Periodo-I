@extends('admin.layout')

@section('title', 'Estadísticas')
@section('header', 'Estadísticas Generales')

@push('styles')
<style>
.kpi-card { border-left: 4px solid; }
.kpi-card.ingresos  { border-color: #198754; }
.kpi-card.pedidos   { border-color: #0dcaf0; }
.kpi-card.clientes  { border-color: #6f42c1; }
.kpi-card.ticket    { border-color: #fd7e14; }
.kpi-card.devoluciones { border-color: #dc3545; }
.kpi-card.total     { border-color: #212529; }
.chart-container { position: relative; height: 280px; }
@media print {
    .sidebar, .no-print, nav { display: none !important; }
    .main-content { padding: 0 !important; }
    .card { break-inside: avoid; border: 1px solid #dee2e6 !important; box-shadow: none !important; }
    body { background: #fff !important; }
    .chart-container { height: 220px; }
}
</style>
@endpush

@section('content')

{{-- Botón imprimir --}}
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <p class="text-muted small mb-0">Datos acumulados · Actualizado {{ now()->format('d/m/Y H:i') }}</p>
    <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Imprimir
    </button>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-2">
        <div class="card shadow-sm kpi-card ingresos h-100">
            <div class="card-body py-3">
                <p class="text-muted small mb-1">Ingresos este mes</p>
                <h4 class="fw-bold mb-0">${{ number_format($kpis['ingresos_mes'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-2">
        <div class="card shadow-sm kpi-card total h-100">
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

{{-- Fila 1: Ventas por mes + Pedidos por estado --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3">
                <i class="bi bi-bar-chart-line me-2"></i>Ingresos por mes (últimos 12 meses)
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartVentas"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3">
                <i class="bi bi-pie-chart me-2"></i>Pedidos por estado
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div class="chart-container w-100">
                    <canvas id="chartEstados"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Fila 2: Top productos + Devoluciones por motivo --}}
<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3">
                <i class="bi bi-trophy me-2"></i>Top 8 productos más vendidos
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartProductos"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3">
                <i class="bi bi-arrow-counterclockwise me-2"></i>Devoluciones por motivo
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div class="chart-container w-100">
                    <canvas id="chartDevoluciones"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Fila 3: Nuevos clientes por mes --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold border-0 pt-3">
                <i class="bi bi-people me-2"></i>Nuevos clientes por mes (últimos 12 meses)
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartClientes"></canvas>
                </div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
// ── Datos desde PHP ──────────────────────────────────────────────────────────
const mesesData     = @json(array_values($meses));
const labelsMeses   = mesesData.map(m => m.label);
const totalsMeses   = mesesData.map(m => parseFloat(m.total));
const cantMeses     = mesesData.map(m => parseInt(m.cantidad));

const estadosLabels = @json($pedidosPorEstado->keys()->values());
const estadosData   = @json($pedidosPorEstado->values()->values());

const prodLabels    = @json($topProductosLabels);
const prodUnidades  = @json($topProductosUnidades);

const devMotivos    = @json($devolucionesPorMotivo->keys()->values());
const devData       = @json($devolucionesPorMotivo->values()->values());

const clientesMeses = @json($clientesPorMes);

// ── Paleta consistente ───────────────────────────────────────────────────────
const COLORS = [
    '#212529','#495057','#6c757d','#adb5bd',
    '#0d6efd','#198754','#dc3545','#fd7e14',
    '#6f42c1','#0dcaf0','#ffc107','#20c997',
];

Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";
Chart.defaults.font.size   = 12;

// ── 1. Ingresos por mes ──────────────────────────────────────────────────────
new Chart(document.getElementById('chartVentas'), {
    type: 'bar',
    data: {
        labels: labelsMeses,
        datasets: [
            {
                label: 'Ingresos ($)',
                data: totalsMeses,
                backgroundColor: 'rgba(33,37,41,0.15)',
                borderColor: '#212529',
                borderWidth: 2,
                borderRadius: 4,
                yAxisID: 'y',
            },
            {
                label: 'Facturas',
                data: cantMeses,
                type: 'line',
                borderColor: '#198754',
                backgroundColor: 'rgba(25,135,84,0.1)',
                borderWidth: 2,
                pointRadius: 4,
                tension: 0.3,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: {
            y:  { position: 'left',  ticks: { callback: v => '$' + v.toLocaleString() } },
            y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { stepSize: 1 } }
        },
        plugins: { legend: { position: 'top' } }
    }
});

// ── 2. Pedidos por estado ────────────────────────────────────────────────────
new Chart(document.getElementById('chartEstados'), {
    type: 'doughnut',
    data: {
        labels: estadosLabels,
        datasets: [{ data: estadosData, backgroundColor: COLORS, borderWidth: 2 }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});

// ── 3. Top productos ─────────────────────────────────────────────────────────
new Chart(document.getElementById('chartProductos'), {
    type: 'bar',
    data: {
        labels: prodLabels,
        datasets: [
            {
                label: 'Unidades vendidas',
                data: prodUnidades,
                backgroundColor: COLORS.slice(0, prodLabels.length),
                borderRadius: 4,
            }
        ]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// ── 4. Devoluciones por motivo ───────────────────────────────────────────────
new Chart(document.getElementById('chartDevoluciones'), {
    type: 'doughnut',
    data: {
        labels: devMotivos.length ? devMotivos : ['Sin datos'],
        datasets: [{ data: devData.length ? devData : [1], backgroundColor: COLORS, borderWidth: 2 }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
    }
});

// ── 5. Nuevos clientes por mes ───────────────────────────────────────────────
new Chart(document.getElementById('chartClientes'), {
    type: 'line',
    data: {
        labels: labelsMeses,
        datasets: [{
            label: 'Nuevos clientes',
            data: clientesMeses,
            borderColor: '#6f42c1',
            backgroundColor: 'rgba(111,66,193,0.1)',
            borderWidth: 2,
            pointRadius: 5,
            tension: 0.3,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
@endpush
