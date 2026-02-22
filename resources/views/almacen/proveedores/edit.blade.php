@extends('almacen.layout')
@section('title', 'Editar Proveedor')
@section('header', 'Editar Proveedor')

@section('content')
<div class="card border-0 shadow-sm" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ route('almacen.proveedores.update', $proveedor->id_proveedor) }}">
            @csrf @method('PUT')
            @include('almacen.proveedores._form')
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Actualizar
                </button>
                <a href="{{ route('almacen.proveedores.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
