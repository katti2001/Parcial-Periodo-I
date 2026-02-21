@extends('admin.layout')
@section('title', 'Editar Producto')
@section('header', 'Editar Producto')

@section('content')
<div class="card border-0 shadow-sm" style="max-width:700px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.productos.update', $producto->id_producto) }}">
            @csrf @method('PUT')
            @include('admin.productos._form')
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-save me-1"></i>Actualizar
                </button>
                <a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
