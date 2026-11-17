<?php

namespace App\Http\Controllers;

use App\Models\Clienta;
use Illuminate\Http\Request;

class ClientaController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $clientas = Clienta::withCount('ventas')->when($q, fn ($x) => $x->where('nombre', 'like', "%$q%")
            ->orWhere('telefono', 'like', "%$q%"))
            ->orderByDesc('id')->paginate(15)->withQueryString();
        return view('clientas.index', compact('clientas', 'q'));
    }

    public function buscar(Request $request)
    {
        $tel = $request->get('telefono');
        $clienta = Clienta::where('telefono', $tel)->first();
        return response()->json($clienta);
    }

    public function create()
    {
        return view('clientas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'telefono' => 'required|digits:10|unique:clientas,telefono',
            'nombre' => 'required|string|max:255',
            'notas' => 'nullable|string',
        ]);
        Clienta::create($data);
        return redirect()->route('clientas.index')->with('success', 'Clienta creada.');
    }

    public function edit(Clienta $clienta)
    {
        return view('clientas.edit', compact('clienta'));
    }

    public function update(Request $request, Clienta $clienta)
    {
        $data = $request->validate([
            'telefono' => 'required|digits:10|unique:clientas,telefono,' . $clienta->id,
            'nombre' => 'required|string|max:255',
            'notas' => 'nullable|string',
        ]);
        $clienta->update($data);
        return redirect()->route('clientas.index')->with('success', 'Clienta actualizada.');
    }

    public function destroy(Clienta $clienta)
    {
        $clienta->delete();
        return redirect()->route('clientas.index')->with('success', 'Clienta eliminada.');
    }
}