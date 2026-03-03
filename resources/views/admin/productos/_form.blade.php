<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">SKU Base <span class="text-danger">*</span></label>
        <input type="text" name="sku_base" class="form-control @error('sku_base') is-invalid @enderror"
               value="{{ old('sku_base', $producto->sku_base ?? '') }}" required>
        @error('sku_base')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $producto->nombre ?? '') }}" required>
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Descripción</label>
        <textarea name="descripcion" rows="3" class="form-control">{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Precio base ($) <span class="text-danger">*</span></label>
        <input type="number" name="precio_venta_base" step="0.01" min="0"
               class="form-control @error('precio_venta_base') is-invalid @enderror"
               value="{{ old('precio_venta_base', $producto->precio_venta_base ?? '') }}" required>
        @error('precio_venta_base')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Categoría</label>
        <select name="id_categoria" class="form-select">
            <option value="">— Sin categoría —</option>
            @foreach($categorias as $cat)
                <option value="{{ $cat->id_categoria }}"
                    {{ old('id_categoria', $producto->id_categoria ?? '') == $cat->id_categoria ? 'selected' : '' }}>
                    {{ $cat->nombre }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Equipo</label>
        <select name="id_equipo" class="form-select">
            <option value="">— Sin equipo —</option>
            @foreach($equipos as $eq)
                <option value="{{ $eq->id_equipo }}"
                    {{ old('id_equipo', $producto->id_equipo ?? '') == $eq->id_equipo ? 'selected' : '' }}>
                    {{ $eq->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    @isset($producto)
        @if($producto->imagenes_productos->count())
        <div class="col-12">
            <label class="form-label fw-semibold">Imágenes actuales</label>
            <div class="d-flex flex-wrap gap-2">
                @foreach($producto->imagenes_productos as $img)
                <div class="border rounded p-1 text-center" style="width:110px">
                    <img src="{{ $img->url_imagen }}" alt="imagen"
                         class="img-fluid rounded mb-1" style="height:80px;object-fit:cover">
                    @if($img->es_principal)
                        <span class="badge bg-dark d-block mb-1">Principal</span>
                    @endif
                    <div class="form-check d-flex justify-content-center">
                        <input type="checkbox" name="eliminar_imagenes[]"
                               value="{{ $img->id_imagen }}"
                               class="form-check-input"
                               id="del_img_{{ $img->id_imagen }}">
                        <label class="form-check-label ms-1 small text-danger"
                               for="del_img_{{ $img->id_imagen }}">Eliminar</label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endisset

    <div class="col-12">
        <label class="form-label fw-semibold">
            {{ isset($producto) ? 'Agregar imágenes' : 'Imágenes del producto' }}
            <span class="text-muted fw-normal">(máx. 5 archivos · 3 MB c/u)</span>
        </label>
        <input type="file" name="imagenes[]" multiple
               accept="image/*"
               class="form-control @error('imagenes') is-invalid @enderror @error('imagenes.*') is-invalid @enderror"
               id="inputImagenes">
        @error('imagenes')   <div class="invalid-feedback">{{ $message }}</div>@enderror
        @error('imagenes.*') <div class="invalid-feedback">{{ $message }}</div>@enderror

        <div id="previewImagenes" class="d-flex flex-wrap gap-2 mt-2"></div>
        <small id="contadorImagenes" class="text-muted d-none mt-1"></small>
    </div>

    <div class="col-12">
        <div class="form-check">
            <input type="checkbox" name="activo" value="1" id="activo" class="form-check-input"
                {{ old('activo', $producto->activo ?? true) ? 'checked' : '' }}>
            <label for="activo" class="form-check-label fw-semibold">Producto activo (visible en tienda)</label>
        </div>
    </div>
</div>

<style>.img-preview-wrap {
    position: relative;
    width: 80px;
    height: 80px;
}
.img-preview-wrap img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    display: block;
}
.img-preview-wrap:first-child img {
    border-color: #0d6efd;
}
.img-preview-wrap .badge-principal {
    position: absolute;
    bottom: 2px;
    left: 2px;
    font-size: .55rem;
    padding: 1px 4px;
    background: #0d6efd;
    color: #fff;
    border-radius: 3px;
    line-height: 1.4;
    pointer-events: none;
}
.img-preview-wrap .btn-quitar-preview {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 20px;
    height: 20px;
    font-size: .7rem;
    line-height: 20px;
    text-align: center;
    padding: 0;
    border-radius: 50%;
    background: rgba(220,53,69,.85);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
.img-preview-wrap .btn-quitar-preview:hover {
    background: rgba(220,53,69,1);
}
</style>
<script>
(function () {
    const MAX  = 5;
    const input    = document.getElementById('inputImagenes');
    const preview  = document.getElementById('previewImagenes');
    const contador = document.getElementById('contadorImagenes');

    let archivos = [];

    function syncInput() {
        const dt = new DataTransfer();
        archivos.forEach(f => dt.items.add(f));
        input.files = dt.files;
    }

    function actualizarContador() {
        if (archivos.length === 0) {
            contador.classList.add('d-none');
        } else {
            contador.classList.remove('d-none');
            contador.textContent = archivos.length + ' imagen' + (archivos.length > 1 ? 'es' : '') +
                ' seleccionada' + (archivos.length > 1 ? 's' : '') +
                ' — la primera será la principal';
        }
    }

    function renderPreview() {
        preview.innerHTML = '';
        archivos.forEach((file, i) => {
            const reader = new FileReader();
            reader.onload = e => {
                const wrap = document.createElement('div');
                wrap.className = 'img-preview-wrap';
                wrap.innerHTML =
                    '<img src="' + e.target.result + '" alt="' + file.name + '">' +
                    (i === 0 ? '<span class="badge-principal">Principal</span>' : '') +
                    '<button type="button" class="btn-quitar-preview" title="Quitar imagen">×</button>';

                wrap.querySelector('.btn-quitar-preview').addEventListener('click', function () {
                    archivos.splice(i, 1);
                    syncInput();
                    renderPreview();
                    actualizarContador();
                });

                preview.appendChild(wrap);
            };
            reader.readAsDataURL(file);
        });
        actualizarContador();
    }

    input.addEventListener('change', function () {
        const restantes = MAX - archivos.length;
        if (restantes <= 0) { this.value = ''; return; }

        Array.from(this.files).slice(0, restantes).forEach(f => archivos.push(f));
        syncInput();
        renderPreview();
    });
})();
</script>
