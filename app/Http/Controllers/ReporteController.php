<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\EntradaInventario;
use App\Models\Entrega;
use App\Models\Gasto;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    private function csv(string $filename, array $header, iterable $rows)
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $header);
            foreach ($rows as $r) {
                fputcsv($out, $r);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function ventas(Request $request)
    {
        $rows = Venta::with('clienta')->orderBy('fecha')->get()->map(fn ($v) => [
            $v->folio, $v->clienta->nombre ?? '', $v->clienta->telefono ?? '',
            $v->fecha ? $v->fecha->format('Y-m-d') : '', $v->total_pedido, $v->costo_total,
            $v->utilidad_bruta, $v->anticipo_pagado, $v->saldo_pendiente,
            $v->metodo_pago, $v->estado_pago,
        ]);
        return $this->csv('ventas.csv', ['Folio','Clienta','Telefono','Fecha','Total','Costo','Utilidad','Anticipo','Saldo','Metodo','Estado'], $rows);
    }

    public function gastos(Request $request)
    {
        $rows = Gasto::with('categoria')->orderBy('fecha')->get()->map(fn ($g) => [
            $g->fecha ? $g->fecha->format('Y-m-d') : '', $g->tipo_gasto, $g->concepto, $g->monto,
            $g->metodo_pago, $g->linea_negocio, $g->estado, $g->categoria->nombre ?? '',
        ]);
        return $this->csv('gastos.csv', ['Fecha','Categoria','Concepto','Monto','Metodo','Linea','Estado','CategoriaConfig'], $rows);
    }

    public function inventario(Request $request)
    {
        $linea = $request->get('linea');
        $q = Producto::orderBy('nombre');
        if ($linea) {
            if ($linea === 'Otros') {
                $q->where('linea_negocio', '!=', 'Maquillaje');
            } else {
                $q->where('linea_negocio', $linea);
            }
        }
        $suffix = $linea ? '_' . strtolower(str_replace(' ', '_', $linea)) : '';
        $rows = $q->get()->map(fn ($p) => [
            $p->sku, $p->linea_negocio, $p->nombre, $p->categoria, $p->marca,
            $p->variante, $p->stock_actual, $p->stock_minimo, $p->costo_unitario,
            $p->precio_venta, $p->proveedor,
        ]);
        return $this->csv('inventario' . $suffix . '.csv', ['SKU','Linea','Nombre','Categoria','Marca','Variante','Stock','StockMin','Costo','Precio','Proveedor'], $rows);
    }

    public function movimientos()
    {
        $rows = EntradaInventario::with('producto')->orderBy('fecha')->get()->map(fn ($e) => [
            $e->fecha->format('Y-m-d'), $e->producto->sku ?? '', $e->producto->nombre ?? '',
            $e->cantidad, $e->costo_unitario, $e->total_compra, $e->proveedor, $e->referencia,
        ]);
        return $this->csv('movimientos_inventario.csv', ['Fecha','SKU','Producto','Cantidad','CostoUnit','Total','Proveedor','Referencia'], $rows);
    }

    public function cuentasPorCobrar()
    {
        $rows = Venta::with('clienta')->where('saldo_pendiente', '>', 0)->orderBy('fecha')->get()->map(fn ($v) => [
            $v->folio, $v->clienta->nombre ?? '', $v->clienta->telefono ?? '',
            $v->fecha ? $v->fecha->format('Y-m-d') : '', $v->total_pedido, $v->anticipo_pagado,
            $v->saldo_pendiente, $v->estado_pago,
        ]);
        return $this->csv('cuentas_por_cobrar.csv', ['Folio','Clienta','Telefono','Fecha','Total','Anticipo','Saldo','Estado'], $rows);
    }

    public function entregas()
    {
        $rows = Entrega::with(['venta.clienta', 'puntoEncuentro'])->orderBy('fecha_programada')->get()->map(fn ($e) => [
            $e->venta->folio ?? '', $e->venta->clienta->nombre ?? '', $e->lugar,
            $e->fecha_programada?->format('Y-m-d'), $e->hora_programada, $e->estado,
            $e->nombre_recibe, $e->telefono_contacto,
        ]);
        return $this->csv('entregas.csv', ['Folio','Clienta','Lugar','Fecha','Hora','Estado','Recibe','Telefono'], $rows);
    }

    public function utilidades()
    {
        $rows = Venta::with('clienta')->orderBy('fecha')->get()->map(fn ($v) => [
            $v->folio, $v->fecha ? $v->fecha->format('Y-m-d') : '', $v->total_pedido, $v->costo_total,
            $v->utilidad_bruta, $v->anticipo_pagado, $v->saldo_pendiente,
        ]);
        return $this->csv('utilidades.csv', ['Folio','Fecha','Ventas','Costo','UtilidadBruta','Cobrado','Saldo'], $rows);
    }
}