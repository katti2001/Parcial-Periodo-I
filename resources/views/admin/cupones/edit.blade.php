@extends('admin.layout')
@section('title', 'Editar Cupón')
@section('header', 'Editar Cupón')

@section('content')
<div class="card border-0 shadow-sm" style="max-width:500px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.cupones.update', $cupon->id_cupon) }}">
            @csrf @method('PUT')
            @include('admin.cupones._form')
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-dark"><i class="bi bi-save me-1"></i>Actualizar</button>
                <a href="{{ route('admin.cupones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
