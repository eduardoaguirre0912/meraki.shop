<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use Illuminate\Http\Request;

class GastoController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $linea = $request->get('linea');
        $estado = $request->get('estado');
        $gastos = Gasto::when($q, fn ($x) => $x->where('concepto', 'like', "%$q%")
            ->orWhere('tipo_gasto', 'like', "%$q%"))
            ->when($linea, fn ($x) => $x->where('linea_negocio', $linea))
            ->when($estado, fn ($x) => $x->where('estado', $estado))
            ->orderByDesc('id')->paginate(15)->withQueryString();
        return view('gastos.index', compact('gastos', 'q', 'linea', 'estado'));
    }

    public function create()
    {
        return view('gastos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'categoria_gasto_id' => 'nullable|exists:gasto_categorias,id',
            'tipo_gasto' => 'nullable|string',
            'concepto' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'monto' => 'required|numeric',
            'es_devolucion' => 'nullable|boolean',
            'metodo_pago' => 'required|string',
            'linea_negocio' => 'required|string',
            'estado' => 'required|string',
            'notas' => 'nullable|string',
            'comprobante' => 'nullable|file|max:4096',
        ]);
        if (!empty($data['categoria_gasto_id'])) {
            $cat = \App\Models\GastoCategoria::find($data['categoria_gasto_id']);
            $data['tipo_gasto'] = $cat?->nombre ?? ($data['tipo_gasto'] ?? 'Otros');
        } elseif (empty($data['tipo_gasto'])) {
            $data['tipo_gasto'] = 'Otros';
        }
        if ($request->hasFile('comprobante')) {
            $data['comprobante'] = $request->file('comprobante')->store('comprobantes', 'public');
        }
        if ($request->boolean('es_devolucion')) {
            $data['monto'] = -abs((float) $data['monto']);
            $data['tipo_gasto'] = 'Devolución/Reembolso';
        }
        Gasto::create($data);
        return redirect()->route('gastos.index')->with('success', 'Gasto registrado.');
    }

    public function edit(Gasto $gasto)
    {
        return view('gastos.edit', compact('gasto'));
    }

    public function update(Request $request, Gasto $gasto)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'categoria_gasto_id' => 'nullable|exists:gasto_categorias,id',
            'tipo_gasto' => 'nullable|string',
            'concepto' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'monto' => 'required|numeric',
            'es_devolucion' => 'nullable|boolean',
            'metodo_pago' => 'required|string',
            'linea_negocio' => 'required|string',
            'estado' => 'required|string',
            'notas' => 'nullable|string',
            'comprobante' => 'nullable|file|max:4096',
        ]);
        if (!empty($data['categoria_gasto_id'])) {
            $cat = \App\Models\GastoCategoria::find($data['categoria_gasto_id']);
            $data['tipo_gasto'] = $cat?->nombre ?? ($data['tipo_gasto'] ?? $gasto->tipo_gasto);
        }
        if ($request->hasFile('comprobante')) {
            if ($gasto->comprobante) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($gasto->comprobante);
            }
            $data['comprobante'] = $request->file('comprobante')->store('comprobantes', 'public');
        }
        if ($request->boolean('es_devolucion')) {
            $data['monto'] = -abs((float) $data['monto']);
            $data['tipo_gasto'] = 'Devolución/Reembolso';
        }
        $gasto->update($data);
        return redirect()->route('gastos.index')->with('success', 'Gasto actualizado.');
    }

    public function destroy(Gasto $gasto)
    {
        $gasto->delete();
        return redirect()->route('gastos.index')->with('success', 'Gasto eliminado.');
    }
}