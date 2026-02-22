@extends('admin.layout')
@section('title', 'Nueva Categoría')
@section('header', 'Nueva Categoría')

@section('content')
<div class="card border-0 shadow-sm" style="max-width:600px">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.categorias.store') }}">
            @csrf
            @include('admin.categorias._form')
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-check-lg me-1"></i>Guardar categoría
                </button>
                <a href="{{ route('admin.categorias.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
