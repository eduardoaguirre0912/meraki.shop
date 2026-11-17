@extends('layouts.app')
@section('title', 'Nuevo Punto de Encuentro')
@section('content')
<h2 class="text-2xl font-bold mb-4">Nuevo Punto de Encuentro</h2>
<form method="POST" action="{{ route('puntos.store') }}" class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium mb-1">Nombre</label>
        <input name="nombre" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Dirección</label>
        <textarea name="direccion" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Referencias</label>
        <textarea name="referencias" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Observaciones</label>
        <textarea name="observaciones" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
    </div>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="activo" value="1" checked id="activo">
        <label for="activo" class="text-sm">Activo</label>
    </div>
    <div class="flex gap-2">
        <button class="bg-pink-600 text-white px-4 py-2 rounded-lg">Guardar</button>
        <a href="{{ route('puntos.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Cancelar</a>
    </div>
</form>
@endsection