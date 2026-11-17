@extends('layouts.app')
@section('title', 'Editar Entrega')
@section('content')
<h2 class="text-2xl font-bold mb-4">Editar Entrega</h2>
@php $puntos = \App\Models\PuntoEncuentro::where('activo', true)->orderBy('nombre')->get(); @endphp
<form method="POST" action="{{ route('entregas.update', $entrega) }}" class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl space-y-4">
    @csrf @method('PUT')
    <div>
        <label class="block text-sm font-medium mb-1">Venta (pedido)</label>
        <select name="venta_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            @foreach(\App\Models\Venta::with('clienta')->get() as $v)
                <option value="{{ $v->id }}" {{ $entrega->venta_id===$v->id?'selected':'' }}>{{ $v->folio }} - {{ $v->clienta->nombre ?? '' }}</option>
            @endforeach
        </select>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Punto de encuentro</label>
            <select name="punto_encuentro_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <option value="">Otro (especificar)</option>
                @foreach($puntos as $p)
                    <option value="{{ $p->id }}" {{ $entrega->punto_encuentro_id===$p->id?'selected':'' }}>{{ $p->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Lugar</label>
            <input name="lugar" value="{{ $entrega->lugar }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Dirección</label>
            <textarea name="direccion" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ $entrega->direccion }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Fecha programada</label>
            <input type="date" name="fecha_programada" value="{{ $entrega->fecha_programada?->format('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Hora programada</label>
            <input type="text" name="hora_programada" value="{{ $entrega->hora_programada }}" pattern="[0-9]{2}:[0-9]{2}" placeholder="HH:MM" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nombre quien recibe</label>
            <input name="nombre_recibe" value="{{ $entrega->nombre_recibe }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Teléfono contacto</label>
            <input name="telefono_contacto" value="{{ $entrega->telefono_contacto }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Estado</label>
            <select name="estado" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @foreach(\App\Models\Entrega::ESTADOS as $e)
                    <option value="{{ $e }}" {{ $entrega->estado===$e?'selected':'' }}>{{ $e }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Indicaciones</label>
        <textarea name="indicaciones" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ $entrega->indicaciones }}</textarea>
    </div>
    <div class="flex gap-2">
        <button class="bg-pink-600 text-white px-4 py-2 rounded-lg">Actualizar</button>
        <a href="{{ route('entregas.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Cancelar</a>
    </div>
</form>
@endsection