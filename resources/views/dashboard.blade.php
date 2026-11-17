@extends('layouts.app')
@section('title', 'Resumen')
@section('content')
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <h2 class="text-2xl font-bold">Resumen</h2>
    <form method="GET" class="flex items-center gap-2">
        <label class="text-sm text-gray-600">Línea:</label>
        <select name="linea" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            @foreach($lineas as $l)
                <option value="{{ $l }}" {{ $linea === $l ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $cards = [
            ['Ventas Totales', number_format($ventasTotales, 2), 'bg-blue-50 text-blue-700'],
            ['Costo de Mercancía', number_format($costoMercancia, 2), 'bg-gray-50 text-gray-700'],
            ['Ganancia Bruta', number_format($gananciaBruta, 2), 'bg-green-50 text-green-700'],
            ['Gastos Operativos', number_format($gastosOperativos, 2), 'bg-red-50 text-red-700'],
            ['Ganancia Neta Estimada', number_format($gananciaNeta, 2), 'bg-emerald-50 text-emerald-700'],
            ['Dinero Cobrado', number_format($dineroCobrado, 2), 'bg-indigo-50 text-indigo-700'],
            ['Saldo Pendiente por Cobrar', number_format($saldoPendiente, 2), 'bg-amber-50 text-amber-700'],
            ['Valor del Inventario', number_format($valorInventario, 2), 'bg-purple-50 text-purple-700'],
        ];
    @endphp
    @foreach($cards as $c)
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">{{ $c[0] }}</p>
            <p class="text-xl font-bold {{ $c[2] }}">${{ $c[1] }}</p>
        </div>
    @endforeach
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Alertas Stock Bajo</p>
        <p class="text-xl font-bold text-yellow-600">{{ $alertasBajo }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-500 mb-1">Alertas Agotado</p>
        <p class="text-xl font-bold text-red-600">{{ $alertasAgotado }}</p>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4"><p class="text-xs text-gray-500 mb-1">Ventas hoy</p><p class="text-lg font-bold">${{ number_format($ventasHoy,2) }}</p></div>
    <div class="bg-white rounded-xl border border-gray-200 p-4"><p class="text-xs text-gray-500 mb-1">Ingresos hoy</p><p class="text-lg font-bold">${{ number_format($ingresosHoy,2) }}</p></div>
    <div class="bg-white rounded-xl border border-gray-200 p-4"><p class="text-xs text-gray-500 mb-1">Gastos hoy</p><p class="text-lg font-bold">${{ number_format($gastosHoy,2) }}</p></div>
    <div class="bg-white rounded-xl border border-gray-200 p-4"><p class="text-xs text-gray-500 mb-1">Utilidad hoy</p><p class="text-lg font-bold">${{ number_format($utilidadHoy,2) }}</p></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="font-semibold mb-3">Próximas entregas</h3>
        <ul class="text-sm space-y-2">
            @forelse($proximasEntregas as $e)
                <li class="flex justify-between"><span>{{ $e->venta->folio ?? '' }} — {{ $e->venta->clienta->nombre ?? '' }}</span><span class="text-gray-500">{{ $e->fecha_programada?->format('d/m') }} {{ $e->hora_programada }}</span></li>
            @empty
                <li class="text-gray-500">Sin entregas próximas.</li>
            @endforelse
        </ul>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="font-semibold mb-3">Productos con stock bajo</h3>
        <ul class="text-sm space-y-2">
            @forelse($productosBajoLista as $p)
                <li class="flex justify-between"><span>{{ $p->sku }} — {{ $p->nombre }}</span><span class="text-red-600">{{ $p->stock_actual }}</span></li>
            @empty
                <li class="text-gray-500">Sin alertas.</li>
            @endforelse
        </ul>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="font-semibold mb-3">Ventas Mensuales</h3>
        <canvas id="monthlyChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="font-semibold mb-3">Top Productos Más Vendidos</h3>
        <canvas id="topChart" height="200"></canvas>
    </div>
</div>

<script>
    const monthlyLabels = @json($monthly->pluck('ym'));
    const monthlyData = @json($monthly->pluck('total')->map(fn($v)=>(float)$v));
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: { labels: monthlyLabels, datasets: [{ label: 'Ventas', data: monthlyData, backgroundColor: '#ec4899' }] },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    const topLabels = @json($topProductos->map(fn($t)=>$t->producto->nombre ?? 'N/A'));
    const topData = @json($topProductos->pluck('total_qty')->map(fn($v)=>(int)$v));
    new Chart(document.getElementById('topChart'), {
        type: 'bar',
        data: { labels: topLabels, datasets: [{ label: 'Cantidad', data: topData, backgroundColor: '#8b5cf6' }] },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
    });
</script>
@endsection