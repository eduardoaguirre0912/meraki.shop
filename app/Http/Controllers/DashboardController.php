<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Entrega;
use App\Models\Gasto;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $linea = $request->get('linea', 'Todas');
        $lineas = ['Todas', 'Maquillaje', 'Uniformes', 'Accesorios', 'Otros'];

        // Ventas filtradas por línea (ventas que contienen productos de esa línea)
        $ventasQuery = Venta::query();
        if ($linea !== 'Todas') {
            $ventasQuery->whereHas('detalles.producto', fn ($q) => $q->where('linea_negocio', $linea));
        }

        $ventasTotales = (clone $ventasQuery)->sum('total_pedido');
        $costoMercancia = (clone $ventasQuery)->sum('costo_total');
        $gananciaBruta = (clone $ventasQuery)->sum('utilidad_bruta');
        $dineroCobrado = (clone $ventasQuery)->sum('anticipo_pagado');
        $saldoPendiente = (clone $ventasQuery)->sum('saldo_pendiente');

        $gastosQuery = Gasto::query();
        if ($linea !== 'Todas') {
            $gastosQuery->where('linea_negocio', $linea);
        }
        $gastosOperativos = (clone $gastosQuery)->sum('monto');

        $gananciaNeta = $gananciaBruta - $gastosOperativos;

        $prodQuery = Producto::query();
        if ($linea !== 'Todas') {
            $prodQuery->where('linea_negocio', $linea);
        }
        $valorInventario = $prodQuery->get()->sum(fn ($p) => $p->stock_actual * $p->costo_unitario);

        $alertasBajo = (clone $prodQuery)->whereColumn('stock_actual', '<=', 'stock_minimo')->where('stock_actual', '>', 0)->count();
        $alertasAgotado = (clone $prodQuery)->where('stock_actual', '<=', 0)->count();

        // Hoy
        $hoy = now()->toDateString();
        $ventasHoy = Venta::whereDate('fecha', $hoy)->sum('total_pedido');
        $ingresosHoy = Venta::whereDate('fecha', $hoy)->sum('anticipo_pagado');
        $gastosHoy = Gasto::whereDate('fecha', $hoy)->sum('monto');
        $utilidadHoy = Venta::whereDate('fecha', $hoy)->sum('utilidad_bruta') - $gastosHoy;

        $proximasEntregas = Entrega::with(['venta.clienta'])->whereDate('fecha_programada', '>=', $hoy)->orderBy('fecha_programada')->orderBy('hora_programada')->limit(5)->get();
        $productosBajoLista = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->orderBy('stock_actual')->limit(5)->get();

        // Ventas mensuales (últimos 12 meses)
        $monthly = Venta::select(
            DB::raw("DATE_FORMAT(fecha, '%Y-%m') as ym"),
            DB::raw('SUM(total_pedido) as total')
        );
        if ($linea !== 'Todas') {
            $monthly->whereHas('detalles.producto', fn ($q) => $q->where('linea_negocio', $linea));
        }
        $monthly = $monthly->where('fecha', '>=', now()->subMonths(11)->startOfMonth()->toDateString())
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        // Top productos
        $topProductos = DetalleVenta::select('producto_id', DB::raw('SUM(cantidad) as total_qty'), DB::raw('SUM(subtotal) as total_venta'))
            ->with('producto')
            ->when($linea !== 'Todas', fn ($q) => $q->whereHas('producto', fn ($p) => $p->where('linea_negocio', $linea)))
            ->groupBy('producto_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'linea', 'lineas', 'ventasTotales', 'costoMercancia', 'gananciaBruta',
            'gastosOperativos', 'gananciaNeta', 'dineroCobrado', 'saldoPendiente',
            'valorInventario', 'alertasBajo', 'alertasAgotado', 'monthly', 'topProductos',
            'ventasHoy', 'ingresosHoy', 'gastosHoy', 'utilidadHoy', 'proximasEntregas', 'productosBajoLista'
        ));
    }
}