@extends('layouts.app')
@section('title', 'Nueva Entrada')
@section('content')
<h2 class="text-2xl font-bold mb-4">Nueva Entrada de Inventario</h2>
@php $productos = \App\Models\Producto::orderBy('nombre')->get(); @endphp
<form method="POST" action="{{ route('entradas.store') }}" class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl space-y-4">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Fecha</label>
            <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Buscar producto</label>
            <input type="text" id="entrada_search" placeholder="SKU, nombre, variante o marca..." oninput="filterEntrada()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Producto</label>
            <select name="producto_id" id="entrada_sel" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <option value="">Seleccionar...</option>
                @foreach($productos as $p)
                    <option value="{{ $p->id }}" data-label="{{ strtolower($p->sku.' '.$p->nombre.' '.($p->variante ?? '').' '.($p->marca ?? '')) }}">{{ $p->sku }} - {{ $p->nombre }}{{ $p->variante ? ' ('.$p->variante.')' : '' }}{{ $p->marca ? ' - '.$p->marca : '' }} (stock: {{ $p->stock_actual }} | costo: ${{ number_format($p->costo_unitario,2) }} | precio: ${{ number_format($p->precio_venta,2) }})</option>
                @endforeach
            </select>
        </div>
        <script>
        const entradaOpts = Array.from(document.getElementById('entrada_sel').options).map(o => ({ value: o.value, html: o.outerHTML, label: (o.dataset.label || '').toLowerCase() }));
        function filterEntrada() {
            const q = (document.getElementById('entrada_search').value || '').toLowerCase();
            const sel = document.getElementById('entrada_sel');
            const current = sel.value;
            sel.innerHTML = '<option value="">Seleccionar...</option>' + entradaOpts.filter(o => o.value && o.label.includes(q)).map(o => o.html).join('');
            sel.value = current;
        }
        </script>
        <div>
            <label class="block text-sm font-medium mb-1">Cantidad</label>
            <input type="number" name="cantidad" min="1" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Costo unitario</label>
            <input type="number" step="0.01" name="costo_unitario" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Proveedor</label>
            <input name="proveedor" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Referencia</label>
            <input name="referencia" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Notas</label>
        <textarea name="notas" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
    </div>
    <div class="flex gap-2">
        <button class="bg-pink-600 text-white px-4 py-2 rounded-lg">Guardar</button>
        <a href="{{ route('entradas.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Cancelar</a>
    </div>
</form>
@endsection