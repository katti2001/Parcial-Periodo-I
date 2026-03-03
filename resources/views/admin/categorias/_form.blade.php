<div class="mb-3">
    <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
    <input
        type="text"
        name="nombre"
        class="form-control @error('nombre') is-invalid @enderror"
        value="{{ old('nombre', $categoria->nombre ?? '') }}"
        maxlength="50"
        required
        autofocus
    >
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">Descripción</label>
    <textarea
        name="descripcion"
        rows="3"
        class="form-control @error('descripcion') is-invalid @enderror"
        placeholder="Descripción opcional de la categoría..."
    >{{ old('descripcion', $categoria->descripcion ?? '') }}</textarea>
    @error('descripcion')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
