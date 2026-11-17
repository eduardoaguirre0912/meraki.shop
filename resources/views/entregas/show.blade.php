@extends('layouts.app')
@section('title', 'Entrega')
@section('content')
<div class="mb-4 flex items-center justify-between">
    <h2 class="text-2xl font-bold">Entrega — {{ $entrega->venta->folio ?? '' }}</h2>
    <a href="{{ route('entregas.index') }}" class="text-sm text-gray-600 hover:underline">← Volver</a>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-1 text-sm">
        <p><span class="font-medium">Clienta:</span> {{ $entrega->venta->clienta->nombre ?? '' }}</p>
        <p><span class="font-medium">Teléfono:</span> {{ $entrega->venta->clienta->telefono ?? '' }}</p>
        <p><span class="font-medium">Lugar:</span> {{ $entrega->lugar }}</p>
        <p><span class="font-medium">Dirección:</span> {{ $entrega->direccion }}</p>
        <p><span class="font-medium">Indicaciones:</span> {{ $entrega->indicaciones }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-1 text-sm">
        <p><span class="font-medium">Fecha programada:</span> {{ $entrega->fecha_programada?->format('d/m/Y') }}</p>
        <p><span class="font-medium">Hora:</span> {{ $entrega->hora_programada }}</p>
        <p><span class="font-medium">Recibe:</span> {{ $entrega->nombre_recibe }}</p>
        <p><span class="font-medium">Tel. contacto:</span> {{ $entrega->telefono_contacto }}</p>
        <p><span class="font-medium">Estado:</span> {{ $entrega->estado }}</p>
        <p><span class="font-medium">Punto:</span> {{ $entrega->puntoEncuentro->nombre ?? '' }}</p>
    </div>
</div>
<div class="bg-white rounded-xl border border-gray-200 p-4">
    <h3 class="font-semibold mb-2">Historial</h3>
    <ul class="text-sm space-y-1">
        @foreach(json_decode($entrega->historial ?? '[]', true) as $h)
            <li>{{ $h['fecha'] ?? '' }} — {{ $h['accion'] ?? '' }} ({{ $h['estado'] ?? '' }})</li>
        @endforeach
    </ul>
</div>
@endsection