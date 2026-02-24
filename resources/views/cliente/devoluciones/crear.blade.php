@extends('layouts.app')

@section('title', 'Solicitar Devolución — Pedido #' . $pedido->id_pedido)

@section('content')
<div class="mb-3">
    <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Volver al pedido
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <h5 class="fw-bold mb-1">
            <i class="bi bi-arrow-counterclockwise me-2"></i>Solicitar devolución
        </h5>
        <p class="text-muted small mb-4">Pedido #{{ $pedido->id_pedido }} — selecciona los ítems que deseas devolver.</p>

        <form action="{{ route('devoluciones.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id_pedido" value="{{ $pedido->id_pedido }}">

            {{-- Ítems del pedido --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white fw-semibold">
                    <i class="bi bi-bag me-2"></i>Selecciona los ítems a devolver
                </div>
                <div class="card-body p-0">
                    @foreach($pedido->detalle_pedidos as $i => $detalle)
                    <div class="p-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            {{-- Checkbox de selección --}}
                            <div class="form-check mb-0">
                                <input class="form-check-input item-check" type="checkbox"
                                       id="item_check_{{ $i }}"
                                       data-index="{{ $i }}">
                            </div>

                            {{-- Info producto --}}
                            <div class="flex-grow-1">
                                <p class="mb-0 fw-semibold">
                                    {{ optional($detalle->producto)->nombre ?? '(producto eliminado)' }}
                                </p>
                                <small class="text-muted">
                                    Talla: {{ optional($detalle->talla)->nombre ?? '—' }}
                                    &nbsp;|&nbsp;
                                    Comprado: {{ $detalle->cantidad }} unidad(es)
                                </small>
                            </div>

                            {{-- Cantidad a devolver --}}
                            <div style="width:110px">
                                <label class="form-label small text-muted mb-1">Cant. a devolver</label>
                                <input type="number"
                                       name="items[{{ $i }}][cantidad_devuelta]"
                                       class="form-control form-control-sm item-qty"
                                       min="1" max="{{ $detalle->cantidad }}"
                                       value="1"
                                       data-index="{{ $i }}"
                                       disabled>
                                <input type="hidden"
                                       name="items[{{ $i }}][id_detalle_pedido]"
                                       value="{{ $detalle->id_detalle_pedido }}"
                                       class="item-hidden"
                                       data-index="{{ $i }}"
                                       disabled>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Motivo --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <label class="form-label fw-semibold">
                        Motivo de la devolución <span class="text-danger">*</span>
                    </label>
                    <select name="motivo" class="form-select @error('motivo') is-invalid @enderror" required>
                        <option value="">— Selecciona un motivo —</option>
                        @foreach($motivos as $clave => $etiqueta)
                            <option value="{{ $clave }}" {{ old('motivo') === $clave ? 'selected' : '' }}>
                                {{ $etiqueta }}
                            </option>
                        @endforeach
                    </select>
                    @error('motivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <label class="form-label fw-semibold mt-3">
                        Descripción adicional <span class="text-muted small">(opcional)</span>
                    </label>
                    <textarea name="descripcion" rows="3"
                              class="form-control @error('descripcion') is-invalid @enderror"
                              placeholder="Describe el problema con más detalle...">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Aviso --}}
            <div class="alert alert-info small">
                <i class="bi bi-info-circle me-1"></i>
                Una vez enviada la solicitud, el equipo de la tienda la revisará y te notificará la resolución.
                Si el producto es <strong>defectuoso</strong>, no regresará al stock pero el reembolso
                será procesado igualmente.
            </div>

            @if($errors->any())
                <div class="alert alert-danger small">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="d-flex gap-3 justify-content-end">
                <a href="{{ route('pedidos.show', $pedido->id_pedido) }}"
                   class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" id="btn-submit" class="btn btn-warning" disabled>
                    <i class="bi bi-send me-1"></i>Enviar solicitud
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // Habilitar/deshabilitar campos según checkbox
    document.querySelectorAll('.item-check').forEach(function(chk) {
        chk.addEventListener('change', function() {
            const idx   = this.dataset.index;
            const qty   = document.querySelector('.item-qty[data-index="' + idx + '"]');
            const hid   = document.querySelector('.item-hidden[data-index="' + idx + '"]');
            qty.disabled = !this.checked;
            hid.disabled = !this.checked;
            actualizarBoton();
        });
    });

    function actualizarBoton() {
        const alguno = document.querySelectorAll('.item-check:checked').length > 0;
        document.getElementById('btn-submit').disabled = !alguno;
    }
</script>
@endpush
