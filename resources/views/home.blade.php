@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="text-center py-5">
    <h1 class="display-5 fw-bold"><i class="bi bi-shirt me-2"></i>Tienda Deportiva</h1>
    <p class="lead text-muted">Camisetas y equipos de fútbol al mejor precio</p>
    @guest
        <a href="{{ route('registro') }}" class="btn btn-dark btn-lg me-2">
            <i class="bi bi-person-plus me-1"></i>Crear cuenta
        </a>
        <a href="{{ route('login') }}" class="btn btn-outline-dark btn-lg">
            <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión
        </a>
    @endguest
    @auth
        <p class="mt-3">Bienvenido, <strong>{{ Auth::user()->nombre }}</strong>. ¡Explora nuestro catálogo!</p>
    @endauth
</div>
@endsection
