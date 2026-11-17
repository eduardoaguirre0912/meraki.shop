<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $linea = $request->get('linea');
        $estado = $request->get('estado'); // bajo, agotado, ok
        $productos = Producto::with('componentes.producto')->when($q, fn ($x) => $x->where(function ($w) use ($q) {
            $w->where('nombre', 'like', "%$q%")
              ->orWhere('sku', 'like', "%$q%")
              ->orWhere('marca', 'like', "%$q%")
              ->orWhere('categoria', 'like', "%$q%")
              ->orWhere('linea_negocio', 'like', "%$q%")
              ->orWhere('variante', 'like', "%$q%")
              ->orWhere('proveedor', 'like', "%$q%")
              ->orWhere('notas', 'like', "%$q%");
        }))
            ->when($linea, fn ($x) => $x->where('linea_negocio', $linea))
            ->when($estado === 'agotado', fn ($x) => $x->where('stock_actual', '<=', 0))
            ->when($estado === 'bajo', fn ($x) => $x->whereColumn('stock_actual', '<=', 'stock_minimo')->where('stock_actual', '>', 0))
            ->orderByDesc('id')->paginate(15)->withQueryString();
        return view('productos.index', compact('productos', 'q', 'linea', 'estado'));
    }

    public function buscar(Request $request)
    {
        $q = $request->get('q');
        $productos = Producto::where(function ($w) use ($q) {
            $w->where('nombre', 'like', "%$q%")->orWhere('sku', 'like', "%$q%");
        })->limit(10)->get(['id', 'sku', 'nombre', 'precio_venta', 'costo_unitario', 'stock_actual', 'linea_negocio']);
        return response()->json($productos);
    }

    public function create()
    {
        return view('productos.create');
    }

    public function store(Request $request)
    {
        $this->filterEmptyFiles($request);
        $data = $request->validate([
            'linea_negocio' => 'required|string',
            'nombre' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'marca' => 'nullable|string|max:255',
            'variante' => 'nullable|string|max:255',
            'stock_actual' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'costo_unitario' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'proveedor' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'es_combo' => 'nullable|boolean',
            'componentes' => 'nullable|array',
            'componentes.*.producto_id' => 'nullable|exists:productos,id',
            'componentes.*.cantidad' => 'nullable|integer|min:1',
            'foto' => 'nullable|image|max:2048',
            'fotos' => 'nullable|array',
            'fotos.*' => 'nullable|image|max:2048',
        ]);
        $data['stock_actual'] = $data['stock_actual'] ?? 0;
        $data['stock_minimo'] = $data['stock_minimo'] ?? 0;
        $data['costo_unitario'] = $data['costo_unitario'] ?? 0;
        $data['precio_venta'] = $data['precio_venta'] ?? 0;
        $data['es_combo'] = $request->boolean('es_combo');
        $producto = Producto::create($data);
        $this->handleFotos($request, $producto);
        $this->handleComponentes($request, $producto);
        return redirect()->route('productos.index')->with('success', 'Producto creado.');
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    public function update(Request $request, Producto $producto)
    {
        $this->filterEmptyFiles($request);
        $data = $request->validate([
            'linea_negocio' => 'required|string',
            'nombre' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'marca' => 'nullable|string|max:255',
            'variante' => 'nullable|string|max:255',
            'stock_actual' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'costo_unitario' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'proveedor' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'es_combo' => 'nullable|boolean',
            'componentes' => 'nullable|array',
            'componentes.*.producto_id' => 'nullable|exists:productos,id',
            'componentes.*.cantidad' => 'nullable|integer|min:1',
            'foto' => 'nullable|image|max:2048',
            'fotos' => 'nullable|array',
            'fotos.*' => 'nullable|image|max:2048',
            'delete_fotos' => 'nullable|array',
        ]);
        $data['es_combo'] = $request->boolean('es_combo');
        $producto->update($data);
        $this->handleFotos($request, $producto, true);
        $this->handleComponentes($request, $producto);
        return redirect()->route('productos.index')->with('success', 'Producto actualizado.');
    }

    public function destroy(Producto $producto)
    {
        foreach ($producto->fotos as $f) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($f->path);
        }
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado.');
    }

    protected function filterEmptyFiles(\Illuminate\Http\Request $request): void
    {
        if ($request->hasFile('foto')) {
            $f = $request->file('foto');
            if (!$f || !$f->isValid() || $f->getSize() === 0) {
                $request->files->remove('foto');
            }
        }
        if ($request->hasFile('fotos')) {
            $files = array_values(array_filter($request->file('fotos'), function ($f) {
                return $f && $f->isValid() && $f->getSize() > 0;
            }));
            if (empty($files)) {
                $request->files->remove('fotos');
            } else {
                $request->files->set('fotos', $files);
            }
        }
    }

    protected function handleComponentes(\Illuminate\Http\Request $request, Producto $producto): void
    {
        if (!$request->boolean('es_combo')) {
            $producto->componentes()->delete();
            return;
        }
        $producto->componentes()->delete();
        foreach ($request->input('componentes', []) as $c) {
            if (empty($c['producto_id'])) continue;
            \App\Models\ComboComponente::create([
                'combo_producto_id' => $producto->id,
                'producto_id' => $c['producto_id'],
                'cantidad' => max(1, (int) ($c['cantidad'] ?? 1)),
            ]);
        }
    }

    protected function handleFotos(\Illuminate\Http\Request $request, Producto $producto, bool $isUpdate = false): void
    {
        if ($isUpdate && $request->filled('delete_fotos')) {
            foreach ($request->input('delete_fotos', []) as $fid) {
                $foto = \App\Models\ProductoFoto::find($fid);
                if ($foto && $foto->producto_id === $producto->id) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($foto->path);
                    $foto->delete();
                }
            }
        }

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('productos', 'public');
            // remove previous principal
            $producto->fotos()->where('principal', true)->each(function ($f) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($f->path);
                $f->delete();
            });
            \App\Models\ProductoFoto::create([
                'producto_id' => $producto->id,
                'path' => $path,
                'principal' => true,
                'orden' => 0,
            ]);
        }

        if ($request->hasFile('fotos')) {
            $orden = $producto->fotos()->max('orden') ?? 0;
            foreach ($request->file('fotos') as $file) {
                $path = $file->store('productos', 'public');
                $orden++;
                \App\Models\ProductoFoto::create([
                    'producto_id' => $producto->id,
                    'path' => $path,
                    'principal' => false,
                    'orden' => $orden,
                ]);
            }
        }
    }
}