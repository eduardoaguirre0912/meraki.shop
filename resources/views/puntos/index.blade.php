@extends('layouts.app')
@section('title', 'Puntos de Encuentro')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Puntos de Encuentro</h2>
    <a href="{{ route('puntos.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-pink-700">+ Nuevo Punto</a>
</div>
<form method="GET" class="mb-4 flex gap-2">
    <input name="q" value="{{ $q }}" placeholder="Buscar..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full max-w-md">
    <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Buscar</button>
</form>
<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left px-4 py-3">Nombre</th>
                <th class="text-left px-4 py-3">Dirección</th>
                <th class="text-left px-4 py-3">Referencias</th>
                <th class="text-left px-4 py-3">Activo</th>
                <th class="text-right px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($puntos as $p)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3 font-medium">{{ $p->nombre }}</td>
                    <td class="px-4 py-3">{{ $p->direccion }}</td>
                    <td class="px-4 py-3">{{ $p->referencias }}</td>
                    <td class="px-4 py-3">{{ $p->activo ? 'Sí' : 'No' }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('puntos.edit', $p) }}" class="text-blue-600 hover:underline">Editar</a>
                        <form action="{{ route('puntos.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Sin puntos.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $puntos->links() }}</div>
@endsection