{{-- Formulario compartido para crear/editar equipo --}}
<div class="mb-3">
    <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
    <input
        type="text"
        name="nombre"
        class="form-control @error('nombre') is-invalid @enderror"
        value="{{ old('nombre', $equipo->nombre ?? '') }}"
        maxlength="100"
        required
        autofocus
        placeholder="Ej: Real Madrid"
    >
    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">País</label>
    <input
        type="text"
        name="pais"
        class="form-control @error('pais') is-invalid @enderror"
        value="{{ old('pais', $equipo->pais ?? '') }}"
        maxlength="50"
        placeholder="Ej: España"
    >
    @error('pais')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
