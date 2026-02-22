{{-- Formulario compartido para crear/editar producto --}}
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

    {{-- Imágenes actuales (solo en edición) --}}
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

    {{-- Subir nuevas imágenes --}}
    <div class="col-12">
        <label class="form-label fw-semibold">
            {{ isset($producto) ? 'Agregar imágenes' : 'Imágenes del producto' }}
            <span class="text-muted fw-normal">(máx. 5 archivos · 3 MB c/u · JPG, PNG, WEBP)</span>
        </label>
        <input type="file" name="imagenes[]" multiple
               accept="image/jpeg,image/png,image/webp"
               class="form-control @error('imagenes') is-invalid @enderror @error('imagenes.*') is-invalid @enderror"
               id="inputImagenes">
        @error('imagenes')   <div class="invalid-feedback">{{ $message }}</div>@enderror
        @error('imagenes.*') <div class="invalid-feedback">{{ $message }}</div>@enderror
        <div id="previewImagenes" class="d-flex flex-wrap gap-2 mt-2"></div>
    </div>

    <div class="col-12">
        <div class="form-check">
            <input type="checkbox" name="activo" value="1" id="activo" class="form-check-input"
                {{ old('activo', $producto->activo ?? true) ? 'checked' : '' }}>
            <label for="activo" class="form-check-label fw-semibold">Producto activo (visible en tienda)</label>
        </div>
    </div>
</div>

{{-- Preview JS de imágenes seleccionadas --}}
<script>
document.getElementById('inputImagenes').addEventListener('change', function () {
    const preview = document.getElementById('previewImagenes');
    preview.innerHTML = '';
    Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'height:80px;width:80px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});
</script>
