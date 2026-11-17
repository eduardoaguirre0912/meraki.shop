@extends('layouts.app')
@section('title', 'Nuevo Gasto')
@section('content')
<h2 class="text-2xl font-bold mb-4">Nuevo Gasto</h2>
<form method="POST" action="{{ route('gastos.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl space-y-4">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Fecha</label>
            <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Categoría</label>
            <select name="categoria_gasto_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <option value="">Sin categoría</option>
                @foreach(\App\Models\GastoCategoria::where('activo',true)->orderBy('nombre')->get() as $c)
                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Descripción</label>
            <textarea name="descripcion" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Comprobante (opcional)</label>
            <input type="file" name="comprobante" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Concepto</label>
            <input name="concepto" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Monto</label>
            <input type="number" step="0.01" name="monto" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Método de pago</label>
            <select name="metodo_pago" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @foreach(['Efectivo','Transferencia','Tarjeta','Depósito','Mixto'] as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Línea de negocio</label>
            <select name="linea_negocio" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @foreach(['Maquillaje','Uniformes','Generales'] as $l)
                    <option value="{{ $l }}">{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Estado</label>
            <select name="estado" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @foreach(['Pagado','Pendiente'] as $e)
                    <option value="{{ $e }}">{{ $e }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Notas</label>
        <textarea name="notas" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
    </div>
    <div class="flex gap-2">
        <button class="bg-pink-600 text-white px-4 py-2 rounded-lg">Guardar</button>
        <a href="{{ route('gastos.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Cancelar</a>
    </div>
</form>
@endsection