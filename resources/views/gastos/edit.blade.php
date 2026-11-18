@extends('layouts.app')
@section('title', 'Editar Gasto')
@section('content')
<h2 class="text-2xl font-bold mb-4">Editar Gasto</h2>
<form method="POST" action="{{ route('gastos.update', $gasto) }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl space-y-4">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Fecha</label>
            <input type="date" name="fecha" value="{{ $gasto->fecha ? $gasto->fecha->format('Y-m-d') : '' }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Categoría</label>
            <select name="categoria_gasto_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <option value="">Sin categoría</option>
                @foreach(\App\Models\GastoCategoria::where('activo',true)->orderBy('nombre')->get() as $c)
                    <option value="{{ $c->id }}" {{ $gasto->categoria_gasto_id===$c->id?'selected':'' }}>{{ $c->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Descripción</label>
            <textarea name="descripcion" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ $gasto->descripcion }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Comprobante (opcional)</label>
            @if($gasto->comprobante)
                <a href="{{ asset('storage/'.$gasto->comprobante) }}" target="_blank" class="text-blue-600 text-sm block mb-1">Ver comprobante actual</a>
            @endif
            <input type="file" name="comprobante" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Concepto</label>
            <input name="concepto" value="{{ $gasto->concepto }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Monto</label>
            <input type="number" step="0.01" name="monto" value="{{ $gasto->monto }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="es_devolucion" value="1" id="es_devolucion" {{ $gasto->monto < 0 ? 'checked' : '' }}>
            <label for="es_devolucion" class="text-sm">Es devolución/reembolso (se guarda como negativo)</label>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Método de pago</label>
            <select name="metodo_pago" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @foreach(['Efectivo','Transferencia','Tarjeta','Depósito','Mixto'] as $m)
                    <option value="{{ $m }}" {{ $gasto->metodo_pago===$m?'selected':'' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Línea de negocio</label>
            <select name="linea_negocio" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @foreach(['Maquillaje','Uniformes','Generales'] as $l)
                    <option value="{{ $l }}" {{ $gasto->linea_negocio===$l?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Estado</label>
            <select name="estado" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @foreach(['Pagado','Pendiente'] as $e)
                    <option value="{{ $e }}" {{ $gasto->estado===$e?'selected':'' }}>{{ $e }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Notas</label>
        <textarea name="notas" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ $gasto->notas }}</textarea>
    </div>
    <div class="flex gap-2">
        <button class="bg-pink-600 text-white px-4 py-2 rounded-lg">Actualizar</button>
        <a href="{{ route('gastos.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Cancelar</a>
    </div>
</form>
@endsection