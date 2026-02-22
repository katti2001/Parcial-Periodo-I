@extends('layouts.app')

@section('title', 'Panel Administrador')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="bi bi-speedometer2 me-2"></i>Panel Administrador</h2>
    <span class="badge bg-dark fs-6">{{ Auth::user()->nombre }} {{ Auth::user()->apellido }}</span>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <i class="bi bi-box-seam display-5 text-primary"></i>
            <h6 class="mt-2 fw-semibold">Productos</h6>
            <small class="text-muted">Gestionar catálogo</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <i class="bi bi-people display-5 text-success"></i>
            <h6 class="mt-2 fw-semibold">Usuarios</h6>
            <small class="text-muted">Gestionar usuarios</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <i class="bi bi-cart-check display-5 text-warning"></i>
            <h6 class="mt-2 fw-semibold">Pedidos</h6>
            <small class="text-muted">Ver pedidos</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <i class="bi bi-tags display-5 text-danger"></i>
            <h6 class="mt-2 fw-semibold">Cupones</h6>
            <small class="text-muted">Gestionar cupones</small>
        </div>
    </div>
</div>
@endsection
