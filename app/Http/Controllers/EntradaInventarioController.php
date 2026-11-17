<?php

namespace App\Http\Controllers;

use App\Models\EntradaInventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntradaInventarioController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $entradas = EntradaInventario::with('producto')
            ->when($q, fn ($x) => $x->whereHas('producto', fn ($p) => $p->where('nombre', 'like', "%$q%")->orWhere('sku', 'like', "%$q%"))
                ->orWhere('proveedor', 'like', "%$q%")->orWhere('referencia', 'like', "%$q%"))
            ->orderByDesc('id')->paginate(15)->withQueryString();
        return view('entradas.index', compact('entradas', 'q'));
    }

    public function create()
    {
        return view('entradas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'costo_unitario' => 'required|numeric|min:0',
            'proveedor' => 'nullable|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
        ]);
        DB::transaction(function () use ($data) {
            $producto = Producto::lockForUpdate()->find($data['producto_id']);
            $total = (float) $data['costo_unitario'] * (int) $data['cantidad'];
            EntradaInventario::create([
                'fecha' => $data['fecha'],
                'producto_id' => $data['producto_id'],
                'cantidad' => $data['cantidad'],
                'costo_unitario' => $data['costo_unitario'],
                'total_compra' => $total,
                'proveedor' => $data['proveedor'] ?? null,
                'referencia' => $data['referencia'] ?? null,
                'notas' => $data['notas'] ?? null,
            ]);
            $producto->stock_actual += (int) $data['cantidad'];
            if ((float) $data['costo_unitario'] != (float) $producto->costo_unitario) {
                $producto->costo_unitario = $data['costo_unitario'];
            }
            $producto->save();
        });
        return redirect()->route('entradas.index')->with('success', 'Entrada registrada.');
    }
}