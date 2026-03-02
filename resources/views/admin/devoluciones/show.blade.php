@extends('admin.layout')
@section('title', 'Devolución #' . $devolucion->id_devolucion)
@section('header', 'Devolución #' . $devolucion->id_devolucion)

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.devoluciones.index') }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Volver a devoluciones
    </a>
</div>

<div class="row g-4">

    {{-- Columna izquierda — info de la solicitud --}}
    <div class="col-lg-7">

        {{-- Estado actual --}}
        @php
            $cfg = [
                'solicitado' => ['color' => 'warning',  'icono' => 'bi-hourglass-split',  'label' => 'En revisión'],
                'aprobado'   => ['color' => 'success',  'icono' => 'bi-check-circle-fill', 'label' => 'Aprobada'],
                'rechazado'  => ['color' => 'danger',   'icono' => 'bi-x-circle-fill',     'label' => 'Rechazada'],
            ];
            $c = $cfg[$devolucion->estado] ?? ['color' => 'secondary', 'icono' => 'bi-circle', 'label' => $devolucion->estado];
        @endphp

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 text-muted small">Estado</p>
                    <span class="badge bg-{{ $c['color'] }} fs-6">
                        <i class="bi {{ $c['icono'] }} me-1"></i>{{ $c['label'] }}
                    </span>
                </div>
                <div class="text-end">
                    <p class="mb-0 text-muted small">Solicitada</p>
                    <p class="mb-0 fw-semibold">{{ $devolucion->fecha_solicitud->format('d/m/Y H:i') }}</p>
                    @if($devolucion->fecha_resolucion)
                    <p class="mb-0 text-muted small">
                        Resuelta: {{ $devolucion->fecha_resolucion->format('d/m/Y H:i') }}
                    </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Datos del cliente y pedido --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white fw-semibold">
                <i class="bi bi-person me-2"></i>Cliente y Pedido
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1 text-muted small">Cliente</p>
                        <p class="fw-semibold mb-0">
                            {{ optional($devolucion->usuario)->nombre }}
                            {{ optional($devolucion->usuario)->apellido }}
                        </p>
                        <p class="text-muted small mb-0">
                            {{ optional($devolucion->usuario)->email }}
                        </p>
                    </div>
                    <div class="col-6">
                        <p class="mb-1 text-muted small">Pedido original</p>
                        <a href="{{ route('admin.pedidos.show', $devolucion->id_pedido) }}"
                           class="fw-semibold text-decoration-none">
                            #{{ $devolucion->id_pedido }}
                        </a>
                        <p class="text-muted small mb-0">
                            Total: ${{ number_format(optional($devolucion->pedido)->total, 2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Motivo y descripción --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white fw-semibold">
                <i class="bi bi-chat-left-text me-2"></i>Motivo del cliente
            </div>
            <div class="card-body">
                <p class="mb-1">
                    <strong>Motivo:</strong>
                    {{ \App\Models\Devolucion::MOTIVOS[$devolucion->motivo] ?? $devolucion->motivo }}
                </p>
                @if($devolucion->descripcion)
                <p class="mb-0 text-muted small">
                    <strong>Descripción:</strong> {{ $devolucion->descripcion }}
                </p>
                @endif

                {{-- Aviso stock --}}
                @if($devolucion->motivo === 'producto_defectuoso')
                <div class="alert alert-danger small mt-3 mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Producto defectuoso</strong> — Si se aprueba, el stock <strong>NO</strong>
                    será restaurado al inventario.
                </div>
                @else
                <div class="alert alert-info small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Si se aprueba, el stock será restaurado al inventario y registrado en el Kardex.
                </div>
                @endif
            </div>
        </div>

        {{-- Ítems a devolver --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white fw-semibold">
                <i class="bi bi-bag me-2"></i>Ítems solicitados
            </div>
            <ul class="list-group list-group-flush">
                @foreach($devolucion->detalles as $detalle)
                @php $dp = $detalle->detallePedido; @endphp
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-0 fw-semibold">
                            {{ optional($dp->producto)->nombre ?? '(producto eliminado)' }}
                        </p>
                        <small class="text-muted">
                            Talla: {{ optional($dp->talla)->nombre ?? '—' }}
                            &nbsp;|&nbsp;
                            Precio unit.: ${{ number_format($dp->precio_unitario, 2) }}
                        </small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-secondary">{{ $detalle->cantidad_devuelta }} ud(s)</span>
                        <p class="mb-0 small fw-semibold">
                            ${{ number_format($detalle->cantidad_devuelta * $dp->precio_unitario, 2) }}
                        </p>
                    </div>
                </li>
                @endforeach
            </ul>
            {{-- Total estimado --}}
            <div class="card-footer text-end fw-bold">
                @php
                    $totalEstimado = $devolucion->detalles->sum(function($d) {
                        return $d->cantidad_devuelta * optional($d->detallePedido)->precio_unitario;
                    });
                @endphp
                Reembolso estimado: ${{ number_format($totalEstimado, 2) }}
            </div>
        </div>

        {{-- Resolución anterior (si ya fue resuelta) --}}
        @if($devolucion->notas_admin || $devolucion->paypal_refund_id)
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-semibold">
                <i class="bi bi-clipboard-check me-2"></i>Resolución registrada
            </div>
            <div class="card-body">
                @if($devolucion->paypal_refund_id)
                <p class="mb-1 small">
                    <strong>Ref. PayPal:</strong>
                    <code>{{ $devolucion->paypal_refund_id }}</code>
                </p>
                @endif
                @if($devolucion->monto_reembolso)
                <p class="mb-1 small text-success fw-bold">
                    Monto reembolsado: ${{ number_format($devolucion->monto_reembolso, 2) }}
                </p>
                @endif
                @if($devolucion->notas_admin)
                <p class="mb-0 small">
                    <strong>Nota al cliente:</strong> {{ $devolucion->notas_admin }}
                </p>
                @endif
            </div>
        </div>
        @endif

    </div>

    {{-- Columna derecha — acciones --}}
    <div class="col-lg-5">

        @if($devolucion->estado === 'solicitado')

        {{-- Formulario APROBAR --}}
        <div class="card border-success border-2 shadow-sm mb-4">
            <div class="card-header bg-success text-white fw-semibold">
                <i class="bi bi-check-circle me-2"></i>Aprobar devolución
            </div>
            <div class="card-body">
                <form action="{{ route('admin.devoluciones.aprobar', $devolucion->id_devolucion) }}"
                      method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Referencia de reembolso PayPal
                            <span class="text-muted">(opcional — pega el ID desde tu panel PayPal)</span>
                        </label>
                        <input type="text"
                               name="paypal_refund_id"
                               class="form-control form-control-sm"
                               placeholder="Ej: 8B916808LY....."
                               value="{{ old('paypal_refund_id') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Nota para el cliente
                            <span class="text-muted">(opcional)</span>
                        </label>
                        <textarea name="notas_admin"
                                  rows="3"
                                  class="form-control form-control-sm"
                                  placeholder="Ej: Hemos procesado tu reembolso. Recibirás el dinero en 3-5 días hábiles.">{{ old('notas_admin') }}</textarea>
                    </div>

                    <div class="alert alert-warning small mb-3">
                        <i class="bi bi-calculator me-1"></i>
                        Se calculará automáticamente un reembolso de
                        <strong>${{ number_format($totalEstimado, 2) }}</strong>.
                        @if($devolucion->motivo === 'producto_defectuoso')
                            El stock <strong>no</strong> será restaurado (producto defectuoso).
                        @else
                            El stock será restaurado en el Kardex.
                        @endif
                    </div>

                    <button type="submit" class="btn btn-success w-100"
                            onclick="return confirm('¿Confirmar aprobación de la devolución?')">
                        <i class="bi bi-check-circle me-1"></i>Aprobar y procesar
                    </button>
                </form>
            </div>
        </div>

        {{-- Formulario RECHAZAR --}}
        <div class="card border-danger border-2 shadow-sm">
            <div class="card-header bg-danger text-white fw-semibold">
                <i class="bi bi-x-circle me-2"></i>Rechazar devolución
            </div>
            <div class="card-body">
                <form action="{{ route('admin.devoluciones.rechazar', $devolucion->id_devolucion) }}"
                      method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Motivo del rechazo <span class="text-danger">*</span>
                        </label>
                        <textarea name="notas_admin"
                                  rows="3"
                                  class="form-control form-control-sm @error('notas_admin') is-invalid @enderror"
                                  placeholder="Ej: El plazo de devolución ha vencido / El producto no presenta defectos..."
                                  required>{{ old('notas_admin') }}</textarea>
                        @error('notas_admin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-danger w-100"
                            onclick="return confirm('¿Confirmar rechazo de la devolución?')">
                        <i class="bi bi-x-circle me-1"></i>Rechazar solicitud
                    </button>
                </form>
            </div>
        </div>

        @else
        {{-- Ya resuelta --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4 text-muted">
                <i class="bi bi-lock fs-2"></i>
                <p class="mt-2 mb-0">
                    Esta solicitud ya fue
                    <strong class="text-capitalize">{{ $devolucion->estado }}</strong>
                    el {{ $devolucion->fecha_resolucion?->format('d/m/Y') }}.
                </p>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
