<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function index()
    {
        $equipos = Equipo::withCount('productos')->orderBy('nombre')->get();

        return view('admin.equipos.index', compact('equipos'));
    }

    public function create()
    {
        return view('admin.equipos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:equipos,nombre',
            'pais'   => 'nullable|string|max:50',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe un equipo con ese nombre.',
            'nombre.max'      => 'El nombre no puede superar 100 caracteres.',
            'pais.max'        => 'El país no puede superar 50 caracteres.',
        ]);

        Equipo::create($request->only('nombre', 'pais'));

        return redirect()->route('admin.equipos.index')
            ->with('success', 'Equipo creado correctamente.');
    }

    public function edit($id)
    {
        $equipo = Equipo::findOrFail($id);

        return view('admin.equipos.edit', compact('equipo'));
    }

    public function update(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100|unique:equipos,nombre,' . $id . ',id_equipo',
            'pais'   => 'nullable|string|max:50',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe un equipo con ese nombre.',
            'nombre.max'      => 'El nombre no puede superar 100 caracteres.',
            'pais.max'        => 'El país no puede superar 50 caracteres.',
        ]);

        $equipo->update($request->only('nombre', 'pais'));

        return redirect()->route('admin.equipos.index')
            ->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy($id)
    {
        $equipo = Equipo::withCount('productos')->findOrFail($id);

        if ($equipo->productos_count > 0) {
            return back()->with('error',
                "No se puede eliminar: el equipo tiene {$equipo->productos_count} producto(s) asociado(s).");
        }

        $equipo->delete();

        return back()->with('success', 'Equipo eliminado correctamente.');
    }
}
