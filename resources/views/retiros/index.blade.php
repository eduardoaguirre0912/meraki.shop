@extends('layouts.app')
@section('title', 'Retiros y Ganancias')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Retiros y Ganancias (Edu y Dian)</h2>
    <a href="{{ route('retiros.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-pink-700">+ Nuevo Retiro</a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Ganancias totales (utilidad neta, sin doble conteo de compras de inventario)</p>
        <p class="text-lg font-bold">${{ number_format($totalGanancias, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Total retirado</p>
        <p class="text-lg font-bold">${{ number_format($totalRetiros, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Restante (a mitades)</p>
        <p class="text-lg font-bold">${{ number_format($restante, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Restante por persona (Edu / Dian)</p>
        <p class="text-lg font-bold">${{ number_format($saldoEdu, 2) }} / ${{ number_format($saldoDian, 2) }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-4 mb-6 overflow-x-auto">
    <h3 class="font-semibold mb-3">Ganancias por línea de negocio (híbrido)</h3>
    <p class="text-xs text-gray-500 mb-2">Maquillaje: ventas totales − todos los gastos. Quirúrgicos/Accesorios/Otros: utilidad por venta − gastos operativos.</p>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left px-4 py-2">Línea</th>
                <th class="text-right px-4 py-2">Ventas</th>
                <th class="text-right px-4 py-2">Costo</th>
                <th class="text-right px-4 py-2">Gastos</th>
                <th class="text-right px-4 py-2">Ganancia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($porLinea as $ln => $d)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-2">{{ $ln }}</td>
                    <td class="px-4 py-2 text-right">${{ number_format($d['revenue'], 2) }}</td>
                    <td class="px-4 py-2 text-right">${{ number_format($d['cost'], 2) }}</td>
                    <td class="px-4 py-2 text-right">${{ number_format($d['gastos'], 2) }}</td>
                    <td class="px-4 py-2 text-right font-semibold">${{ number_format($d['ganancia'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="border-t border-gray-200 font-bold">
                <td class="px-4 py-2">Total</td>
                <td colspan="3"></td>
                <td class="px-4 py-2 text-right">${{ number_format($totalGanancias, 2) }}</td>
            </tr>
        </tbody>
    </table>
    <p class="text-xs text-gray-500 mt-2">Gastos generales (no asignados a línea): ${{ number_format($gastosGenerales, 2) }}</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-sm">Retiros Edu: ${{ number_format($retirosEdu, 2) }}</p>
        <p class="text-sm">Retiros Dian: ${{ number_format($retirosDian, 2) }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto mb-6">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left px-4 py-3">Fecha</th>
                <th class="text-left px-4 py-3">Socio</th>
                <th class="text-right px-4 py-3">Monto</th>
                <th class="text-left px-4 py-3">Notas</th>
                <th class="text-right px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($retiros as $r)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3">{{ $r->fecha->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">{{ $r->socio }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($r->monto, 2) }}</td>
                    <td class="px-4 py-3">{{ $r->notas }}</td>
                    <td class="px-4 py-3 text-right">
                        <form action="{{ route('retiros.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar retiro?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Sin retiros.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@php $devoluciones = \App\Models\Gasto::where('tipo_gasto', 'Devolución/Reembolso')->orderByDesc('fecha')->get(); @endphp
<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto mt-6">
    <div class="p-4 border-b border-gray-200">
        <h3 class="font-semibold">Devoluciones a ganancias (gastos negativos)</h3>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left px-4 py-3">Fecha</th>
                <th class="text-left px-4 py-3">Concepto</th>
                <th class="text-right px-4 py-3">Monto</th>
                <th class="text-left px-4 py-3">Notas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devoluciones as $d)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3">{{ $d->fecha ? $d->fecha->format('d/m/Y') : '-' }}</td>
                    <td class="px-4 py-3">{{ $d->concepto }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($d->monto, 2) }}</td>
                    <td class="px-4 py-3">{{ $d->notas }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin devoluciones.</td></tr>
            @endforelse
            <tr class="border-t border-gray-200 font-bold">
                <td colspan="2" class="px-4 py-3">Total devoluciones</td>
                <td class="px-4 py-3 text-right">${{ number_format($devoluciones->sum('monto'), 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection