<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #212529; }
        .sidebar .nav-link { color: #adb5bd; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,.1); border-radius: .375rem; }
        .sidebar .nav-link i { width: 20px; }
        .main-content { background: #f8f9fa; min-height: 100vh; }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    {{-- Sidebar --}}
    <div class="sidebar p-3" style="width:240px;flex-shrink:0">
        <a href="{{ route('admin.dashboard') }}" class="text-white text-decoration-none d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-shirt fs-5"></i>
            <span class="fw-bold">Panel Admin</span>
        </a>
        <nav class="nav flex-column gap-1">
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
            <a href="{{ route('admin.productos.index') }}"
               class="nav-link {{ request()->routeIs('admin.productos*') ? 'active' : '' }}">
                <i class="bi bi-box-seam me-2"></i>Productos
            </a>
            <a href="{{ route('admin.categorias.index') }}"
               class="nav-link {{ request()->routeIs('admin.categorias*') ? 'active' : '' }}">
                <i class="bi bi-tags me-2"></i>Categorías
            </a>
            <a href="{{ route('admin.equipos.index') }}"
               class="nav-link {{ request()->routeIs('admin.equipos*') ? 'active' : '' }}">
                <i class="bi bi-shield-fill me-2"></i>Equipos
            </a>
            <a href="{{ route('admin.pedidos.index') }}"
               class="nav-link {{ request()->routeIs('admin.pedidos*') ? 'active' : '' }}">
                <i class="bi bi-bag me-2"></i>Pedidos
            </a>
            <a href="{{ route('admin.cupones.index') }}"
               class="nav-link {{ request()->routeIs('admin.cupones*') ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated me-2"></i>Cupones
            </a>
            <hr class="border-secondary my-2">
            <a href="{{ route('almacen.dashboard') }}"
               class="nav-link {{ request()->routeIs('almacen.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam me-2"></i>Almacén
            </a>
            <hr class="border-secondary my-2">
            <a href="{{ route('home') }}" class="nav-link">
                <i class="bi bi-shop me-2"></i>Ver tienda
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link btn btn-link text-start w-100 p-0" style="color:#adb5bd">
                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                </button>
            </form>
        </nav>
    </div>

    {{-- Contenido --}}
    <div class="main-content flex-grow-1 p-4">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold">@yield('header', 'Dashboard')</h4>
            <span class="text-muted small">
                <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->nombre }} {{ Auth::user()->apellido }}
            </span>
        </div>

        {{-- Alertas --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
