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
    <div class="col-12">
        <div class="form-check">
            <input type="checkbox" name="activo" value="1" id="activo" class="form-check-input"
                {{ old('activo', $producto->activo ?? true) ? 'checked' : '' }}>
            <label for="activo" class="form-check-label fw-semibold">Producto activo (visible en tienda)</label>
        </div>
    </div>
</div>
