@extends('layouts.app')
@section('title', 'Entradas de Inventario')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Entradas de Inventario</h2>
    <a href="{{ route('entradas.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-pink-700">+ Nueva Entrada</a>
</div>
<form method="GET" class="mb-4 flex gap-2">
    <input name="q" value="{{ $q }}" placeholder="Buscar..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full max-w-md">
    <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Buscar</button>
</form>
<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left px-4 py-3">Fecha</th>
                <th class="text-left px-4 py-3">Producto</th>
                <th class="text-right px-4 py-3">Cantidad</th>
                <th class="text-right px-4 py-3">Costo unit.</th>
                <th class="text-right px-4 py-3">Total compra</th>
                <th class="text-left px-4 py-3">Proveedor</th>
                <th class="text-left px-4 py-3">Referencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entradas as $e)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3">{{ $e->fecha ? $e->fecha->format('d/m/Y') : '-' }}</td>
                    <td class="px-4 py-3">{{ $e->producto->sku ?? '' }} - {{ $e->producto->nombre ?? '' }}</td>
                    <td class="px-4 py-3 text-right">{{ $e->cantidad }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($e->costo_unitario,2) }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($e->total_compra,2) }}</td>
                    <td class="px-4 py-3">{{ $e->proveedor }}</td>
                    <td class="px-4 py-3">{{ $e->referencia }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Sin entradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $entradas->links() }}</div>
@endsection