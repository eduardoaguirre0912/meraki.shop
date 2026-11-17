@extends('layouts.app')
@section('title', 'Finanzas')
@section('content')
<h2 class="text-2xl font-bold mb-4">Finanzas</h2>
<form method="GET" class="mb-4 flex flex-wrap gap-2 items-end">
    <div>
        <label class="text-xs">Periodo</label>
        <select name="periodo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            @foreach(['day'=>'Día','week'=>'Semana','month'=>'Mes','year'=>'Año','custom'=>'Personalizado'] as $k=>$v)
                <option value="{{ $k }}" {{ $periodo===$k?'selected':'' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-xs">Fecha base</label>
        <input type="date" name="fecha" value="{{ $fecha }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
    </div>
    <div id="customRange" class="w-full {{ $periodo==='custom' ? '' : 'hidden' }}">
        <div class="mt-3 pt-3 border-t border-gray-200 flex flex-wrap gap-2 items-end">
            <div>
                <label class="text-xs">Desde</label>
                <input type="date" name="fecha_inicio" value="{{ $inicio }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs">Hasta</label>
                <input type="date" name="fecha_fin" value="{{ $fin }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
    </div>
    <div>
        <label class="text-xs">Línea</label>
        <select name="linea" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            @foreach(['Todas','Maquillaje','Uniformes','Accesorios','Otros'] as $l)
                <option value="{{ $l }}" {{ $linea===$l?'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <button class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm">Filtrar</button>
</form>
<script>
    const periodoSel = document.querySelector('select[name="periodo"]');
    const range1 = document.getElementById('customRange');
    function toggleRange() {
        const isCustom = periodoSel.value === 'custom';
        range1.classList.toggle('hidden', !isCustom);
    }
    periodoSel.addEventListener('change', toggleRange);
    toggleRange();
</script>
</form>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
    $cards = [
        ['Ventas registradas', number_format($totalVentas,2)],
        ['Dinero cobrado', number_format($dineroCobrado,2)],
        ['Ventas pendientes', number_format($saldoPendiente,2)],
        ['Gastos totales', number_format($totalGastos,2)],
        ['Gastos pagados', number_format($gastosPagados,2)],
        ['Gastos pendientes', number_format($gastosPendientes,2)],
        ['Costo mercancía vendida', number_format($costoVendido,2)],
        ['Utilidad bruta', number_format($utilidadBruta,2)],
        ['Utilidad neta estimada', number_format($utilidadNeta,2)],
        ['Dinero disponible', number_format($dineroDisponible,2)],
        ['Cuentas por cobrar', number_format($cuentasPorCobrar,2)],
        ['Compras realizadas', number_format($totalCompras,2)],
    ];
    @endphp
    @foreach($cards as $c)
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">{{ $c[0] }}</p>
            <p class="text-lg font-bold">${{ $c[1] }}</p>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="font-semibold mb-3">Ingresos vs Gastos (mensual)</h3>
        <canvas id="igChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="font-semibold mb-3">Gastos por categoría</h3>
        <canvas id="catChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="font-semibold mb-3">Ventas por línea de negocio</h3>
        <canvas id="lineaChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="font-semibold mb-3">Top productos por rentabilidad</h3>
        <canvas id="rentChart" height="200"></canvas>
    </div>
</div>

<script>
    const monthlyLabels = @json($monthly->pluck('ym'));
    const monthlyVentas = @json($monthly->pluck('ventas')->map(fn($v)=>(float)$v));
    const monthlyUtil = @json($monthly->pluck('utilidad')->map(fn($v)=>(float)$v));
    const gastosLabels = @json($gastosMes->pluck('ym'));
    const gastosData = @json($gastosMes->pluck('total')->map(fn($v)=>(float)$v));
    // align gastos to monthly labels
    const gastosMap = Object.fromEntries(gastosLabels.map((l,i)=>[l,gastosData[i]]));
    const gastosAligned = monthlyLabels.map(l => gastosMap[l] ?? 0);

    new Chart(document.getElementById('igChart'), {
        type: 'bar',
        data: { labels: monthlyLabels, datasets: [
            { label: 'Ventas', data: monthlyVentas, backgroundColor: '#ec4899' },
            { label: 'Gastos', data: gastosAligned, backgroundColor: '#f59e0b' },
        ]},
        options: { responsive: true }
    });

    new Chart(document.getElementById('catChart'), {
        type: 'pie',
        data: { labels: @json($gastosCat->pluck('tipo_gasto')), datasets: [{ data: @json($gastosCat->pluck('total')->map(fn($v)=>(float)$v)), backgroundColor: ['#ec4899','#8b5cf6','#3b82f6','#10b981','#f59e0b','#ef4444','#6b7280'] }] },
        options: { responsive: true }
    });

    new Chart(document.getElementById('lineaChart'), {
        type: 'bar',
        data: { labels: @json($ventasLinea->pluck('linea_negocio')), datasets: [{ label: 'Ventas', data: @json($ventasLinea->pluck('total')->map(fn($v)=>(float)$v)), backgroundColor: '#3b82f6' }] },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('rentChart'), {
        type: 'bar',
        data: { labels: @json($topRentables->map(fn($t)=>$t->producto->nombre ?? 'N/A')), datasets: [{ label: 'Utilidad', data: @json($topRentables->pluck('utilidad')->map(fn($v)=>(float)$v)), backgroundColor: '#10b981' }] },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
    });
</script>
@endsection