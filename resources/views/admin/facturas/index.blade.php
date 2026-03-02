@extends('layouts.admin')

@section('title', 'Facturas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-0">Facturas</h3>
        <p class="text-muted mb-0">Listado de facturas de clientes</p>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-3">
            <div class="col-sm-3">
                <label class="form-label small text-muted">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach(['borrador','emitida','pagada','vencida','cancelada'] as $estado)
                        <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst($estado) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label small text-muted">Usuario ID</label>
                <input type="number" name="usuario" value="{{ request('usuario') }}" class="form-control form-control-sm" />
            </div>
            <div class="col-sm-2">
                <label class="form-label small text-muted">Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm" />
            </div>
            <div class="col-sm-2">
                <label class="form-label small text-muted">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm" />
            </div>
            <div class="col-sm-3 d-flex align-items-end gap-2">
                <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-filter"></i> Filtrar</button>
                <a href="{{ route('admin.facturas.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Folio</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Fecha emisión</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($facturas as $f)
                <tr>
                    <td class="fw-semibold">{{ $f->numero }}</td>
                    <td>
                        <div class="small">{{ optional($f->usuario)->nombre }} {{ optional($f->usuario)->apellido }}</div>
                        <div class="text-muted small">ID: {{ $f->id_usuario }}</div>
                    </td>
                    <td><span class="badge bg-dark">{{ $f->estado }}</span></td>
                    <td class="fw-semibold">${{ number_format($f->total, 2) }} {{ $f->moneda }}</td>
                    <td class="text-muted small">{{ optional($f->fecha_emision)->format('d/m/Y H:i') }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.facturas.show', $f->id_factura) }}" class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No hay facturas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $facturas->links() }}</div>
</div>
@endsection
