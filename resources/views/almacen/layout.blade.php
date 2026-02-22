<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Almacén') — Tienda Deportiva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { overflow-x: hidden; }
        #sidebar { min-height: 100vh; background: #1a2035; width: 240px; }
        #sidebar .nav-link { color: #adb5bd; border-radius: .375rem; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { background: rgba(255,255,255,.1); color: #fff; }
        #sidebar .nav-link .bi { width: 20px; }
        #sidebar .sidebar-brand { color: #fff; font-weight: 700; font-size: 1.1rem; }
        #main { flex: 1; background: #f8f9fa; min-height: 100vh; }
        .page-header { border-bottom: 1px solid #dee2e6; padding-bottom: .75rem; margin-bottom: 1.5rem; }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">

    {{-- Sidebar --}}
    <nav id="sidebar" class="p-3 d-flex flex-column">
        <a href="{{ route('almacen.dashboard') }}" class="sidebar-brand text-decoration-none mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-box-seam fs-5"></i> Panel Almacén
        </a>

        <ul class="nav flex-column gap-1 flex-grow-1">
            <li class="nav-item">
                <a href="{{ route('almacen.dashboard') }}"
                   class="nav-link {{ request()->routeIs('almacen.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('almacen.proveedores.index') }}"
                   class="nav-link {{ request()->routeIs('almacen.proveedores.*') ? 'active' : '' }}">
                    <i class="bi bi-truck me-2"></i>Proveedores
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('almacen.compras.index') }}"
                   class="nav-link {{ request()->routeIs('almacen.compras.*') ? 'active' : '' }}">
                    <i class="bi bi-download me-2"></i>Compras / Entradas
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('almacen.kardex.index') }}"
                   class="nav-link {{ request()->routeIs('almacen.kardex.*') ? 'active' : '' }}">
                    <i class="bi bi-card-list me-2"></i>Kardex
                </a>
            </li>
        </ul>

        <div class="mt-auto pt-3 border-top border-secondary">
            <p class="text-muted small mb-1">
                <i class="bi bi-person-circle me-1"></i>
                {{ Auth::user()->nombre }} {{ Auth::user()->apellido }}
            </p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-danger w-100">
                    <i class="bi bi-box-arrow-right me-1"></i>Salir
                </button>
            </form>
        </div>
    </nav>

    {{-- Contenido principal --}}
    <div id="main" class="p-4 flex-grow-1">

        {{-- Alertas --}}
        @foreach(['success' => 'success', 'error' => 'danger', 'warning' => 'warning'] as $key => $type)
            @if(session($key))
                <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
                    {{ session($key) }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        @endforeach

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="page-header d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0">@yield('header', 'Almacén')</h4>
            @yield('header-actions')
        </div>

        @yield('content')
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
