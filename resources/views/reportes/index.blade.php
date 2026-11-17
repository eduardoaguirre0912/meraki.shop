@extends('layouts.app')
@section('title', 'Reportes')
@section('content')
<h2 class="text-2xl font-bold mb-4">Reportes y Exportación</h2>
<p class="text-sm text-gray-500 mb-4">Descarga reportes en CSV (abre en Excel). Usa "Imprimir" del navegador para PDF.</p>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @php
        $links = [
            ['Ventas', route('reportes.ventas')],
            ['Gastos', route('reportes.gastos')],
            ['Inventario (todo)', route('reportes.inventario')],
            ['Inventario (Maquillaje)', route('reportes.inventario', ['linea' => 'Maquillaje'])],
            ['Inventario (Uniformes y otros)', route('reportes.inventario', ['linea' => 'Otros'])],
            ['Movimientos de inventario', route('reportes.movimientos')],
            ['Cuentas por cobrar', route('reportes.cuentas')],
            ['Entregas', route('reportes.entregas')],
            ['Utilidades', route('reportes.utilidades')],
        ];
    @endphp
    @foreach($links as $l)
        <a href="{{ $l[1] }}" class="bg-white rounded-xl border border-gray-200 p-4 hover:bg-gray-50 flex items-center justify-between">
            <span class="font-medium">{{ $l[0] }}</span>
            <span class="text-pink-600 text-sm">Descargar CSV</span>
        </a>
    @endforeach
</div>
@endsection