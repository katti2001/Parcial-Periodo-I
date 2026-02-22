@extends('almacen.layout')
@section('title', 'Nuevo Proveedor')
@section('header', 'Nuevo Proveedor')

@section('content')
<div class="card border-0 shadow-sm" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ route('almacen.proveedores.store') }}">
            @csrf
            @include('almacen.proveedores._form')
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Guardar
                </button>
                <a href="{{ route('almacen.proveedores.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
