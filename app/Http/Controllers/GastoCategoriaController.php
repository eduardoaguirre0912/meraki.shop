<?php

namespace App\Http\Controllers;

use App\Models\GastoCategoria;
use Illuminate\Http\Request;

class GastoCategoriaController extends Controller
{
    public function index()
    {
        $categorias = GastoCategoria::orderBy('nombre')->get();
        return view('gasto_categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nombre' => 'required|string|max:255|unique:gasto_categorias,nombre']);
        GastoCategoria::create($data);
        return redirect()->route('gasto-categorias.index')->with('success', 'Categoría creada.');
    }

    public function update(Request $request, GastoCategoria $categoria)
    {
        $data = $request->validate(['nombre' => 'required|string|max:255|unique:gasto_categorias,nombre,' . $categoria->id]);
        $categoria->update($data);
        return redirect()->route('gasto-categorias.index')->with('success', 'Categoría actualizada.');
    }

    public function toggle(GastoCategoria $categoria)
    {
        $categoria->update(['activo' => !$categoria->activo]);
        return redirect()->route('gasto-categorias.index')->with('success', 'Estado actualizado.');
    }
}