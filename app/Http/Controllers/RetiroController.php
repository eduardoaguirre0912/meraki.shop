<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\Retiro;
use App\Models\Venta;
use Illuminate\Http\Request;

class RetiroController extends Controller
{
    public function index()
    {
        $retiros = Retiro::orderByDesc('fecha')->orderByDesc('id')->get();
        $lineas = ['Maquillaje', 'Uniformes', 'Accesorios', 'Otros'];
        $porLinea = [];
        $totalGanancias = 0;
        foreach ($lineas as $ln) {
            $rev = 0; $cost = 0;
            foreach (\App\Models\DetalleVenta::with(['producto', 'venta'])->get() as $d) {
                if (($d->producto->linea_negocio ?? '') !== $ln) continue;
                // Solo ventas ya pagadas (liquidadas)
                if (($d->venta->estado_pago ?? '') !== 'Liquidado') continue;
                $rev += (float) $d->subtotal;
                $cost += (float) $d->costo_unitario * (int) $d->cantidad;
            }
            $gastosLinea = (float) \App\Models\Gasto::where('linea_negocio', $ln)->sum('monto');
            $gastosOpLinea = (float) \App\Models\Gasto::where('linea_negocio', $ln)
                ->where('tipo_gasto', 'not like', 'Compra de inventario%')->sum('monto');

            if ($ln === 'Maquillaje') {
                // Opción B: ventas totales - todos los gastos
                $ganancia = $rev - $gastosLinea;
            } else {
                // Opción A: costo por venta - gastos operativos
                $ganancia = ($rev - $cost) - $gastosOpLinea;
            }
            $porLinea[$ln] = [
                'revenue' => $rev,
                'cost' => $cost,
                'gastos' => $gastosLinea,
                'gastosOp' => $gastosOpLinea,
                'ganancia' => $ganancia,
            ];
            $totalGanancias += $ganancia;
        }

        $gastosGenerales = (float) \App\Models\Gasto::where('linea_negocio', 'Generales')->sum('monto');
        // Se restan los gastos generales (incluye chamarras y pasajes) de la ganancia total
        $totalGanancias -= $gastosGenerales;

        $totalRetiros = (float) $retiros->sum('monto');
        $restante = $totalGanancias - $totalRetiros;

        $retirosEdu = (float) $retiros->where('socio', 'Edu')->sum('monto');
        $retirosDian = (float) $retiros->where('socio', 'Dian')->sum('monto');
        $saldoEdu = ($totalGanancias / 2) - $retirosEdu;
        $saldoDian = ($totalGanancias / 2) - $retirosDian;

        return view('retiros.index', compact(
            'retiros', 'totalGanancias', 'totalRetiros', 'restante',
            'retirosEdu', 'retirosDian', 'saldoEdu', 'saldoDian',
            'porLinea', 'gastosGenerales'
        ));
    }

    public function create()
    {
        return view('retiros.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha' => 'required|date',
            'socio' => 'required|in:Edu,Dian',
            'monto' => 'required|numeric|min:0.01',
            'notas' => 'nullable|string',
        ]);
        Retiro::create($data);
        return redirect()->route('retiros.index')->with('success', 'Retiro registrado.');
    }

    public function destroy(Retiro $retiro)
    {
        $retiro->delete();
        return redirect()->route('retiros.index')->with('success', 'Retiro eliminado.');
    }
}