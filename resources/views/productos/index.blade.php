@extends('layouts.app')
@section('title', 'Productos')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Productos</h2>
    <div class="flex gap-2">
        <a href="{{ route('productos.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-pink-700">+ Nuevo Producto</a>
        <a href="{{ route('productos.create', ['combo' => 1]) }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700">+ Combo</a>
    </div>
</div>
<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="q" value="{{ $q }}" placeholder="Buscar..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
    <select name="linea" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <option value="">Todas las líneas</option>
        @foreach(['Maquillaje','Uniformes','Accesorios','Otros'] as $l)
            <option value="{{ $l }}" {{ $linea===$l?'selected':'' }}>{{ $l }}</option>
        @endforeach
    </select>
    <select name="estado" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <option value="">Todo stock</option>
        <option value="bajo" {{ $estado==='bajo'?'selected':'' }}>Stock bajo</option>
        <option value="agotado" {{ $estado==='agotado'?'selected':'' }}>Agotado</option>
    </select>
    <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Filtrar</button>
</form>
<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left px-4 py-3">Foto</th>
                <th class="text-left px-4 py-3">SKU</th>
                <th class="text-left px-4 py-3">Nombre</th>
                <th class="text-left px-4 py-3">Línea</th>
                <th class="text-left px-4 py-3">Categoría</th>
                <th class="text-left px-4 py-3">Variante</th>
                <th class="text-right px-4 py-3">Stock</th>
                <th class="text-right px-4 py-3">Costo</th>
                <th class="text-right px-4 py-3">Precio</th>
                <th class="text-right px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $p)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3">
                        @if($p->fotoPrincipal())
                            <img src="{{ asset('storage/'.$p->fotoPrincipal()) }}" class="w-16 h-16 object-cover rounded">
                        @else
                            <div class="w-16 h-16 bg-gray-100 rounded"></div>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $p->sku }} @if($p->es_combo)<span class="ml-1 px-1.5 py-0.5 bg-purple-100 text-purple-700 text-[10px] rounded">COMBO</span>@endif</td>
                    <td class="px-4 py-3 font-medium">{{ $p->nombre }} <span class="text-gray-400 text-xs">{{ $p->marca }}</span></td>
                    <td class="px-4 py-3">{{ $p->linea_negocio }}</td>
                    <td class="px-4 py-3">{{ $p->categoria }}</td>
                    <td class="px-4 py-3 text-xs">
                        @if($p->es_combo)
                            @foreach($p->componentes as $c)
                                {{ $c->producto->nombre ?? '' }}{{ $c->producto?->variante ? ' ('.$c->producto->variante.')' : '' }} x{{ $c->cantidad }}@if(!$loop->last), @endif
                            @endforeach
                        @else
                            {{ $p->variante }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @php
                            if ($p->es_combo) {
                                $compStocks = $p->componentes->map(fn($c) => $c->producto ? (int) floor($c->producto->stock_actual / max(1, $c->cantidad)) : 0);
                                $stockShow = $compStocks->isEmpty() ? 0 : $compStocks->min();
                            } else {
                                $stockShow = $p->stock_actual;
                            }
                        @endphp
                        @if($stockShow <= 0)
                            <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">{{ $stockShow }}</span>
                        @elseif($stockShow <= $p->stock_minimo)
                            <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold">{{ $stockShow }}</span>
                        @else
                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">{{ $stockShow }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">${{ number_format($p->costo_unitario,2) }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($p->precio_venta,2) }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('productos.edit', $p) }}" class="text-blue-600 hover:underline">Editar</a>
                        <form action="{{ route('productos.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-4 py-6 text-center text-gray-500">Sin productos.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $productos->links() }}</div>
@endsection