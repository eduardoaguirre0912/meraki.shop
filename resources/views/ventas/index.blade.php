@extends('layouts.app')
@section('title', 'Ventas')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Ventas</h2>
    <a href="{{ route('ventas.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-pink-700">+ Nueva Venta</a>
</div>
<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="q" value="{{ $q }}" placeholder="Buscar folio/clienta..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
    <select name="estado" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <option value="">Todos</option>
        @foreach(['Liquidado','Parcial','Pendiente'] as $e)
            <option value="{{ $e }}" {{ $estado===$e?'selected':'' }}>{{ $e }}</option>
        @endforeach
    </select>
    <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Filtrar</button>
</form>
<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left px-4 py-3">Folio</th>
                <th class="text-left px-4 py-3">Clienta</th>
                <th class="text-left px-4 py-3">Productos</th>
                <th class="text-left px-4 py-3">Fecha</th>
                <th class="text-right px-4 py-3">Total</th>
                <th class="text-right px-4 py-3">Anticipo</th>
                <th class="text-right px-4 py-3">Saldo</th>
                <th class="text-left px-4 py-3">Estado</th>
                <th class="text-right px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $v)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-mono text-xs">{{ $v->folio }}</td>
                    <td class="px-4 py-3">{{ $v->clienta->nombre ?? '' }}<br><span class="text-xs text-gray-400">{{ $v->clienta->telefono ?? '' }}</span></td>
                    <td class="px-4 py-3 text-xs">{{ $v->detalles->map(fn($d)=> ($d->producto->nombre ?? 'Producto no identificado') . ($d->producto?->variante ? ' ('.$d->producto->variante.')' : '') . ' x'.$d->cantidad)->implode(', ') }}</td>
                    <td class="px-4 py-3">{{ $v->fecha ? $v->fecha->format('d/m/Y') : '-' }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($v->total_pedido,2) }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($v->anticipo_pagado,2) }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($v->saldo_pendiente,2) }}</td>
                    <td class="px-4 py-3">
                        @php
                            $badge = match($v->estado_pago){
                                'Liquidado' => 'bg-green-100 text-green-700',
                                'Parcial' => 'bg-yellow-100 text-yellow-700',
                                default => 'bg-red-100 text-red-700',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded-full {{ $badge }} text-xs font-semibold">{{ $v->estado_pago }}</span>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('ventas.show', $v) }}" class="text-blue-600 hover:underline">Ver</a>
                        <a href="{{ route('ventas.edit', $v) }}" class="text-gray-600 hover:underline">Editar</a>
                        <form action="{{ route('ventas.destroy', $v) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar venta y revertir stock?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-4 py-6 text-center text-gray-500">Sin ventas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $ventas->links() }}</div>
@endsection