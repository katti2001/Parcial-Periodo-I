<?php

namespace App\Http\Controllers\Almacen;

use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::orderBy('nombre_empresa')->paginate(15);
        return view('almacen.proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('almacen.proveedores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_empresa' => 'required|string|max:100',
            'contacto'       => 'nullable|string|max:100',
            'telefono'       => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
        ]);

        Proveedor::create($data);

        return redirect()->route('almacen.proveedores.index')
            ->with('success', 'Proveedor creado correctamente.');
    }

    public function edit($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('almacen.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $data = $request->validate([
            'nombre_empresa' => 'required|string|max:100',
            'contacto'       => 'nullable|string|max:100',
            'telefono'       => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
        ]);

        $proveedor->update($data);

        return redirect()->route('almacen.proveedores.index')
            ->with('success', 'Proveedor actualizado.');
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);

        if ($proveedor->compras()->exists()) {
            return redirect()->route('almacen.proveedores.index')
                ->with('error', 'No se puede eliminar: el proveedor tiene compras asociadas.');
        }

        $proveedor->delete();

        return redirect()->route('almacen.proveedores.index')
            ->with('success', 'Proveedor eliminado.');
    }
}
