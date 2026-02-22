@extends('almacen.layout')
@section('title', 'Nueva Compra')
@section('header', 'Registrar Compra / Entrada')

@section('content')
<form method="POST" action="{{ route('almacen.compras.store') }}" id="formCompra">
    @csrf

    {{-- Cabecera --}}
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
                           class="form-control"
                           value="{{ old('numero_factura_proveedor') }}" maxlength="50">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado <span class="text-danger">*</span></label>
                    <select name="estado" class="form-select" required>
                        <option value="solicitado" {{ old('estado','solicitado') === 'solicitado' ? 'selected' : '' }}>Solicitado</option>
                        <option value="recibido"   {{ old('estado') === 'recibido'   ? 'selected' : '' }}>Recibido</option>
                        <option value="cancelado"  {{ old('estado') === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
            </div>

            {{-- Margen global --}}
            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Margen de ganancia (%)
                        <span class="text-muted fw-normal small">— aplica al precio de venta de productos nuevos</span>
                    </label>
                    <div class="input-group" style="max-width:200px">
                        <input type="number" id="inputMargen" name="margen"
                               class="form-control" min="0" max="500" step="0.5"
                               value="{{ old('margen', 25) }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Precio venta = costo × (1 + margen / 100)</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de productos --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold border-0 pt-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2"></i>Productos</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarFila">
                <i class="bi bi-plus me-1"></i>Agregar fila
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table mb-0 align-middle" id="tablaItems">
                <thead class="table-light">
                    <tr>
                        <th style="width:130px">Tipo</th>
                        <th style="min-width:220px">Producto</th>
                        <th style="min-width:110px">Talla</th>
                        <th style="min-width:90px">Cantidad</th>
                        <th style="min-width:110px">Costo unit.</th>
                        <th style="min-width:110px">Precio venta</th>
                        <th style="min-width:100px">Subtotal</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="filas">
                    {{-- fila inicial generada por JS al cargar --}}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total compra:</td>
                        <td class="text-end fw-bold" id="totalGeneral">$0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            </div>
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
// Datos pasados desde PHP
const productosExistentes = @json($productos->map(fn($p) => ['id' => $p->id_producto, 'nombre' => $p->nombre, 'precio' => $p->precio_venta_base]));
const tallasDisponibles   = @json($tallas->map(fn($t) => ['id' => $t->id_talla, 'nombre' => $t->nombre]));
const categoriasDisponibles = @json($categorias->map(fn($c) => ['id' => $c->id_categoria, 'nombre' => $c->nombre]));
const equiposDisponibles  = @json($equipos->map(fn($e) => ['id' => $e->id_equipo, 'nombre' => $e->nombre]));

let filaIndex = 0;

// ─── Helpers ──────────────────────────────────────────────────────────────────
function getMargen() {
    return parseFloat(document.getElementById('inputMargen').value) || 25;
}

function recalcularTotales() {
    let total = 0;
    document.querySelectorAll('.fila-item').forEach(fila => {
        const cant  = parseFloat(fila.querySelector('.inp-cantidad').value) || 0;
        const costo = parseFloat(fila.querySelector('.inp-costo').value) || 0;
        const sub   = cant * costo;
        fila.querySelector('.td-subtotal').textContent = '$' + sub.toFixed(2);
        total += sub;

        // Recalcular precio venta si es producto nuevo
        const esNuevo = fila.querySelector('.inp-es-nuevo');
        if (esNuevo && esNuevo.value === '1') {
            const margen = getMargen();
            const pvInput = fila.querySelector('.inp-precio-venta');
            if (pvInput) {
                pvInput.value = (costo * (1 + margen / 100)).toFixed(2);
            }
        }
    });
    document.getElementById('totalGeneral').textContent = '$' + total.toFixed(2);
}

// ─── Build options ─────────────────────────────────────────────────────────────
function buildOptsProducto() {
    return productosExistentes.map(p =>
        `<option value="${p.id}" data-precio="${p.precio}">${p.nombre}</option>`
    ).join('');
}
function buildOptsTalla() {
    return tallasDisponibles.map(t => `<option value="${t.id}">${t.nombre}</option>`).join('');
}
function buildOptsCat() {
    return '<option value="">— Sin categoría —</option>' +
        categoriasDisponibles.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
}
function buildOptsEquipo() {
    return '<option value="">— Sin equipo —</option>' +
        equiposDisponibles.map(e => `<option value="${e.id}">${e.nombre}</option>`).join('');
}

// ─── Template de fila ─────────────────────────────────────────────────────────
function buildFila(idx) {
    const margen = getMargen();
    return `
<tr class="fila-item" data-idx="${idx}">
  <td>
    <input type="hidden" name="items[${idx}][es_nuevo]" class="inp-es-nuevo" value="0">
    <div class="btn-group btn-group-sm w-100" role="group">
      <button type="button" class="btn btn-outline-secondary btn-tipo active" data-tipo="existente">Existente</button>
      <button type="button" class="btn btn-outline-primary btn-tipo" data-tipo="nuevo">Nuevo</button>
    </div>
  </td>

  {{-- Panel: producto existente --}}
  <td>
    <div class="panel-existente">
      <select name="items[${idx}][id_producto]" class="form-select form-select-sm sel-producto">
        <option value="">Selecciona...</option>${buildOptsProducto()}
      </select>
    </div>
    {{-- Panel: producto nuevo --}}
    <div class="panel-nuevo d-none">
      <input type="text"   name="items[${idx}][sku_base]"     class="form-control form-control-sm mb-1" placeholder="SKU *" maxlength="20">
      <input type="text"   name="items[${idx}][nombre]"       class="form-control form-control-sm mb-1" placeholder="Nombre *" maxlength="100">
      <input type="text"   name="items[${idx}][descripcion]"  class="form-control form-control-sm mb-1" placeholder="Descripción (opcional)">
      <select name="items[${idx}][id_categoria]" class="form-select form-select-sm mb-1">${buildOptsCat()}</select>
      <select name="items[${idx}][id_equipo]"    class="form-select form-select-sm">${buildOptsEquipo()}</select>
    </div>
  </td>

  <td>
    <select name="items[${idx}][id_talla]" class="form-select form-select-sm" required>
      <option value="">Talla...</option>${buildOptsTalla()}
    </select>
  </td>
  <td>
    <input type="number" name="items[${idx}][cantidad_comprada]"
           class="form-control form-control-sm inp-cantidad" min="1" value="1" required>
  </td>
  <td>
    <input type="number" name="items[${idx}][costo_unitario]"
           class="form-control form-control-sm inp-costo" min="0" step="0.01" value="0.00" required>
  </td>
  <td>
    <input type="number" class="form-control form-control-sm inp-precio-venta bg-light"
           value="${(0 * (1 + margen / 100)).toFixed(2)}" step="0.01" readonly
           title="Precio de venta calculado con margen del ${margen}%">
    <small class="text-muted d-block" style="font-size:.7rem">costo × ${margen}% margen</small>
  </td>
  <td class="td-subtotal text-end small fw-semibold">$0.00</td>
  <td>
    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-fila">
      <i class="bi bi-x"></i>
    </button>
  </td>
</tr>`;
}

// ─── Bind eventos de una fila ──────────────────────────────────────────────────
function bindFila(fila) {
    // Toggle existente / nuevo
    fila.querySelectorAll('.btn-tipo').forEach(btn => {
        btn.addEventListener('click', () => {
            const tipo = btn.dataset.tipo;
            fila.querySelectorAll('.btn-tipo').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const esNuevo = tipo === 'nuevo' ? '1' : '0';
            fila.querySelector('.inp-es-nuevo').value = esNuevo;

            fila.querySelector('.panel-existente').classList.toggle('d-none', tipo === 'nuevo');
            fila.querySelector('.panel-nuevo').classList.toggle('d-none', tipo === 'existente');

            recalcularTotales();
        });
    });

    // Al cambiar producto existente, actualizar precio venta como referencia
    fila.querySelector('.sel-producto').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const precio = parseFloat(opt.dataset.precio) || 0;
        // Solo muestra el precio actual del producto (no recalcula con margen)
        fila.querySelector('.inp-precio-venta').value = precio.toFixed(2);
        fila.querySelector('.inp-precio-venta').title = 'Precio de venta actual del producto';
        recalcularTotales();
    });

    fila.querySelector('.inp-cantidad').addEventListener('input', recalcularTotales);
    fila.querySelector('.inp-costo').addEventListener('input', recalcularTotales);

    fila.querySelector('.btn-eliminar-fila').addEventListener('click', () => {
        if (document.querySelectorAll('.fila-item').length > 1) {
            fila.remove();
            recalcularTotales();
        }
    });
}

// ─── Agregar fila ──────────────────────────────────────────────────────────────
document.getElementById('btnAgregarFila').addEventListener('click', () => {
    const tbody = document.getElementById('filas');
    tbody.insertAdjacentHTML('beforeend', buildFila(filaIndex++));
    bindFila(tbody.querySelector('tr:last-child'));
    recalcularTotales();
});

// ─── Margen global cambia → recalcular precio venta de todas las filas nuevas ──
document.getElementById('inputMargen').addEventListener('input', () => {
    const margen = getMargen();
    document.querySelectorAll('.fila-item').forEach(fila => {
        if (fila.querySelector('.inp-es-nuevo').value === '1') {
            const costo = parseFloat(fila.querySelector('.inp-costo').value) || 0;
            fila.querySelector('.inp-precio-venta').value = (costo * (1 + margen / 100)).toFixed(2);
            const smallEl = fila.querySelector('.inp-precio-venta + small');
            if (smallEl) smallEl.textContent = `costo × ${margen}% margen`;
        }
    });
    recalcularTotales();
});

// ─── Inicializar con una fila vacía ───────────────────────────────────────────
(function init() {
    const tbody = document.getElementById('filas');
    tbody.insertAdjacentHTML('beforeend', buildFila(filaIndex++));
    bindFila(tbody.querySelector('tr:last-child'));
    recalcularTotales();
})();
</script>
@endpush
