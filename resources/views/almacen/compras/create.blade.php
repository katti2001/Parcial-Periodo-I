@extends('almacen.layout')
@section('title', 'Nueva Compra')
@section('header', 'Registrar Compra / Entrada')

@section('content')
<form method="POST" action="{{ route('almacen.compras.store') }}" id="formCompra">
    @csrf

    {{-- Cabecera de la compra --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold border-0 pt-3">
            <i class="bi bi-file-earmark-text me-2"></i>Datos generales
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                    <select name="id_proveedor" class="form-select @error('id_proveedor') is-invalid @enderror" required>
                        <option value="">Selecciona...</option>
                        @foreach($proveedores as $p)
                            <option value="{{ $p->id_proveedor }}"
                                {{ old('id_proveedor') == $p->id_proveedor ? 'selected' : '' }}>
                                {{ $p->nombre_empresa }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_proveedor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha de compra <span class="text-danger">*</span></label>
                    <input type="date" name="fecha_compra"
                           class="form-control @error('fecha_compra') is-invalid @enderror"
                           value="{{ old('fecha_compra', date('Y-m-d')) }}" required>
                    @error('fecha_compra')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">N° Factura proveedor</label>
                    <input type="text" name="numero_factura_proveedor"
                           class="form-control @error('numero_factura_proveedor') is-invalid @enderror"
                           value="{{ old('numero_factura_proveedor') }}" maxlength="50">
                    @error('numero_factura_proveedor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado <span class="text-danger">*</span></label>
                    <select name="estado" class="form-select @error('estado') is-invalid @enderror" required>
                        <option value="solicitado" {{ old('estado','solicitado') === 'solicitado' ? 'selected' : '' }}>Solicitado</option>
                        <option value="recibido"   {{ old('estado') === 'recibido'   ? 'selected' : '' }}>Recibido</option>
                        <option value="cancelado"  {{ old('estado') === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                    </select>
                    @error('estado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Ítems --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold border-0 pt-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2"></i>Productos</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarFila">
                <i class="bi bi-plus me-1"></i>Agregar fila
            </button>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0" id="tablaItems">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:200px">Producto</th>
                        <th style="min-width:120px">Talla</th>
                        <th style="min-width:100px">Cantidad</th>
                        <th style="min-width:120px">Costo unitario</th>
                        <th style="min-width:100px">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="filas">
                    {{-- fila inicial --}}
                    <tr class="fila-item">
                        <td>
                            <select name="items[0][id_producto]" class="form-select form-select-sm sel-producto" required>
                                <option value="">Selecciona...</option>
                                @foreach($productos as $p)
                                    <option value="{{ $p->id_producto }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="items[0][id_talla]" class="form-select form-select-sm" required>
                                <option value="">Talla...</option>
                                @foreach($tallas as $t)
                                    <option value="{{ $t->id_talla }}">{{ $t->nombre }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="items[0][cantidad_comprada]"
                                   class="form-control form-control-sm inp-cantidad"
                                   min="1" value="1" required>
                        </td>
                        <td>
                            <input type="number" name="items[0][costo_unitario]"
                                   class="form-control form-control-sm inp-costo"
                                   min="0" step="0.01" value="0.00" required>
                        </td>
                        <td class="td-subtotal text-end small fw-semibold">$0.00</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-fila">
                                <i class="bi bi-x"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Total:</td>
                        <td class="text-end fw-bold" id="totalGeneral">$0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>Registrar compra
        </button>
        <a href="{{ route('almacen.compras.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Datos de productos y tallas para clonar filas
const productos = @json($productos->map(fn($p) => ['id' => $p->id_producto, 'nombre' => $p->nombre]));
const tallas    = @json($tallas->map(fn($t) => ['id' => $t->id_talla, 'nombre' => $t->nombre]));

let filaIndex = 1;

function recalcularTotales() {
    let total = 0;
    document.querySelectorAll('.fila-item').forEach(fila => {
        const cant  = parseFloat(fila.querySelector('.inp-cantidad').value) || 0;
        const costo = parseFloat(fila.querySelector('.inp-costo').value) || 0;
        const sub   = cant * costo;
        fila.querySelector('.td-subtotal').textContent = '$' + sub.toFixed(2);
        total += sub;
    });
    document.getElementById('totalGeneral').textContent = '$' + total.toFixed(2);
}

function buildFila(idx) {
    const optsProducto = productos.map(p =>
        `<option value="${p.id}">${p.nombre}</option>`).join('');
    const optsTalla = tallas.map(t =>
        `<option value="${t.id}">${t.nombre}</option>`).join('');

    return `<tr class="fila-item">
        <td><select name="items[${idx}][id_producto]" class="form-select form-select-sm sel-producto" required>
            <option value="">Selecciona...</option>${optsProducto}</select></td>
        <td><select name="items[${idx}][id_talla]" class="form-select form-select-sm" required>
            <option value="">Talla...</option>${optsTalla}</select></td>
        <td><input type="number" name="items[${idx}][cantidad_comprada]"
                   class="form-control form-control-sm inp-cantidad" min="1" value="1" required></td>
        <td><input type="number" name="items[${idx}][costo_unitario]"
                   class="form-control form-control-sm inp-costo" min="0" step="0.01" value="0.00" required></td>
        <td class="td-subtotal text-end small fw-semibold">$0.00</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-fila">
            <i class="bi bi-x"></i></button></td>
    </tr>`;
}

document.getElementById('btnAgregarFila').addEventListener('click', () => {
    document.getElementById('filas').insertAdjacentHTML('beforeend', buildFila(filaIndex++));
    bindFilaEvents(document.querySelector('#filas tr:last-child'));
    recalcularTotales();
});

function bindFilaEvents(fila) {
    fila.querySelector('.inp-cantidad').addEventListener('input', recalcularTotales);
    fila.querySelector('.inp-costo').addEventListener('input', recalcularTotales);
    fila.querySelector('.btn-eliminar-fila').addEventListener('click', () => {
        if (document.querySelectorAll('.fila-item').length > 1) {
            fila.remove();
            recalcularTotales();
        }
    });
}

// Bind a la fila inicial
document.querySelectorAll('.fila-item').forEach(bindFilaEvents);
recalcularTotales();
</script>
@endpush
