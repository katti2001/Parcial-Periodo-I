@extends('almacen.layout')

@section('title', 'Panel Almacén')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="bi bi-box-seam me-2"></i>Panel Almacén</h2>
    <span class="badge bg-secondary fs-6">{{ Auth::user()->nombre }} {{ Auth::user()->apellido }}</span>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <a href="{{ route('almacen.compras.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center p-4 h-100 card-hover">
                <i class="bi bi-download display-5 text-primary"></i>
                <h6 class="mt-3 fw-semibold">Compras / Entradas</h6>
                <small class="text-muted">Registrar entradas de inventario</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('almacen.kardex.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center p-4 h-100 card-hover">
                <i class="bi bi-card-list display-5 text-success"></i>
                <h6 class="mt-3 fw-semibold">Kardex</h6>
                <small class="text-muted">Historial de movimientos</small>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('almacen.proveedores.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm text-center p-4 h-100 card-hover">
                <i class="bi bi-truck display-5 text-warning"></i>
                <h6 class="mt-3 fw-semibold">Proveedores</h6>
                <small class="text-muted">Gestionar proveedores</small>
            </div>
        </a>
    </div>
</div>

<style>
.card-hover { transition: transform .15s, box-shadow .15s; }
.card-hover:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.12) !important; }
</style>
@endsection
