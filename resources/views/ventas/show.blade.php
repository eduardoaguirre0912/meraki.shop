@extends('layouts.app')
@section('title', 'Venta ' . $venta->folio)
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Venta {{ $venta->folio }}</h2>
    <a href="{{ route('ventas.edit', $venta) }}" class="text-sm text-blue-600 hover:underline mr-3">Editar</a>
    <a href="{{ route('ventas.index') }}" class="text-sm text-gray-600 hover:underline">← Volver</a>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-1 text-sm">
        <p><span class="font-medium">Clienta:</span> {{ $venta->clienta->nombre }}</p>
        <p><span class="font-medium">Teléfono:</span> {{ $venta->clienta->telefono }}</p>
        <p><span class="font-medium">Fecha:</span> {{ $venta->fecha ? $venta->fecha->format('d/m/Y') : '-' }}</p>
        <p><span class="font-medium">Método de pago:</span> {{ $venta->metodo_pago }}</p>
        <p><span class="font-medium">Notas:</span> {{ $venta->notas }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-1 text-sm">
        <p><span class="font-medium">Total pedido:</span> ${{ number_format($venta->total_pedido,2) }}</p>
        <p><span class="font-medium">Costo total:</span> ${{ number_format($venta->costo_total,2) }}</p>
        <p><span class="font-medium">Utilidad bruta:</span> ${{ number_format($venta->utilidad_bruta,2) }}</p>
        <p><span class="font-medium">Anticipo pagado:</span> ${{ number_format($venta->anticipo_pagado,2) }}</p>
        <p><span class="font-medium">Saldo pendiente:</span> ${{ number_format($venta->saldo_pendiente,2) }}</p>
        <p><span class="font-medium">Estado:</span> {{ $venta->estado_pago }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto mb-6">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left px-4 py-3">Producto</th>
                <th class="text-right px-4 py-3">Cantidad</th>
                <th class="text-right px-4 py-3">Precio unit.</th>
                <th class="text-right px-4 py-3">Costo unit.</th>
                <th class="text-right px-4 py-3">Subtotal</th>
                <th class="text-right px-4 py-3">Utilidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $d)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3">{{ $d->producto->sku ?? '' }} - {{ $d->producto->nombre ?? '' }}</td>
                    <td class="px-4 py-3 text-right">{{ $d->cantidad }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($d->precio_unitario,2) }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($d->costo_unitario,2) }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($d->subtotal,2) }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($d->utilidad_linea,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($venta->saldo_pendiente > 0)
<div class="bg-white rounded-xl border border-gray-200 p-4 max-w-md">
    <h3 class="font-semibold mb-3">Registrar Abono</h3>
    <form method="POST" action="{{ route('ventas.abono', $venta) }}" class="space-y-3">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Monto</label>
            <input type="number" step="0.01" name="monto" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Método</label>
            <select name="metodo_pago" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @foreach(['Efectivo','Transferencia','Tarjeta','Depósito','Mixto'] as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <button class="bg-green-600 text-white px-4 py-2 rounded-lg">Registrar Abono</button>
    </form>
</div>
@endif
@endsection