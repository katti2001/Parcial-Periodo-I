@extends('layouts.app')

@section('title', 'Crear Cuenta')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h4 class="card-title text-center mb-4 fw-bold">
                    <i class="bi bi-person-plus me-2 text-dark"></i>Crear Cuenta
                </h4>

                <form method="POST" action="{{ route('registro') }}">
                    @csrf

                    {{-- Nombre y Apellido --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label fw-semibold">Nombre</label>
                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                class="form-control @error('nombre') is-invalid @enderror"
                                value="{{ old('nombre') }}"
                                placeholder="Juan"
                                autofocus
                                required
                            >
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido" class="form-label fw-semibold">Apellido</label>
                            <input
                                type="text"
                                id="apellido"
                                name="apellido"
                                class="form-control @error('apellido') is-invalid @enderror"
                                value="{{ old('apellido') }}"
                                placeholder="Pérez"
                                required
                            >
                            @error('apellido')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Correo electrónico</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="correo@ejemplo.com"
                            required
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Contraseña</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Mínimo 8 caracteres"
                            required
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirmar Password --}}
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">Confirmar contraseña</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Repite tu contraseña"
                            required
                        >
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-dark btn-lg">
                            <i class="bi bi-person-check me-2"></i>Crear cuenta
                        </button>
                    </div>
                </form>

                <hr>

                <p class="text-center mb-0">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Inicia sesión</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
