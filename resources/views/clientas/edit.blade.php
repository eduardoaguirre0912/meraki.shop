@extends('layouts.app')
@section('title', 'Editar Clienta')
@section('content')
<h2 class="text-2xl font-bold mb-4">Editar Clienta</h2>
<form method="POST" action="{{ route('clientas.update', $clienta) }}" class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg space-y-4">
    @csrf @method('PUT')
    <div>
        <label class="block text-sm font-medium mb-1">Teléfono</label>
        <input name="telefono" value="{{ old('telefono', $clienta->telefono) }}" required maxlength="10" class="w-full border border-gray-300 rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Nombre</label>
        <input name="nombre" value="{{ old('nombre', $clienta->nombre) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Notas</label>
        <textarea name="notas" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('notas', $clienta->notas) }}</textarea>
    </div>
    <div class="flex gap-2">
        <button class="bg-pink-600 text-white px-4 py-2 rounded-lg">Actualizar</button>
        <a href="{{ route('clientas.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Cancelar</a>
    </div>
</form>
@endsection