@extends('admin.layout')
@section('title', 'Devoluciones')
@section('header', 'Gestión de Devoluciones')

@section('content')

{{-- Filtros por estado --}}
<div class="row g-3 mb-4">
    @php
        $filtros = [
            ''           => ['label' => 'Todas',      'color' => 'secondary'],
            'solicitado' => ['label' => 'Solicitadas', 'color' => 'warning'],
            'aprobado'   => ['label' => 'Aprobadas',  'color' => 'success'],
            'rechazado'  => ['label' => 'Rechazadas', 'color' => 'danger'],
        ];
        $estadoActual = request('estado', '');
    @endphp

    @foreach($filtros as $valor => $cfg)
    <div class="col-auto">
        <a href="{{ route('admin.devoluciones.index', $valor ? ['estado' => $valor] : []) }}"
           class="btn btn-sm {{ $estadoActual === $valor ? 'btn-'.$cfg['color'] : 'btn-outline-'.$cfg['color'] }}">
            {{ $cfg['label'] }}
            @if($valor !== '' && isset($totales[$valor]))
                <span class="badge bg-white text-{{ $cfg['color'] }} ms-1">{{ $totales[$valor] }}</span>
            @endif
        </a>
    </div>
    @endforeach

    {{-- Urgentes: solicitadas hace más de 48h --}}
    @if(isset($totales['solicitado']) && $totales['solicitado'] > 0)
    <div class="col-auto ms-auto">
        <span class="text-muted small">
            <i class="bi bi-clock-history me-1 text-warning"></i>
            {{ $totales['solicitado'] }} solicitud(es) pendiente(s) de revisión
        </span>
    </div>
    @endif
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Pedido</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                    <th>Reembolso</th>
                    <th>Fecha solicitud</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($devoluciones as $dev)
                @php
                    $estadoColor = [
                        'solicitado' => 'warning text-dark',
                        'aprobado'   => 'success',
                        'rechazado'  => 'danger',
                    ][$dev->estado] ?? 'secondary';

                    // Marcar urgencia si lleva más de 48h sin resolución
                    $urgente = $dev->estado === 'solicitado'
                        && $dev->fecha_solicitud->diffInHours(now()) > 48;
                @endphp
                <tr class="{{ $urgente ? 'table-warning' : '' }}">
                    <td>{{ $dev->id_devolucion }}</td>
                    <td>
                        {{ optional($dev->usuario)->nombre }}
                        {{ optional($dev->usuario)->apellido }}
                        @if($urgente)
                            <i class="bi bi-exclamation-triangle-fill text-danger ms-1"
                               title="Más de 48h sin revisión"></i>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.pedidos.show', $dev->id_pedido) }}"
                           class="text-decoration-none">
                            #{{ $dev->id_pedido }}
                        </a>
                    </td>
                    <td>
                        <span class="small">
                            {{ \App\Models\Devolucion::MOTIVOS[$dev->motivo] ?? $dev->motivo }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $estadoColor }} text-capitalize">
                            {{ $dev->estado }}
                        </span>
                    </td>
                    <td>
                        @if($dev->monto_reembolso)
                            <span class="text-success fw-semibold">
                                ${{ number_format($dev->monto_reembolso, 2) }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $dev->fecha_solicitud->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.devoluciones.show', $dev->id_devolucion) }}"
                           class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        No hay devoluciones{{ request('estado') ? ' con estado "'.request('estado').'"' : '' }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($devoluciones->hasPages())
    <div class="card-footer bg-white border-0">
        {{ $devoluciones->links() }}
    </div>
    @endif
</div>
@endsection
