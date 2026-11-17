@extends('layouts.app')
@section('title', 'Entregas')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Entregas</h2>
    <a href="{{ route('entregas.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-pink-700">+ Nueva Entrega</a>
</div>
<form method="GET" class="mb-4 flex flex-wrap gap-2 items-end">
    <div>
        <label class="text-xs">Vista</label>
        <select name="view" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="day" {{ $view==='day'?'selected':'' }}>Día</option>
            <option value="week" {{ $view==='week'?'selected':'' }}>Semana</option>
            <option value="month" {{ $view==='month'?'selected':'' }}>Mes</option>
        </select>
    </div>
    <div>
        <label class="text-xs">Fecha</label>
        <input type="date" name="fecha" value="{{ $fecha }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
    </div>
    <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Ver</button>
</form>
<p class="text-sm text-gray-500 mb-3">Periodo: {{ $start->format('d/m/Y') }} — {{ $end->format('d/m/Y') }} ({{ $ventas->count() }} ventas con entrega)</p>

@if($view === 'week' || $view === 'month')
@php
    if ($view === 'week') {
        $calStart = $start->copy()->startOfWeek();
        $calEnd = $start->copy()->endOfWeek();
    } else {
        $calStart = $start->copy()->startOfMonth()->startOfWeek();
        $calEnd = $start->copy()->endOfMonth()->endOfWeek();
    }
    $days = [];
    $cur = $calStart->copy();
    while ($cur->lte($calEnd)) {
        $days[] = $cur->copy();
        $cur->addDay();
    }
@endphp
<div class="bg-white rounded-xl border border-gray-200 p-2 mb-6">
    <div class="grid grid-cols-7 gap-1 mb-1">
        @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dname)
            <div class="text-center text-xs font-semibold text-gray-500 py-1">{{ $dname }}</div>
        @endforeach
    </div>
    <div class="grid grid-cols-7 gap-1">
        @foreach($days as $day)
            @php
                $inMonth = $day->month === $start->month;
                $dayVentas = $ventas->filter(function($v) use ($day) {
                    $d = $v->entrega && $v->entrega->fecha_programada ? $v->entrega->fecha_programada : $v->fecha;
                    return $d && $d->isSameDay($day);
                });
            @endphp
            <div class="border border-gray-100 rounded p-1 min-h-[90px] {{ $inMonth ? 'bg-white' : 'bg-gray-50 text-gray-300' }}">
                <div class="text-xs font-semibold mb-1">{{ $day->format('d') }}</div>
                @foreach($dayVentas as $v)
                @php $ent = $v->entrega; @endphp
                    <a href="{{ $ent ? route('entregas.show', $ent) : route('ventas.show', $v) }}" class="block mb-1 p-1 rounded bg-pink-50 hover:bg-pink-100 text-[10px] leading-tight">
                        <div class="font-medium">{{ $ent?->hora_programada ?? '' }}</div>
                        <div>{{ $v->folio }}</div>
                        <div class="truncate">{{ $v->clienta->nombre ?? '' }}</div>
                        <div class="truncate">{{ $ent?->lugar ?: ($v->notas ?? '') }}</div>
                    </a>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left px-4 py-3">Fecha/Hora</th>
                <th class="text-left px-4 py-3">Folio</th>
                <th class="text-left px-4 py-3">Clienta</th>
                <th class="text-left px-4 py-3">Lugar</th>
                <th class="text-left px-4 py-3">Recibe</th>
                <th class="text-left px-4 py-3">Estado</th>
                <th class="text-right px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $v)
            @php $ent = $v->entrega; @endphp
                <tr class="border-t border-gray-100">
                    <td class="px-4 py-3">{{ ($ent?->fecha_programada ?? $v->fecha) ? ($ent?->fecha_programada ?? $v->fecha)->format('d/m/Y') : '-' }} {{ $ent?->hora_programada }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $v->folio }}</td>
                    <td class="px-4 py-3">{{ $v->clienta->nombre ?? '' }}</td>
                    <td class="px-4 py-3">{{ $ent?->lugar ?: ($v->notas ?? '') }}</td>
                    <td class="px-4 py-3">{{ $ent?->nombre_recibe ?? ($v->clienta->nombre ?? '') }}</td>
                    <td class="px-4 py-3">{{ $ent?->estado ?? 'Pendiente de programar' }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        @if($ent)
                            <a href="{{ route('entregas.show', $ent) }}" class="text-blue-600 hover:underline">Ver</a>
                            <a href="{{ route('entregas.edit', $ent) }}" class="text-gray-600 hover:underline">Editar</a>
                        @else
                            <a href="{{ route('ventas.show', $v) }}" class="text-blue-600 hover:underline">Ver venta</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Sin ventas en este periodo.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection