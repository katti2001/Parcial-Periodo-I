@extends('admin.layout')
@section('title', 'Nuevo Producto')
@section('header', 'Nuevo Producto')

@section('content')
<div class="card border-0 shadow-sm" style="max-width:700px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.productos.store') }}">
            @csrf
            @include('admin.productos._form')
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-save me-1"></i>Guardar
                </button>
                <a href="{{ route('admin.productos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
