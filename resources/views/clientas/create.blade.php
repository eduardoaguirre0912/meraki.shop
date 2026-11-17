@extends('layouts.app')
@section('title', 'Nueva Clienta')
@section('content')
<h2 class="text-2xl font-bold mb-4">Nueva Clienta</h2>
<form method="POST" action="{{ route('clientas.store') }}" class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium mb-1">Teléfono (10 dígitos)</label>
        <input name="telefono" value="{{ old('telefono') }}" required maxlength="10" class="w-full border border-gray-300 rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Nombre</label>
        <input name="nombre" value="{{ old('nombre') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Notas</label>
        <textarea name="notas" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('notas') }}</textarea>
    </div>
    <div class="flex gap-2">
        <button class="bg-pink-600 text-white px-4 py-2 rounded-lg">Guardar</button>
        <a href="{{ route('clientas.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Cancelar</a>
    </div>
</form>
@endsection