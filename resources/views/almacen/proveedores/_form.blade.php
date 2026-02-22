<div class="mb-3">
    <label class="form-label">Nombre de la empresa <span class="text-danger">*</span></label>
    <input type="text" name="nombre_empresa" class="form-control @error('nombre_empresa') is-invalid @enderror"
           value="{{ old('nombre_empresa', $proveedor->nombre_empresa ?? '') }}" required maxlength="100">
    @error('nombre_empresa')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Contacto</label>
        <input type="text" name="contacto" class="form-control @error('contacto') is-invalid @enderror"
               value="{{ old('contacto', $proveedor->contacto ?? '') }}" maxlength="100">
        @error('contacto')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Teléfono</label>
        <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror"
               value="{{ old('telefono', $proveedor->telefono ?? '') }}" maxlength="20">
        @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="mb-3 mt-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email', $proveedor->email ?? '') }}" maxlength="100">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
