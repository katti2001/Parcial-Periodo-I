@extends('admin.layout')
@section('title', 'Editar Equipo')
@section('header', 'Editar Equipo')

@section('content')
<div class="card border-0 shadow-sm" style="max-width:500px">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.equipos.update', $equipo->id_equipo) }}">
            @csrf @method('PUT')
            @include('admin.equipos._form')
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-check-lg me-1"></i>Actualizar equipo
                </button>
                <a href="{{ route('admin.equipos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
