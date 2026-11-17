@extends('layouts.app')
@section('title', 'Clientas')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Clientas</h2>
    <a href="{{ route('clientas.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-pink-700">+ Nueva Clienta</a>
</div>
<form method="GET" class="mb-4 flex gap-2">
    <input name="q" value="{{ $q }}" placeholder="Buscar por nombre o teléfono..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full max-w-md">
    <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Buscar</button>
</form>
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left px-4 py-3">Teléfono</th>
                <th class="text-left px-4 py-3">Nombre</th>
                <th class="text-left px-4 py-3">Compras</th>
                <th class="text-left px-4 py-3">Notas</th>
                <th class="text-right px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientas as $c)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3">{{ $c->telefono }}</td>
                    <td class="px-4 py-3 font-medium">{{ $c->nombre }}</td>
                    <td class="px-4 py-3">
                        {{ $c->ventas_count ?? 0 }}
                        @if(in_array($c->ventas_count ?? 0, [3,5,10]))
                            <span class="ml-2 px-2 py-0.5 bg-pink-100 text-pink-700 text-xs rounded-full">🎁 Regalo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $c->notas }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('clientas.edit', $c) }}" class="text-blue-600 hover:underline">Editar</a>
                        <form action="{{ route('clientas.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Sin clientas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $clientas->links() }}</div>
@endsection