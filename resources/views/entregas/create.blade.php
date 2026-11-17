@extends('layouts.app')
@section('title', 'Nueva Entrega')
@section('content')
<h2 class="text-2xl font-bold mb-4">Nueva Entrega</h2>
@php $ventas = \App\Models\Venta::with('clienta')->whereDoesntHave('entrega')->orderByDesc('id')->get(); @endphp
@php $puntos = \App\Models\PuntoEncuentro::where('activo', true)->orderBy('nombre')->get(); @endphp
<form method="POST" action="{{ route('entregas.store') }}" class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium mb-1">Venta (pedido)</label>
        <select name="venta_id" id="venta_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2" onchange="prefill()">
            <option value="">Seleccionar...</option>
            @foreach($ventas as $v)
                <option value="{{ $v->id }}" data-nombre="{{ $v->clienta->nombre ?? '' }}" data-tel="{{ $v->clienta->telefono ?? '' }}">{{ $v->folio }} - {{ $v->clienta->nombre ?? '' }}</option>
            @endforeach
        </select>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Punto de encuentro frecuente</label>
            <select name="punto_encuentro_id" id="punto_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" onchange="prefillPunto()">
                <option value="">Otro (especificar)</option>
                @foreach($puntos as $p)
                    <option value="{{ $p->id }}" data-nombre="{{ $p->nombre }}" data-dir="{{ $p->direccion }}">{{ $p->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Lugar</label>
            <input name="lugar" id="lugar" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Dirección / ubicación</label>
            <textarea name="direccion" id="direccion" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Fecha programada</label>
            <input type="date" name="fecha_programada" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Hora programada (24h)</label>
            <input type="text" name="hora_programada" pattern="[0-9]{2}:[0-9]{2}" placeholder="HH:MM" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            <p class="text-xs text-gray-400 mt-1">Usa formato 24h, ej. 15:00</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nombre quien recibe</label>
            <input name="nombre_recibe" id="nombre_recibe" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Teléfono contacto</label>
            <input name="telefono_contacto" id="telefono_contacto" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Estado</label>
            <select name="estado" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @foreach(\App\Models\Entrega::ESTADOS as $e)
                    <option value="{{ $e }}">{{ $e }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Indicaciones adicionales</label>
        <textarea name="indicaciones" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
    </div>
    <div class="flex gap-2">
        <button class="bg-pink-600 text-white px-4 py-2 rounded-lg">Guardar</button>
        <a href="{{ route('entregas.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Cancelar</a>
    </div>
</form>
<script>
function prefill() {
    const sel = document.getElementById('venta_id');
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;
    document.getElementById('nombre_recibe').value = opt.dataset.nombre || '';
    document.getElementById('telefono_contacto').value = opt.dataset.tel || '';
}
function prefillPunto() {
    const sel = document.getElementById('punto_id');
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;
    document.getElementById('lugar').value = opt.dataset.nombre || '';
    document.getElementById('direccion').value = opt.dataset.dir || '';
}
</script>
@endsection