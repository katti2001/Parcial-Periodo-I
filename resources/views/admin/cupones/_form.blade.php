<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
        <input type="text" name="codigo" class="form-control @error('codigo') is-invalid @enderror"
               value="{{ old('codigo', $cupon->codigo ?? '') }}" required>
        @error('codigo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Tipo de descuento <span class="text-danger">*</span></label>
        <select name="tipo_descuento" class="form-select" required>
            <option value="porcentaje" {{ old('tipo_descuento', $cupon->tipo_descuento ?? '') === 'porcentaje' ? 'selected' : '' }}>Porcentaje (%)</option>
            <option value="fijo" {{ old('tipo_descuento', $cupon->tipo_descuento ?? '') === 'fijo' ? 'selected' : '' }}>Monto fijo ($)</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Valor <span class="text-danger">*</span></label>
        <input type="number" name="valor" step="0.01" min="0"
               class="form-control @error('valor') is-invalid @enderror"
               value="{{ old('valor', $cupon->valor ?? '') }}" required>
        @error('valor')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Fecha de expiración</label>
        <input type="date" name="fecha_expiracion" class="form-control"
               value="{{ old('fecha_expiracion', isset($cupon->fecha_expiracion) ? $cupon->fecha_expiracion->format('Y-m-d') : '') }}">
    </div>
    <div class="col-12">
        <div class="form-check">
            <input type="checkbox" name="activo" value="1" id="activo" class="form-check-input"
                {{ old('activo', $cupon->activo ?? true) ? 'checked' : '' }}>
            <label for="activo" class="form-check-label fw-semibold">Cupón activo</label>
        </div>
    </div>
</div>
