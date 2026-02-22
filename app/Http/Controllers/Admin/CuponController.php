<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use Illuminate\Http\Request;

class CuponController extends Controller
{
    public function index()
    {
        $cupones = Cupon::orderByDesc('id_cupon')->paginate(15);
        return view('admin.cupones.index', compact('cupones'));
    }

    public function create()
    {
        return view('admin.cupones.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo'           => 'required|string|max:50|unique:cupones',
            'tipo_descuento'   => 'required|in:porcentaje,fijo',
            'valor'            => 'required|numeric|min:0',
            'fecha_expiracion' => 'nullable|date|after:today',
            'activo'           => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        Cupon::create($data);

        return redirect()->route('admin.cupones.index')
            ->with('success', 'Cupón creado correctamente.');
    }

    public function edit($id)
    {
        $cupon = Cupon::findOrFail($id);
        return view('admin.cupones.edit', compact('cupon'));
    }

    public function update(Request $request, $id)
    {
        $cupon = Cupon::findOrFail($id);

        $data = $request->validate([
            'codigo'           => 'required|string|max:50|unique:cupones,codigo,' . $id . ',id_cupon',
            'tipo_descuento'   => 'required|in:porcentaje,fijo',
            'valor'            => 'required|numeric|min:0',
            'fecha_expiracion' => 'nullable|date',
            'activo'           => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo');
        $cupon->update($data);

        return redirect()->route('admin.cupones.index')
            ->with('success', 'Cupón actualizado.');
    }

    public function destroy($id)
    {
        Cupon::findOrFail($id)->delete();
        return redirect()->route('admin.cupones.index')
            ->with('success', 'Cupón eliminado.');
    }
}
