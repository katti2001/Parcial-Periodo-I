@extends('almacen.layout')
@section('title', 'Nueva Compra')
@section('header', 'Registrar Compra / Entrada')

@section('content')
{{-- enctype requerido para subida de imágenes --}}
<form method="POST" action="{{ route('almacen.compras.store') }}"
      id="formCompra" enctype="multipart/form-data">
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
                        <th style="min-width:260px">Producto</th>
                        <th style="min-width:110px">Talla</th>
                        <th style="min-width:90px">Cantidad</th>
                        <th style="min-width:110px">Costo unit.</th>
                        <th style="min-width:110px">Precio venta</th>
                        <th style="min-width:100px">Subtotal</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="filas">
                    {{-- filas generadas por JS --}}
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

{{-- ══════════════════════════════════════════════════════════
     ASISTENTE IA — Botón flotante + Modal chat
══════════════════════════════════════════════════════════ --}}

{{-- Botón flotante --}}
<button id="btnAsistente" type="button"
        style="position:fixed;bottom:28px;right:28px;z-index:1055;
               width:56px;height:56px;border-radius:50%;
               background:linear-gradient(135deg,#4f46e5,#7c3aed);
               border:none;box-shadow:0 4px 18px rgba(79,70,229,.45);
               display:flex;align-items:center;justify-content:center;
               cursor:pointer;transition:transform .15s;">
    <i class="bi bi-stars text-white" style="font-size:1.4rem;"></i>
</button>

{{-- Modal chat --}}
<div id="modalAsistente"
     style="display:none;position:fixed;bottom:96px;right:28px;z-index:1054;
            width:360px;max-height:540px;
            background:#fff;border-radius:16px;
            box-shadow:0 8px 32px rgba(0,0,0,.18);
            display:none;flex-direction:column;overflow:hidden;">

    {{-- Header --}}
    <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:14px 16px;
                display:flex;align-items:center;justify-content:space-between;">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-stars text-white" style="font-size:1.1rem;"></i>
            <span class="text-white fw-semibold" style="font-size:.95rem;">Asistente de compras</span>
        </div>
        <button id="btnCerrarAsistente" type="button"
                style="background:none;border:none;color:rgba(255,255,255,.7);
                       font-size:1.2rem;line-height:1;cursor:pointer;padding:0;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    {{-- Mensajes --}}
    <div id="chatMensajes"
         style="flex:1;overflow-y:auto;padding:14px;display:flex;
                flex-direction:column;gap:10px;max-height:360px;
                background:#f8f9fa;">
        {{-- Mensaje bienvenida --}}
        <div class="msg-bot" style="align-self:flex-start;max-width:90%;">
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;
                        padding:10px 13px;font-size:.85rem;color:#374151;
                        box-shadow:0 1px 3px rgba(0,0,0,.06);">
                ¡Hola! Dime qué producto necesitas comprar y te digo si hay stock. También puedo agregarlo al formulario.
            </div>
        </div>
    </div>

    {{-- Input --}}
    <div style="padding:10px 12px;border-top:1px solid #e9ecef;background:#fff;display:flex;gap:8px;">
        <input id="chatInput" type="text" placeholder="Ej: camiseta del Barcelona talla M..."
               style="flex:1;border:1px solid #d1d5db;border-radius:8px;
                      padding:8px 12px;font-size:.85rem;outline:none;"
               maxlength="300" autocomplete="off">
        <button id="btnEnviarChat" type="button"
                style="background:linear-gradient(135deg,#4f46e5,#7c3aed);
                       border:none;border-radius:8px;width:38px;height:38px;
                       display:flex;align-items:center;justify-content:center;
                       cursor:pointer;flex-shrink:0;">
            <i class="bi bi-send-fill text-white" style="font-size:.85rem;"></i>
        </button>
    </div>
</div>
@endsection

@push('styles')
<style>
.panel-imagenes .preview-carousel {
    scroll-behavior: smooth;
}
.panel-imagenes .img-slide {
    position: relative;
    flex: 0 0 80px;
    height: 80px;
}
.panel-imagenes .img-slide img {
    width: 80px; height: 80px;
    object-fit: cover;
    border-radius: .375rem;
    border: 2px solid transparent;
    display: block;
}
.panel-imagenes .img-slide.principal img {
    border-color: #0d6efd;
}
.panel-imagenes .img-slide .badge-principal {
    position: absolute; bottom: 2px; left: 2px;
    font-size: .6rem; padding: 1px 4px;
    background: #0d6efd; color: #fff;
    border-radius: 3px; line-height: 1.4;
}
.panel-imagenes .img-slide .btn-rm {
    position: absolute; top: 2px; right: 2px;
    width: 18px; height: 18px; font-size: .65rem;
    padding: 0; line-height: 18px; text-align: center;
    border-radius: 50%; background: rgba(220,53,69,.85); color: #fff; border: none; cursor: pointer;
}
.mismo-badge {
    font-size: .7rem; background: #e9ecef; border-radius: .25rem;
    padding: 2px 6px; color: #495057;
}
</style>
@endpush

@push('scripts')
<script>
// ── Datos desde PHP ────────────────────────────────────────────────────────────
const productosExistentes   = @json($productos->map(fn($p) => ['id' => $p->id_producto, 'nombre' => $p->nombre, 'precio' => $p->precio_venta_base]));
const tallasDisponibles     = @json($tallas->map(fn($t) => ['id' => $t->id_talla, 'nombre' => $t->nombre]));
const categoriasDisponibles = @json($categorias->map(fn($c) => ['id' => $c->id_categoria, 'nombre' => $c->nombre]));
const equiposDisponibles    = @json($equipos->map(fn($e) => ['id' => $e->id_equipo, 'nombre' => $e->nombre]));

let filaIndex = 0;

// ── Helpers ────────────────────────────────────────────────────────────────────
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
        if (fila.querySelector('.inp-es-nuevo').value === '1') {
            const margen  = getMargen();
            const pvInput = fila.querySelector('.inp-precio-venta');
            if (pvInput) pvInput.value = (costo * (1 + margen / 100)).toFixed(2);
        }
    });
    document.getElementById('totalGeneral').textContent = '$' + total.toFixed(2);
}

// ── Constructores de <option> ──────────────────────────────────────────────────
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

// ── Template de fila ───────────────────────────────────────────────────────────
function buildFila(idx, esPrimera) {
    const margen = getMargen();
    // El toggle "mismo producto" solo aparece si NO es la primera fila
    const mismoBtn = esPrimera ? '' : `
        <div class="mt-2">
            <button type="button" class="btn btn-xs btn-outline-secondary btn-mismo-producto"
                    style="font-size:.75rem;padding:2px 8px;" title="Usar los mismos datos del producto de la fila anterior">
                <i class="bi bi-arrow-up me-1"></i>Mismo producto anterior
            </button>
        </div>`;

    return `
<tr class="fila-item" data-idx="${idx}">
  <td>
    <input type="hidden" name="items[${idx}][es_nuevo]" class="inp-es-nuevo" value="0">
    <input type="hidden" name="items[${idx}][mismo_producto]" class="inp-mismo-producto" value="0">
    <div class="btn-group btn-group-sm w-100" role="group">
      <button type="button" class="btn btn-outline-secondary btn-tipo active" data-tipo="existente">Existente</button>
      <button type="button" class="btn btn-outline-primary btn-tipo" data-tipo="nuevo">Nuevo</button>
    </div>
    ${mismoBtn}
  </td>

  <!-- Panel: producto existente -->
  <td>
    <div class="panel-existente">
      <select name="items[${idx}][id_producto]" class="form-select form-select-sm sel-producto">
        <option value="">Selecciona...</option>${buildOptsProducto()}
      </select>
    </div>

    <!-- Panel: producto nuevo -->
    <div class="panel-nuevo d-none">

      <!-- Bloque campos nuevo producto — se oculta cuando es "mismo" -->
      <div class="campos-nuevo-producto">
        <input type="text"   name="items[${idx}][sku_base]"    class="form-control form-control-sm mb-1 inp-sku"    placeholder="SKU *" maxlength="20">
        <input type="text"   name="items[${idx}][nombre]"      class="form-control form-control-sm mb-1 inp-nombre" placeholder="Nombre *" maxlength="100">
        <input type="text"   name="items[${idx}][descripcion]" class="form-control form-control-sm mb-1 inp-desc"   placeholder="Descripción (opcional)">
        <select name="items[${idx}][id_categoria]" class="form-select form-select-sm mb-1 inp-cat">${buildOptsCat()}</select>
        <select name="items[${idx}][id_equipo]"    class="form-select form-select-sm mb-1 inp-equipo">${buildOptsEquipo()}</select>

        <!-- Sección imágenes — solo visible en fila "líder" (no mismo-producto) -->
        <div class="panel-imagenes mt-2 border rounded p-2 bg-light">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <small class="fw-semibold text-secondary"><i class="bi bi-images me-1"></i>Imágenes del producto</small>
            <label class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:2px 8px;cursor:pointer;">
              <i class="bi bi-upload me-1"></i>Agregar
              <input type="file" name="imagenes[${idx}][]" class="inp-imagenes d-none"
                     accept="image/*" multiple>
            </label>
          </div>

          {{-- Carrusel de preview --}}
          <div class="preview-carousel-wrap d-none" style="position:relative;">
            <div class="preview-carousel d-flex gap-2 overflow-hidden" style="position:relative;height:90px;">
              {{-- slides generados por JS --}}
            </div>
            <button type="button" class="btn-prev-preview btn btn-sm btn-light border"
                    style="position:absolute;top:50%;left:-10px;transform:translateY(-50%);padding:1px 6px;font-size:.75rem;display:none;">
              <i class="bi bi-chevron-left"></i>
            </button>
            <button type="button" class="btn-next-preview btn btn-sm btn-light border"
                    style="position:absolute;top:50%;right:-10px;transform:translateY(-50%);padding:1px 6px;font-size:.75rem;display:none;">
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>

          <div class="preview-empty text-center text-muted py-2" style="font-size:.75rem;">
            <i class="bi bi-image me-1"></i>Sin imágenes — máx. 5
          </div>
          <div class="preview-counter d-none text-center text-muted mt-1" style="font-size:.7rem;"></div>
        </div>
      </div>

      <!-- Panel resumen "mismo producto" — visible cuando mismo=1, muestra datos del líder de solo lectura -->
      <div class="aviso-mismo d-none">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="mismo-badge"><i class="bi bi-arrow-up me-1"></i>Mismo producto que la fila anterior</span>
        </div>
        <div class="bg-white border rounded p-2" style="font-size:.82rem;">
          <div class="mb-1"><span class="text-muted me-1">SKU:</span><strong class="ref-sku">—</strong></div>
          <div class="mb-1"><span class="text-muted me-1">Nombre:</span><strong class="ref-nombre">—</strong></div>
          <div class="mb-1"><span class="text-muted me-1">Descripción:</span><span class="ref-desc text-secondary">—</span></div>
          <div class="mb-1"><span class="text-muted me-1">Categoría:</span><span class="ref-cat text-secondary">—</span></div>
          <div><span class="text-muted me-1">Equipo:</span><span class="ref-equipo text-secondary">—</span></div>
        </div>
      </div>
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
           value="0.00" step="0.01" readonly
           title="Precio de venta calculado con margen">
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

// ── Encontrar la fila "líder" de un grupo mismo-producto ──────────────────────
// Recorre hacia arriba desde la fila dada y devuelve la primera fila
// del grupo que NO tiene mismo_producto=1 y que es tipo "nuevo".
function encontrarLider(fila) {
    let lider = fila;
    let prev  = fila.previousElementSibling;
    while (prev && prev.classList.contains('fila-item')) {
        const esNuevoPrev  = prev.querySelector('.inp-es-nuevo').value === '1';
        const mismoPrev    = prev.querySelector('.inp-mismo-producto').value === '1';
        if (!esNuevoPrev) break;          // fila existente → rompe el grupo
        lider = prev;
        if (!mismoPrev) break;            // llegamos al líder real
        prev = prev.previousElementSibling;
    }
    return lider;
}

// ── Helpers para leer texto visible de un <select> dado su value ─────────────
function textoDeSelect(selectEl, valor) {
    const opt = Array.from(selectEl.options).find(o => o.value === String(valor));
    return opt ? opt.textContent.trim() : '—';
}

// ── Activar modo "mismo producto" en una fila ─────────────────────────────────
function activarMismo(fila) {
    fila.querySelector('.inp-mismo-producto').value = '1';
    fila.querySelector('.campos-nuevo-producto').classList.add('d-none');
    fila.querySelector('.aviso-mismo').classList.remove('d-none');

    // Ocultar input de imágenes (solo existe en el líder)
    const panelImg = fila.querySelector('.panel-imagenes');
    if (panelImg) panelImg.classList.add('d-none');

    // ── Copiar datos del líder a los hidden inputs de esta fila ──────────────
    const lider = encontrarLider(fila);
    if (lider && lider !== fila) {
        // Mapa: nombre del campo en el name="" → clase CSS del input en la fila líder
        const campoClase = {
            'sku_base':    'inp-sku',
            'nombre':      'inp-nombre',
            'descripcion': 'inp-desc',
            'id_categoria':'inp-cat',
            'id_equipo':   'inp-equipo',
        };
        Object.entries(campoClase).forEach(([campo, clase]) => {
            const origen  = lider.querySelector('.' + clase);
            const destino = fila.querySelector(`[name*="[${campo}]"]`);
            if (origen && destino) destino.value = origen.value;
        });

        // ── Poblar el panel de resumen visual ────────────────────────────────
        const aviso = fila.querySelector('.aviso-mismo');

        aviso.querySelector('.ref-sku').textContent    = lider.querySelector('.inp-sku')?.value.trim()  || '—';
        aviso.querySelector('.ref-nombre').textContent = lider.querySelector('.inp-nombre')?.value.trim() || '—';
        aviso.querySelector('.ref-desc').textContent   = lider.querySelector('.inp-desc')?.value.trim()  || '(sin descripción)';

        const selCat    = lider.querySelector('.inp-cat');
        const selEquipo = lider.querySelector('.inp-equipo');
        aviso.querySelector('.ref-cat').textContent    = selCat    ? textoDeSelect(selCat,    selCat.value)    : '—';
        aviso.querySelector('.ref-equipo').textContent = selEquipo ? textoDeSelect(selEquipo, selEquipo.value) : '—';

        // ── También copiar costo unitario del líder como punto de partida ────
        const costoLider = lider.querySelector('.inp-costo')?.value;
        if (costoLider !== undefined) {
            fila.querySelector('.inp-costo').value = costoLider;
            recalcularTotales();
        }
    }

    actualizarBtnMismo(fila);
}

// ── Desactivar modo "mismo producto" en una fila ──────────────────────────────
function desactivarMismo(fila) {
    fila.querySelector('.inp-mismo-producto').value = '0';
    fila.querySelector('.campos-nuevo-producto').classList.remove('d-none');
    fila.querySelector('.aviso-mismo').classList.add('d-none');

    const panelImg = fila.querySelector('.panel-imagenes');
    if (panelImg) panelImg.classList.remove('d-none');

    // Limpiar resumen visual
    const aviso = fila.querySelector('.aviso-mismo');
    if (aviso) {
        ['ref-sku','ref-nombre','ref-desc','ref-cat','ref-equipo'].forEach(cls => {
            const el = aviso.querySelector('.' + cls);
            if (el) el.textContent = '—';
        });
    }

    // Limpiar los campos del producto nuevo de esta fila para que el usuario los rellene
    ['inp-sku','inp-nombre','inp-desc'].forEach(cls => {
        const el = fila.querySelector('.' + cls);
        if (el) el.value = '';
    });
    const selCat    = fila.querySelector('.inp-cat');
    const selEquipo = fila.querySelector('.inp-equipo');
    if (selCat)    selCat.value    = '';
    if (selEquipo) selEquipo.value = '';

    actualizarBtnMismo(fila);
}

// ── Actualizar texto/estado del botón mismo-producto ─────────────────────────
function actualizarBtnMismo(fila) {
    const btn = fila.querySelector('.btn-mismo-producto');
    if (!btn) return;
    const activo = fila.querySelector('.inp-mismo-producto').value === '1';
    if (activo) {
        btn.classList.replace('btn-outline-secondary', 'btn-secondary');
        btn.innerHTML = '<i class="bi bi-x me-1"></i>Producto separado';
        btn.title = 'Crear como producto distinto al anterior';
    } else {
        btn.classList.replace('btn-secondary', 'btn-outline-secondary');
        btn.innerHTML = '<i class="bi bi-arrow-up me-1"></i>Mismo producto anterior';
        btn.title = 'Usar los mismos datos del producto de la fila anterior';
    }
}

// ── Preview de imágenes — carrusel deslizable ─────────────────────────────────
function bindImagenPreview(fila) {
    const inputFile = fila.querySelector('.inp-imagenes');
    if (!inputFile) return;

    const MAX_IMG     = 5;
    const wrap        = fila.querySelector('.preview-carousel-wrap');
    const carousel    = fila.querySelector('.preview-carousel');
    const emptyMsg    = fila.querySelector('.preview-empty');
    const counter     = fila.querySelector('.preview-counter');
    const btnPrev     = fila.querySelector('.btn-prev-preview');
    const btnNext     = fila.querySelector('.btn-next-preview');

    // Almacén local de { file, dataUrl }
    let slides = [];

    // Sincroniza el array slides con el FileList real del input via DataTransfer
    function syncInput() {
        const dt = new DataTransfer();
        slides.forEach(s => dt.items.add(s.file));
        inputFile.files = dt.files;
    }

    function renderSlides() {
        carousel.innerHTML = '';
        slides.forEach((s, i) => {
            const slide = document.createElement('div');
            slide.className = 'img-slide' + (i === 0 ? ' principal' : '');
            slide.innerHTML = `
                <img src="${s.dataUrl}" alt="Imagen ${i+1}">
                ${i === 0 ? '<span class="badge-principal">Principal</span>' : ''}
                <button type="button" class="btn-rm" title="Quitar" data-i="${i}">×</button>`;
            slide.querySelector('.btn-rm').addEventListener('click', () => {
                slides.splice(i, 1);
                syncInput();
                renderSlides();
            });
            carousel.appendChild(slide);
        });

        const empty = slides.length === 0;
        wrap.classList.toggle('d-none', empty);
        emptyMsg.classList.toggle('d-none', !empty);
        counter.classList.toggle('d-none', empty);
        if (!empty) {
            counter.textContent = `${slides.length} imagen${slides.length > 1 ? 'es' : ''} — la primera será la principal`;
        }

        // Mostrar controles solo si hay más de 2 slides (4 visibles a la vez en 80px c/u)
        const mostrarControles = slides.length > 3;
        btnPrev.style.display = mostrarControles ? '' : 'none';
        btnNext.style.display = mostrarControles ? '' : 'none';
    }

    // Scroll del carrusel
    const SCROLL_STEP = 90;
    btnPrev.addEventListener('click', () => carousel.scrollBy({ left: -SCROLL_STEP, behavior: 'smooth' }));
    btnNext.addEventListener('click', () => carousel.scrollBy({ left:  SCROLL_STEP, behavior: 'smooth' }));

    inputFile.addEventListener('change', function () {
        const remaining = MAX_IMG - slides.length;
        if (remaining <= 0) { this.value = ''; return; }

        const toLoad = Array.from(this.files).slice(0, remaining);
        let loaded = 0;

        toLoad.forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                slides.push({ file, dataUrl: e.target.result });
                loaded++;
                if (loaded === toLoad.length) {
                    syncInput();
                    renderSlides();
                }
            };
            reader.readAsDataURL(file);
        });

        this.value = ''; // reset para permitir volver a seleccionar el mismo archivo
    });

    renderSlides(); // estado inicial
}

// ── Bind de todos los eventos de una fila ────────────────────────────────────
function bindFila(fila) {

    // Toggle Existente / Nuevo
    fila.querySelectorAll('.btn-tipo').forEach(btn => {
        btn.addEventListener('click', () => {
            const tipo = btn.dataset.tipo;
            fila.querySelectorAll('.btn-tipo').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const esNuevo = tipo === 'nuevo' ? '1' : '0';
            fila.querySelector('.inp-es-nuevo').value = esNuevo;

            fila.querySelector('.panel-existente').classList.toggle('d-none', tipo === 'nuevo');
            fila.querySelector('.panel-nuevo').classList.toggle('d-none', tipo === 'existente');

            // Si vuelve a Existente, resetear estado mismo-producto
            if (tipo === 'existente') {
                desactivarMismo(fila);
                fila.querySelector('.inp-mismo-producto').value = '0';
            }

            recalcularTotales();
        });
    });

    // Botón "mismo producto anterior"
    const btnMismo = fila.querySelector('.btn-mismo-producto');
    if (btnMismo) {
        btnMismo.addEventListener('click', () => {
            const mismoActual = fila.querySelector('.inp-mismo-producto').value === '1';
            // Solo funciona si la fila anterior es tipo nuevo
            const prev = fila.previousElementSibling;
            if (!prev || !prev.classList.contains('fila-item')) return;
            const prevEsNuevo = prev.querySelector('.inp-es-nuevo').value === '1';
            if (!prevEsNuevo) {
                alert('La fila anterior no es un producto nuevo. Solo puedes usar esta opción si la fila anterior es un producto nuevo.');
                return;
            }
            if (mismoActual) {
                desactivarMismo(fila);
            } else {
                // Asegurarse de que la fila actual es tipo "nuevo"
                if (fila.querySelector('.inp-es-nuevo').value !== '1') {
                    // Activar "nuevo" primero
                    fila.querySelector('[data-tipo="nuevo"]').click();
                }
                activarMismo(fila);
            }
        });
    }

    // Al cambiar producto existente → actualizar precio de referencia
    fila.querySelector('.sel-producto').addEventListener('change', function () {
        const opt   = this.options[this.selectedIndex];
        const precio = parseFloat(opt.dataset.precio) || 0;
        fila.querySelector('.inp-precio-venta').value = precio.toFixed(2);
        fila.querySelector('.inp-precio-venta').title  = 'Precio de venta actual del producto';
        recalcularTotales();
    });

    fila.querySelector('.inp-cantidad').addEventListener('input', recalcularTotales);
    fila.querySelector('.inp-costo').addEventListener('input', recalcularTotales);

    // Eliminar fila
    fila.querySelector('.btn-eliminar-fila').addEventListener('click', () => {
        if (document.querySelectorAll('.fila-item').length > 1) {
            // Si la fila siguiente es "mismo producto" de ésta, liberarla
            const siguiente = fila.nextElementSibling;
            if (siguiente && siguiente.classList.contains('fila-item')) {
                if (siguiente.querySelector('.inp-mismo-producto').value === '1') {
                    desactivarMismo(siguiente);
                }
            }
            fila.remove();
            recalcularTotales();
        }
    });

    // Preview de imágenes
    bindImagenPreview(fila);
}

// ── Agregar fila ───────────────────────────────────────────────────────────────
document.getElementById('btnAgregarFila').addEventListener('click', () => {
    const tbody   = document.getElementById('filas');
    const esPrimera = tbody.querySelectorAll('.fila-item').length === 0;
    tbody.insertAdjacentHTML('beforeend', buildFila(filaIndex++, esPrimera));
    bindFila(tbody.querySelector('tr:last-child'));
    recalcularTotales();
});

// ── Margen global cambia → recalcular precios de filas nuevas ─────────────────
document.getElementById('inputMargen').addEventListener('input', () => {
    const margen = getMargen();
    document.querySelectorAll('.fila-item').forEach(fila => {
        if (fila.querySelector('.inp-es-nuevo').value === '1') {
            const costo   = parseFloat(fila.querySelector('.inp-costo').value) || 0;
            const pvInput = fila.querySelector('.inp-precio-venta');
            pvInput.value = (costo * (1 + margen / 100)).toFixed(2);
            const smallEl = pvInput.nextElementSibling;
            if (smallEl && smallEl.tagName === 'SMALL') smallEl.textContent = `costo × ${margen}% margen`;
        }
    });
    recalcularTotales();
});

// ── Inicializar con una fila vacía ────────────────────────────────────────────
(function init() {
    const tbody = document.getElementById('filas');
    tbody.insertAdjacentHTML('beforeend', buildFila(filaIndex++, true));
    bindFila(tbody.querySelector('tr:last-child'));
    recalcularTotales();
})();

// ══════════════════════════════════════════════════════════════════════════════
// ASISTENTE IA — lógica del chat
// ══════════════════════════════════════════════════════════════════════════════
(function initAsistente() {
    const btnAbrir   = document.getElementById('btnAsistente');
    const btnCerrar  = document.getElementById('btnCerrarAsistente');
    const modal      = document.getElementById('modalAsistente');
    const mensajes   = document.getElementById('chatMensajes');
    const input      = document.getElementById('chatInput');
    const btnEnviar  = document.getElementById('btnEnviarChat');
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;
    const urlChat    = "{{ route('almacen.asistente.chat') }}";

    // Abrir / cerrar modal
    btnAbrir.addEventListener('click', () => {
        const visible = modal.style.display === 'flex';
        modal.style.display = visible ? 'none' : 'flex';
        modal.style.flexDirection = 'column';
        if (!visible) input.focus();
    });
    btnCerrar.addEventListener('click', () => { modal.style.display = 'none'; });

    // Hover en botón flotante
    btnAbrir.addEventListener('mouseenter', () => btnAbrir.style.transform = 'scale(1.1)');
    btnAbrir.addEventListener('mouseleave', () => btnAbrir.style.transform = 'scale(1)');

    // Enter para enviar
    input.addEventListener('keydown', e => { if (e.key === 'Enter') enviar(); });
    btnEnviar.addEventListener('click', enviar);

    // ── Renderizar mensaje ────────────────────────────────────────────────────
    function agregarMensaje(texto, tipo, accion) {
        const esBot = tipo === 'bot';
        const wrap  = document.createElement('div');
        wrap.className = esBot ? 'msg-bot' : 'msg-user';
        wrap.style.cssText = esBot
            ? 'align-self:flex-start;max-width:92%;'
            : 'align-self:flex-end;max-width:92%;';

        const burbuja = document.createElement('div');
        burbuja.style.cssText = esBot
            ? 'background:#fff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;padding:10px 13px;font-size:.85rem;color:#374151;box-shadow:0 1px 3px rgba(0,0,0,.06);'
            : 'background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:12px 12px 2px 12px;padding:10px 13px;font-size:.85rem;color:#fff;';

        // Convertir saltos de línea en <br>
        burbuja.innerHTML = texto.replace(/\n/g, '<br>');
        wrap.appendChild(burbuja);

        // Botón "Agregar al formulario" si hay acción
        if (accion && accion.accion === 'agregar') {
            const btnAgregar = document.createElement('button');
            btnAgregar.type = 'button';
            btnAgregar.innerHTML = '<i class="bi bi-plus-circle me-1"></i>Agregar al formulario';
            btnAgregar.style.cssText = 'margin-top:6px;font-size:.78rem;padding:4px 10px;border-radius:6px;border:1px solid #4f46e5;color:#4f46e5;background:#fff;cursor:pointer;display:block;';
            btnAgregar.addEventListener('click', () => agregarProductoAlFormulario(accion));
            wrap.appendChild(btnAgregar);

            // Sugerencias similares
            if (accion.similares && accion.similares.length > 0) {
                const simWrap = document.createElement('div');
                simWrap.style.cssText = 'margin-top:6px;display:flex;flex-wrap:wrap;gap:5px;';
                accion.similares.forEach(s => {
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    chip.innerHTML = `<i class="bi bi-arrow-right-circle me-1"></i>${s.nombre} <span style="opacity:.7">(stock: ${s.stock})</span>`;
                    chip.style.cssText = 'font-size:.73rem;padding:3px 8px;border-radius:20px;border:1px solid #d1d5db;background:#f3f4f6;color:#374151;cursor:pointer;';
                    chip.title = 'Agregar este producto similar';
                    chip.addEventListener('click', () => agregarProductoAlFormulario(s));
                    simWrap.appendChild(chip);
                });
                const label = document.createElement('div');
                label.textContent = 'Similares:';
                label.style.cssText = 'font-size:.72rem;color:#6b7280;margin-top:6px;width:100%;';
                wrap.appendChild(label);
                wrap.appendChild(simWrap);
            }
        }

        mensajes.appendChild(wrap);
        mensajes.scrollTop = mensajes.scrollHeight;
    }

    // ── Indicador de escritura ────────────────────────────────────────────────
    function mostrarTyping() {
        const el = document.createElement('div');
        el.id = 'typing-indicator';
        el.style.cssText = 'align-self:flex-start;';
        el.innerHTML = `<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px 12px 12px 2px;
            padding:8px 14px;font-size:.8rem;color:#9ca3af;box-shadow:0 1px 3px rgba(0,0,0,.06);">
            <span class="typing-dot">●</span> <span class="typing-dot">●</span> <span class="typing-dot">●</span>
        </div>`;
        mensajes.appendChild(el);
        mensajes.scrollTop = mensajes.scrollHeight;
    }
    function ocultarTyping() {
        const el = document.getElementById('typing-indicator');
        if (el) el.remove();
    }

    // ── Enviar mensaje al backend ─────────────────────────────────────────────
    async function enviar() {
        const texto = input.value.trim();
        if (!texto) return;

        agregarMensaje(texto, 'user');
        input.value = '';
        btnEnviar.disabled = true;
        mostrarTyping();

        try {
            const res = await fetch(urlChat, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ mensaje: texto }),
            });

            ocultarTyping();
            const data = await res.json();

            if (data.error) {
                agregarMensaje('⚠️ ' + data.mensaje, 'bot');
            } else {
                agregarMensaje(data.mensaje, 'bot', data.accion);
            }
        } catch (e) {
            ocultarTyping();
            agregarMensaje('Error de red. Verifica tu conexión.', 'bot');
        } finally {
            btnEnviar.disabled = false;
            input.focus();
        }
    }

    // ── Agregar producto existente al formulario de compra ────────────────────
    function agregarProductoAlFormulario(accion) {
        const tbody    = document.getElementById('filas');
        const esPrimera = tbody.querySelectorAll('.fila-item').length === 0;

        tbody.insertAdjacentHTML('beforeend', buildFila(filaIndex++, esPrimera));
        const nuevaFila = tbody.querySelector('tr:last-child');
        bindFila(nuevaFila);

        // Seleccionar el producto en el <select> de la fila
        const idProducto = accion.id_producto ?? accion.id;
        const selProducto = nuevaFila.querySelector('.sel-producto');
        if (selProducto && idProducto) {
            selProducto.value = String(idProducto);
            // Disparar evento change para actualizar precio
            selProducto.dispatchEvent(new Event('change'));
        }

        recalcularTotales();

        // Scroll suave hasta la nueva fila
        nuevaFila.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Confirmar en el chat
        const nombre = accion.nombre ?? 'Producto';
        agregarMensaje(`✅ "${nombre}" agregado al formulario. Selecciona la talla y cantidad.`, 'bot');
    }
})();
</script>
@endpush
