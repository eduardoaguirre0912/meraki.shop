@extends('layouts.app')
@section('title', 'Categorías de Gastos')
@section('content')
<h2 class="text-2xl font-bold mb-4">Categorías de Gastos</h2>
<form method="POST" action="{{ route('gasto-categorias.store') }}" class="mb-4 flex gap-2">
    @csrf
    <input name="nombre" placeholder="Nueva categoría" required class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-full max-w-md">
    <button class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm">Agregar</button>
</form>
<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left px-4 py-3">Nombre</th>
                <th class="text-left px-4 py-3">Activa</th>
                <th class="text-right px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categorias as $c)
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('gasto-categorias.update', $c) }}" class="flex gap-2">
                            @csrf @method('PUT')
                            <input name="nombre" value="{{ $c->nombre }}" class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <button class="text-blue-600 text-sm">Guardar</button>
                        </form>
                    </td>
                    <td class="px-4 py-3">{{ $c->activo ? 'Sí' : 'No' }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('gasto-categorias.toggle', $c) }}" class="inline">
                            @csrf
                            <button class="text-gray-600 hover:underline text-sm">{{ $c->activo ? 'Desactivar' : 'Activar' }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection