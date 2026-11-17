<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\EntradaInventario;
use App\Models\Gasto;
use App\Models\GastoCategoria;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanzasController extends Controller
{
    public function index(Request $request)
    {
        $periodo = $request->get('periodo', 'month');
        $fecha = $request->get('fecha', date('Y-m-d'));
        $inicio = $request->get('fecha_inicio');
        $fin = $request->get('fecha_fin');
        $linea = $request->get('linea', 'Todas');

        $base = \Carbon\Carbon::parse($fecha);
        [$start, $end] = $this->range($periodo, $base, $inicio, $fin);

        $ventasQuery = Venta::whereBetween('fecha', [$start->toDateString(), $end->toDateString()]);
        if ($linea !== 'Todas') {
            $ventasQuery->whereHas('detalles.producto', fn ($q) => $q->where('linea_negocio', $linea));
        }

        $totalVentas = (clone $ventasQuery)->sum('total_pedido');
        $costoVendido = (clone $ventasQuery)->sum('costo_total');
        $utilidadBruta = (clone $ventasQuery)->sum('utilidad_bruta');
        $dineroCobrado = (clone $ventasQuery)->sum('anticipo_pagado');
        $saldoPendiente = (clone $ventasQuery)->sum('saldo_pendiente');

        $gastosQuery = Gasto::whereBetween('fecha', [$start->toDateString(), $end->toDateString()]);
        if ($linea !== 'Todas' && $linea !== 'Generales') {
            $gastosQuery->where(function ($q) use ($linea) {
                $q->where('linea_negocio', $linea)->orWhere('linea_negocio', 'Generales');
            });
        }
        $totalGastos = (clone $gastosQuery)->sum('monto');
        $gastosPagados = (clone $gastosQuery)->where('estado', 'Pagado')->sum('monto');
        $gastosPendientes = (clone $gastosQuery)->where('estado', 'Pendiente')->sum('monto');

        $utilidadNeta = $utilidadBruta - $totalGastos;
        $dineroDisponible = $dineroCobrado - $gastosPagados;

        $compras = EntradaInventario::whereBetween('fecha', [$start->toDateString(), $end->toDateString()]);
        if ($linea !== 'Todas') {
            $compras->whereHas('producto', fn ($q) => $q->where('linea_negocio', $linea));
        }
        $totalCompras = $compras->sum('total_compra');

        $cuentasPorCobrar = Venta::sum('saldo_pendiente');

        // charts data
        $monthly = Venta::select(DB::raw("DATE_FORMAT(fecha, '%Y-%m') as ym"), DB::raw('SUM(total_pedido) as ventas'), DB::raw('SUM(utilidad_bruta) as utilidad'))
            ->where('fecha', '>=', now()->subMonths(11)->startOfMonth()->toDateString())
            ->groupBy('ym')->orderBy('ym')->get();

        $gastosMes = Gasto::select(DB::raw("DATE_FORMAT(fecha, '%Y-%m') as ym"), DB::raw('SUM(monto) as total'))
            ->where('fecha', '>=', now()->subMonths(11)->startOfMonth()->toDateString())
            ->groupBy('ym')->orderBy('ym')->get();

        $gastosCat = Gasto::select('tipo_gasto', DB::raw('SUM(monto) as total'))
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
            ->groupBy('tipo_gasto')->get();

        $ventasLinea = Venta::select('productos.linea_negocio', DB::raw('SUM(detalle_ventas.subtotal) as total'))
            ->join('detalle_ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->join('productos', 'productos.id', '=', 'detalle_ventas.producto_id')
            ->whereBetween('ventas.fecha', [$start->toDateString(), $end->toDateString()])
            ->groupBy('productos.linea_negocio')->get();

        $topRentables = DetalleVenta::select('producto_id', DB::raw('SUM(utilidad_linea) as utilidad'))
            ->with('producto')
            ->whereHas('venta', fn ($q) => $q->whereBetween('fecha', [$start->toDateString(), $end->toDateString()]))
            ->groupBy('producto_id')->orderByDesc('utilidad')->limit(10)->get();

        $categorias = GastoCategoria::where('activo', true)->orderBy('nombre')->get();

        return view('finanzas.index', compact(
            'periodo', 'fecha', 'inicio', 'fin', 'linea', 'start', 'end',
            'totalVentas', 'costoVendido', 'utilidadBruta', 'dineroCobrado',
            'saldoPendiente', 'totalGastos', 'gastosPagados', 'gastosPendientes',
            'utilidadNeta', 'dineroDisponible', 'totalCompras', 'cuentasPorCobrar',
            'monthly', 'gastosMes', 'gastosCat', 'ventasLinea', 'topRentables', 'categorias'
        ));
    }

    private function range(string $periodo, \Carbon\Carbon $base, ?string $inicio, ?string $fin): array
    {
        return match ($periodo) {
            'day' => [$base->copy()->startOfDay(), $base->copy()->endOfDay()],
            'week' => [$base->copy()->startOfWeek(), $base->copy()->endOfWeek()],
            'year' => [$base->copy()->startOfYear(), $base->copy()->endOfYear()],
            'custom' => [\Carbon\Carbon::parse($inicio ?: date('Y-m-d'))->startOfDay(), \Carbon\Carbon::parse($fin ?: date('Y-m-d'))->endOfDay()],
            default => [$base->copy()->startOfMonth(), $base->copy()->endOfMonth()],
        };
    }
}