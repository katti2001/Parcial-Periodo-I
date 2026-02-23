@extends('layouts.app')

@section('title', 'Checkout')

@push('styles')
<style>
    #paypal-button-container { min-height: 50px; }
    .resumen-item { font-size: .95rem; }
</style>
@endpush

@section('content')
<h2 class="mb-4"><i class="bi bi-lock me-2"></i>Checkout</h2>

<div class="row g-4">
    {{-- Resumen de productos --}}
    <div class="col-lg-7">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-bag me-2"></i>Productos
            </div>
            <ul class="list-group list-group-flush">
                @foreach($carrito as $item)
                <li class="list-group-item d-flex justify-content-between align-items-center resumen-item">
                    <div>
                        <span class="fw-semibold">{{ $item['nombre'] }}</span>
                        <small class="text-muted ms-2">Talla: {{ $item['talla'] }} × {{ $item['cantidad'] }}</small>
                    </div>
                    <span>${{ number_format($item['precio'] * $item['cantidad'], 2) }}</span>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Cupón --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-ticket-perforated me-2"></i>Cupón de descuento</h6>
                @if($cupon)
                    <div class="alert alert-success py-2 mb-0">
                        <i class="bi bi-check-circle me-2"></i>
                        Cupón <strong>{{ $cupon->codigo }}</strong> aplicado — descuento: ${{ number_format($monto_descuento, 2) }}
                    </div>
                @else
                    <form method="POST" action="{{ route('checkout.cupon') }}" class="d-flex gap-2">
                        @csrf
                        <input type="text" name="codigo" class="form-control" placeholder="Código de cupón">
                        <button class="btn btn-outline-dark">Aplicar</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Totales + PayPal --}}
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-receipt me-2"></i>Total a pagar
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <span>${{ number_format($subtotal, 2) }}</span>
                </div>
                @if($monto_descuento > 0)
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Descuento ({{ $cupon->codigo }})</span>
                    <span>-${{ number_format($monto_descuento, 2) }}</span>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between fw-bold fs-5 mb-4">
                    <span>Total</span>
                    <span>${{ number_format($total, 2) }}</span>
                </div>

                {{-- Botón PayPal --}}
                <div id="paypal-button-container"></div>
                <div id="paypal-error" class="alert alert-danger mt-2 d-none"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&currency=USD"></script>
<script>
    paypal.Buttons({
        createOrder: function() {
            return fetch('{{ route('checkout.crear-orden') }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(function(res) {
                if (!res.ok) {
                    return res.text().then(function(t) { throw new Error('Server error ' + res.status + ': ' + t.substring(0, 200)); });
                }
                return res.json();
            })
            .then(function(data) {
                if (data.error) throw new Error(data.error);
                return data.id;
            });
        },
        onApprove: function(data) {
            return fetch('{{ url('checkout/capturar') }}/' + data.orderID, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(function(res) {
                if (!res.ok) {
                    return res.text().then(function(t) { throw new Error('Server error ' + res.status + ': ' + t.substring(0, 200)); });
                }
                return res.json();
            })
            .then(function(result) {
                if (result.success) {
                    window.location.href = '{{ url('checkout/confirmacion') }}/' + result.pedido_id;
                } else {
                    document.getElementById('paypal-error').textContent = result.error || 'Error al procesar pago.';
                    document.getElementById('paypal-error').classList.remove('d-none');
                }
            });
        },
        onError: function(err) {
            document.getElementById('paypal-error').textContent = 'Error en PayPal: ' + err;
            document.getElementById('paypal-error').classList.remove('d-none');
        }
    }).render('#paypal-button-container');
</script>
@endpush
