@extends('layouts.app')
@section('title', 'Gastos')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Gastos</h2>
    <div class="flex gap-2">
        <a href="{{ route('gasto-categorias.index') }}" class="border border-gray-300 px-4 py-2 rounded-lg text-sm hover:bg-gray-50">Categorías</a>
        <a href="{{ route('gastos.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-pink-700">+ Nuevo Gasto</a>
    </div>
</div>
<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <input name="q" value="{{ $q }}" placeholder="Buscar..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
    <select name="linea" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <option value="">Todas</option>
        @foreach(['Maquillaje','Uniformes','Generales'] as $l)
            <option value="{{ $l }}" {{ $linea===$l?'selected':'' }}>{{ $l }}</option>
        @endforeach
    </select>
    <select name="estado" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        <option value="">Todos</option>
        @foreach(['Pagado','Pendiente'] as $e)
            <option value="{{ $e }}" {{ $estado===$e?'selected':'' }}>{{ $e }}</option>
        @endforeach
    </select>
    <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Filtrar</button>
</form>
<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left px-4 py-3">Fecha</th>
                <th class="text-left px-4 py-3">Tipo</th>
                <th class="text-left px-4 py-3">Concepto</th>
                <th class="text-left px-4 py-3">Descripción</th>
                <th class="text-right px-4 py-3">Monto</th>
                <th class="text-left px-4 py-3">Método</th>
                <th class="text-left px-4 py-3">Línea</th>
                <th class="text-left px-4 py-3">Estado</th>
                <th class="text-right px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($gastos as $g)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3">{{ $g->fecha ? $g->fecha->format('d/m/Y') : '-' }}</td>
                    <td class="px-4 py-3">{{ $g->tipo_gasto }}</td>
                    <td class="px-4 py-3">{{ $g->concepto }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $g->descripcion }}</td>
                    <td class="px-4 py-3 text-right">${{ number_format($g->monto,2) }}</td>
                    <td class="px-4 py-3">{{ $g->metodo_pago }}</td>
                    <td class="px-4 py-3">{{ $g->linea_negocio }}</td>
                    <td class="px-4 py-3">{{ $g->estado }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('gastos.edit', $g) }}" class="text-blue-600 hover:underline">Editar</a>
                        <form action="{{ route('gastos.destroy', $g) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-4 py-6 text-center text-gray-500">Sin gastos.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $gastos->links() }}</div>
@endsection