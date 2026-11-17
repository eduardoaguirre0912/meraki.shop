<?php

namespace App\Http\Controllers;

use App\Models\Clienta;
use App\Models\DetalleVenta;
use App\Models\Entrega;
use App\Models\Producto;
use App\Models\PuntoEncuentro;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $estado = $request->get('estado');
        $ventas = Venta::with(['clienta', 'detalles.producto'])
            ->when($q, fn ($x) => $x->where('folio', 'like', "%$q%")
                ->orWhereHas('clienta', fn ($c) => $c->where('nombre', 'like', "%$q%")->orWhere('telefono', 'like', "%$q%")))
            ->when($estado, fn ($x) => $x->where('estado_pago', $estado))
            ->orderByDesc('id')->paginate(15)->withQueryString();
        return view('ventas.index', compact('ventas', 'q', 'estado'));
    }

    public function create()
    {
        $productos = \App\Models\Producto::with(['fotos', 'componentes.producto'])->orderBy('nombre')->get(['id','sku','nombre','marca','variante','precio_venta','costo_unitario','stock_actual','es_combo']);
        return view('ventas.create', compact('productos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'telefono' => 'required|digits:10',
            'nombre' => 'required|string|max:255',
            'notas' => 'nullable|string',
            'fecha' => 'required|date',
            'metodo_pago' => 'required|string',
            'anticipo_pagado' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.costo_unitario' => 'required|numeric|min:0',
            'entrega_punto_encuentro_id' => 'nullable|exists:puntos_encuentro,id',
            'entrega_lugar' => 'nullable|string|max:255',
            'entrega_direccion' => 'nullable|string',
            'entrega_fecha' => 'nullable|date',
            'entrega_hora' => 'nullable',
            'entrega_indicaciones' => 'nullable|string',
            'entrega_nombre_recibe' => 'nullable|string|max:255',
            'entrega_telefono' => 'nullable|string|max:20',
            'entrega_estado' => 'nullable|string',
        ]);

        $anticipo = (float) ($data['anticipo_pagado'] ?? 0);

        try {
            DB::transaction(function () use ($data, $anticipo) {
            // clienta upsert
            $clienta = Clienta::firstOrNew(['telefono' => $data['telefono']]);
            $clienta->nombre = $data['nombre'];
            if (!empty($data['notas'])) {
                $clienta->notas = $data['notas'];
            }
            $clienta->save();

            $totalPedido = 0;
            $costoTotal = 0;

            $venta = Venta::create([
                'clienta_id' => $clienta->id,
                'fecha' => $data['fecha'],
                'metodo_pago' => $data['metodo_pago'],
                'anticipo_pagado' => $anticipo,
                'notas' => $data['notas'] ?? null,
                'total_pedido' => 0,
                'costo_total' => 0,
                'utilidad_bruta' => 0,
                'saldo_pendiente' => 0,
                'estado_pago' => 'Pendiente',
            ]);

            foreach ($data['items'] as $item) {
                $producto = Producto::with('componentes.producto')->lockForUpdate()->find($item['producto_id']);
                $cantidad = (int) $item['cantidad'];
                $precio = (float) $item['precio_unitario'];

                if ($producto->es_combo) {
                    $componentes = $producto->componentes;
                    if ($componentes->isEmpty()) {
                        throw new \RuntimeException("El combo {$producto->nombre} no tiene componentes.");
                    }
                    foreach ($componentes as $comp) {
                        $compProd = Producto::lockForUpdate()->find($comp->producto_id);
                        $req = $comp->cantidad * $cantidad;
                        if ($req > $compProd->stock_actual) {
                            throw new \RuntimeException("Stock insuficiente para {$compProd->nombre} (combo {$producto->nombre}).");
                        }
                    }
                    // calcular costo
                    $costo = 0;
                    foreach ($componentes as $comp) {
                        $compProd = Producto::find($comp->producto_id);
                        $costo += (float) $compProd->costo_unitario * $comp->cantidad;
                    }
                } else {
                    if ($cantidad > $producto->stock_actual) {
                        throw new \RuntimeException("Stock insuficiente para {$producto->nombre}.");
                    }
                    $costo = (float) $item['costo_unitario'];
                }

                $subtotal = $precio * $cantidad;
                $utilidadLinea = ($precio - $costo) * $cantidad;

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'costo_unitario' => $costo,
                    'subtotal' => $subtotal,
                    'utilidad_linea' => $utilidadLinea,
                ]);

                if ($producto->es_combo) {
                    foreach ($producto->componentes as $comp) {
                        $compProd = Producto::lockForUpdate()->find($comp->producto_id);
                        $compProd->stock_actual -= $comp->cantidad * $cantidad;
                        $compProd->save();
                    }
                } else {
                    $producto->stock_actual -= $cantidad;
                    $producto->save();
                }

                $totalPedido += $subtotal;
                $costoTotal += $costo * $cantidad;
            }

            $utilidadBruta = $totalPedido - $costoTotal;
            $saldo = $totalPedido - $anticipo;
            if ($saldo < 0) {
                $saldo = 0;
            }
            $estado = 'Pendiente';
            if ($anticipo >= $totalPedido && $totalPedido > 0) {
                $estado = 'Liquidado';
            } elseif ($anticipo > 0) {
                $estado = 'Parcial';
            }

            $venta->update([
                'total_pedido' => $totalPedido,
                'costo_total' => $costoTotal,
                'utilidad_bruta' => $utilidadBruta,
                'saldo_pendiente' => $saldo,
                'estado_pago' => $estado,
            ]);

            // Entrega opcional al registrar la venta
            $lugar = trim($data['entrega_lugar'] ?? '');
            $puntoId = $data['entrega_punto_encuentro_id'] ?? null;
            $fechaEnt = $data['entrega_fecha'] ?? $data['fecha'];
            $horaEnt = $data['entrega_hora'] ?? null;
            $telEnt = trim($data['entrega_telefono'] ?? '');
            $nombreEnt = trim($data['entrega_nombre_recibe'] ?? '');
            if ($lugar || $puntoId || $fechaEnt || $horaEnt || $telEnt || $nombreEnt) {
                if (empty($lugar) && $puntoId) {
                    $pe = PuntoEncuentro::find($puntoId);
                    $lugar = $pe?->nombre;
                    $data['entrega_direccion'] = $data['entrega_direccion'] ?: $pe?->direccion;
                }
                Entrega::create([
                    'venta_id' => $venta->id,
                    'punto_encuentro_id' => $puntoId,
                    'lugar' => $lugar ?: null,
                    'direccion' => $data['entrega_direccion'] ?? null,
                    'fecha_programada' => $fechaEnt,
                    'hora_programada' => $horaEnt,
                    'indicaciones' => $data['entrega_indicaciones'] ?? null,
                    'nombre_recibe' => $nombreEnt ?: ($data['nombre'] ?? null),
                    'telefono_contacto' => $telEnt ?: ($data['telefono'] ?? null),
                    'estado' => $data['entrega_estado'] ?? 'Pendiente de programar',
                    'historial' => json_encode([['fecha' => now()->toDateTimeString(), 'accion' => 'Creada desde venta', 'estado' => $data['entrega_estado'] ?? 'Pendiente de programar']]),
                ]);
            }
        });
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $clienta = Clienta::where('telefono', $data['telefono'])->first();
        if ($clienta) {
            $count = $clienta->ventas()->count();
            if (in_array($count, [3, 5, 10])) {
                return redirect()->route('ventas.index')->with('success', 'Venta registrada.')->with('gift', "¡La clienta {$clienta->nombre} alcanzó {$count} compras! Entregar regalo.");
            }
        }

        return redirect()->route('ventas.index')->with('success', 'Venta registrada.');
    }

    public function show(Venta $venta)
    {
        $venta->load(['clienta', 'detalles.producto']);
        return view('ventas.show', compact('venta'));
    }

    public function abono(Request $request, Venta $venta)
    {
        $data = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'nullable|string',
        ]);
        $monto = (float) $data['monto'];
        $nuevoAnticipo = (float) $venta->anticipo_pagado + $monto;
        $saldo = (float) $venta->total_pedido - $nuevoAnticipo;
        if ($saldo < 0) {
            $saldo = 0;
            $nuevoAnticipo = (float) $venta->total_pedido;
        }
        $estado = 'Pendiente';
        if ($nuevoAnticipo >= (float) $venta->total_pedido) {
            $estado = 'Liquidado';
        } elseif ($nuevoAnticipo > 0) {
            $estado = 'Parcial';
        }
        $venta->update([
            'anticipo_pagado' => $nuevoAnticipo,
            'saldo_pendiente' => $saldo,
            'estado_pago' => $estado,
        ]);
        return redirect()->route('ventas.show', $venta)->with('success', 'Abono registrado.');
    }

    public function edit(Venta $venta)
    {
        $venta->load(['clienta', 'detalles.producto']);
        $productos = \App\Models\Producto::with(['fotos', 'componentes.producto'])->orderBy('nombre')->get(['id','sku','nombre','marca','variante','precio_venta','costo_unitario','stock_actual','es_combo']);
        $puntos = \App\Models\PuntoEncuentro::where('activo', true)->orderBy('nombre')->get();
        return view('ventas.edit', compact('venta', 'productos', 'puntos'));
    }

    public function update(Request $request, Venta $venta)
    {
        $data = $request->validate([
            'telefono' => 'required|digits:10',
            'nombre' => 'required|string|max:255',
            'notas' => 'nullable|string',
            'fecha' => 'required|date',
            'metodo_pago' => 'required|string',
            'anticipo_pagado' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.costo_unitario' => 'required|numeric|min:0',
            'entrega_punto_encuentro_id' => 'nullable|exists:puntos_encuentro,id',
            'entrega_lugar' => 'nullable|string|max:255',
            'entrega_direccion' => 'nullable|string',
            'entrega_fecha' => 'nullable|date',
            'entrega_hora' => 'nullable',
            'entrega_indicaciones' => 'nullable|string',
            'entrega_nombre_recibe' => 'nullable|string|max:255',
            'entrega_telefono' => 'nullable|string|max:20',
            'entrega_estado' => 'nullable|string',
        ]);

        $anticipo = (float) ($data['anticipo_pagado'] ?? 0);

        try {
            DB::transaction(function () use ($data, $anticipo, $venta) {
                // revertir stock anterior
                foreach ($venta->detalles as $d) {
                    $p = Producto::with('componentes')->find($d->producto_id);
                    if (!$p) continue;
                    if ($p->es_combo) {
                        foreach ($p->componentes as $comp) {
                            $cp = Producto::lockForUpdate()->find($comp->producto_id);
                            if ($cp) {
                                $cp->stock_actual += $comp->cantidad * $d->cantidad;
                                $cp->save();
                            }
                        }
                    } else {
                        $p->stock_actual += $d->cantidad;
                        $p->save();
                    }
                }
                $venta->detalles()->delete();

                // clienta
                $clienta = Clienta::firstOrNew(['telefono' => $data['telefono']]);
                $clienta->nombre = $data['nombre'];
                if (!empty($data['notas'])) $clienta->notas = $data['notas'];
                $clienta->save();

                $totalPedido = 0;
                $costoTotal = 0;

                foreach ($data['items'] as $item) {
                    $producto = Producto::with('componentes.producto')->lockForUpdate()->find($item['producto_id']);
                    $cantidad = (int) $item['cantidad'];
                    $precio = (float) $item['precio_unitario'];

                    if ($producto->es_combo) {
                        $componentes = $producto->componentes;
                        if ($componentes->isEmpty()) throw new \RuntimeException("El combo {$producto->nombre} no tiene componentes.");
                        foreach ($componentes as $comp) {
                            $compProd = Producto::lockForUpdate()->find($comp->producto_id);
                            $req = $comp->cantidad * $cantidad;
                            if ($req > $compProd->stock_actual) throw new \RuntimeException("Stock insuficiente para {$compProd->nombre}.");
                        }
                        $costo = 0;
                        foreach ($componentes as $comp) {
                            $compProd = Producto::find($comp->producto_id);
                            $costo += (float) $compProd->costo_unitario * $comp->cantidad;
                        }
                    } else {
                        if ($cantidad > $producto->stock_actual) throw new \RuntimeException("Stock insuficiente para {$producto->nombre}.");
                        $costo = (float) $item['costo_unitario'];
                    }

                    $subtotal = $precio * $cantidad;
                    $utilidadLinea = ($precio - $costo) * $cantidad;

                    DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $producto->id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precio,
                        'costo_unitario' => $costo,
                        'subtotal' => $subtotal,
                        'utilidad_linea' => $utilidadLinea,
                    ]);

                    if ($producto->es_combo) {
                        foreach ($producto->componentes as $comp) {
                            $compProd = Producto::lockForUpdate()->find($comp->producto_id);
                            $compProd->stock_actual -= $comp->cantidad * $cantidad;
                            $compProd->save();
                        }
                    } else {
                        $producto->stock_actual -= $cantidad;
                        $producto->save();
                    }

                    $totalPedido += $subtotal;
                    $costoTotal += $costo * $cantidad;
                }

                $utilidadBruta = $totalPedido - $costoTotal;
                $saldo = $totalPedido - $anticipo;
                if ($saldo < 0) $saldo = 0;
                $estado = 'Pendiente';
                if ($anticipo >= $totalPedido && $totalPedido > 0) $estado = 'Liquidado';
                elseif ($anticipo > 0) $estado = 'Parcial';

                $venta->update([
                    'clienta_id' => $clienta->id,
                    'fecha' => $data['fecha'],
                    'metodo_pago' => $data['metodo_pago'],
                    'anticipo_pagado' => $anticipo,
                    'total_pedido' => $totalPedido,
                    'costo_total' => $costoTotal,
                    'utilidad_bruta' => $utilidadBruta,
                    'saldo_pendiente' => $saldo,
                    'estado_pago' => $estado,
                    'notas' => $data['notas'] ?? $venta->notas,
                ]);

                // entrega opcional
                $lugar = trim($data['entrega_lugar'] ?? '');
                $puntoId = $data['entrega_punto_encuentro_id'] ?? null;
                if ($lugar || $puntoId || !empty($data['entrega_fecha']) || !empty($data['entrega_hora'])) {
                    if (empty($lugar) && $puntoId) {
                        $pe = PuntoEncuentro::find($puntoId);
                        $lugar = $pe?->nombre;
                        $data['entrega_direccion'] = $data['entrega_direccion'] ?: $pe?->direccion;
                    }
                    \App\Models\Entrega::updateOrCreate(['venta_id' => $venta->id], [
                        'punto_encuentro_id' => $puntoId,
                        'lugar' => $lugar ?: null,
                        'direccion' => $data['entrega_direccion'] ?? null,
                        'fecha_programada' => $data['entrega_fecha'] ?? $data['fecha'],
                        'hora_programada' => $data['entrega_hora'] ?? null,
                        'indicaciones' => $data['entrega_indicaciones'] ?? null,
                        'nombre_recibe' => trim($data['entrega_nombre_recibe'] ?? '') ?: $data['nombre'],
                        'telefono_contacto' => trim($data['entrega_telefono'] ?? '') ?: $data['telefono'],
                        'estado' => $data['entrega_estado'] ?? 'Pendiente de programar',
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('ventas.show', $venta)->with('success', 'Venta actualizada.');
    }

    public function destroy(Venta $venta)
    {
        // revert stock? Not required. But restore stock for correctness.
        DB::transaction(function () use ($venta) {
            foreach ($venta->detalles as $d) {
                $p = Producto::with('componentes')->find($d->producto_id);
                if (!$p) continue;
                if ($p->es_combo) {
                    foreach ($p->componentes as $comp) {
                        $cp = Producto::find($comp->producto_id);
                        if ($cp) {
                            $cp->stock_actual += $comp->cantidad * $d->cantidad;
                            $cp->save();
                        }
                    }
                } else {
                    $p->stock_actual += $d->cantidad;
                    $p->save();
                }
            }
            $venta->delete();
        });
        return redirect()->route('ventas.index')->with('success', 'Venta eliminada.');
    }
}