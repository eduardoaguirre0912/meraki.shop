<?php

namespace App\Http\Controllers;

use App\Models\PuntoEncuentro;
use Illuminate\Http\Request;

class PuntoEncuentroController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $puntos = PuntoEncuentro::when($q, fn ($x) => $x->where('nombre', 'like', "%$q%"))->orderBy('nombre')->paginate(15)->withQueryString();
        return view('puntos.index', compact('puntos', 'q'));
    }

    public function create()
    {
        return view('puntos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'referencias' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);
        $data['activo'] = $request->boolean('activo', true);
        PuntoEncuentro::create($data);
        return redirect()->route('puntos.index')->with('success', 'Punto de encuentro creado.');
    }

    public function edit(PuntoEncuentro $punto)
    {
        return view('puntos.edit', compact('punto'));
    }

    public function update(Request $request, PuntoEncuentro $punto)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'referencias' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);
        $data['activo'] = $request->boolean('activo', true);
        $punto->update($data);
        return redirect()->route('puntos.index')->with('success', 'Punto actualizado.');
    }

    public function destroy(PuntoEncuentro $punto)
    {
        $punto->delete();
        return redirect()->route('puntos.index')->with('success', 'Punto eliminado.');
    }
}