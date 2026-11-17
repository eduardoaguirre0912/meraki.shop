@extends('layouts.app')
@section('title', 'Nuevo Retiro')
@section('content')
<h2 class="text-2xl font-bold mb-4">Nuevo Retiro</h2>
<form method="POST" action="{{ route('retiros.store') }}" class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium mb-1">Fecha</label>
        <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Socio</label>
        <select name="socio" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            <option value="Edu">Edu</option>
            <option value="Dian">Dian</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Monto</label>
        <input type="number" step="0.01" name="monto" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Notas</label>
        <textarea name="notas" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
    </div>
    <div class="flex gap-2">
        <button class="bg-pink-600 text-white px-4 py-2 rounded-lg">Guardar</button>
        <a href="{{ route('retiros.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Cancelar</a>
    </div>
</form>
@endsection