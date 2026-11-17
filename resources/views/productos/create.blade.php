@extends('layouts.app')
@section('title', 'Nuevo Producto')
@section('content')
<h2 class="text-2xl font-bold mb-4">{{ request('combo') ? 'Nuevo Combo' : 'Nuevo Producto' }}</h2>
<form method="POST" action="{{ route('productos.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl space-y-4">
    @csrf
    @if(request('combo'))
        <input type="hidden" name="linea_negocio" value="Maquillaje">
        <input type="hidden" name="stock_actual" value="0">
        <input type="hidden" name="stock_minimo" value="0">
        <input type="hidden" name="es_combo" value="1">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nombre del combo</label>
                <input name="nombre" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Costo unitario</label>
                <input type="number" step="0.01" name="costo_unitario" value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Precio venta</label>
                <input type="number" step="0.01" name="precio_venta" value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
        </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Línea de negocio</label>
            <select name="linea_negocio" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @foreach(['Maquillaje','Uniformes','Accesorios','Otros'] as $l)
                    <option value="{{ $l }}">{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nombre</label>
            <input name="nombre" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Categoría</label>
            <input name="categoria" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Marca</label>
            <input name="marca" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Variante (tono/talla)</label>
            <input name="variante" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Stock actual</label>
            <input type="number" name="stock_actual" value="0" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Stock mínimo</label>
            <input type="number" name="stock_minimo" value="0" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Costo unitario</label>
            <input type="number" step="0.01" name="costo_unitario" value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Precio venta</label>
            <input type="number" step="0.01" name="precio_venta" value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Proveedor</label>
            <input name="proveedor" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
    </div>
    @endif
    <div>
        <label class="block text-sm font-medium mb-1">Foto principal (opcional)</label>
        <input type="file" name="foto" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Galería (varias, opcional)</label>
        <input type="file" name="fotos[]" accept="image/*" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2">
    </div>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="es_combo" value="1" id="es_combo" {{ request('combo') ? 'checked' : '' }}>
        <label for="es_combo" class="text-sm">Es combo (paquete de productos)</label>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Componentes del combo (si aplica)</label>
        @php $componentesDisponibles = \App\Models\Producto::where('es_combo', false)->orderBy('nombre')->get(); @endphp
        <div id="componentesContainer" class="space-y-2"></div>
        <button type="button" onclick="addComponente()" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm mt-2">+ Agregar componente</button>
        <script>
        const compOpts = [
            @foreach($componentesDisponibles as $cp)
            { value: '{{ $cp->id }}', label: '{{ strtolower($cp->sku.' '.$cp->nombre.' '.($cp->variante ?? '').' '.$cp->stock_actual) }}', html: '<option value="{{ $cp->id }}">{{ $cp->sku }} - {{ $cp->nombre }}{{ $cp->variante ? " (".$cp->variante.")" : "" }} - Stock: {{ $cp->stock_actual }}</option>' },
            @endforeach
        ];
        let compIndex = 0;
        function addComponente() {
            const container = document.getElementById('componentesContainer');
            const idx = compIndex++;
            const div = document.createElement('div');
            div.className = 'flex flex-col gap-1 border border-gray-200 rounded p-2';
            div.innerHTML = `
                <input type="text" id="comp_search_${idx}" placeholder="Buscar componente..." oninput="filterComp(${idx})" class="border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
                <div class="flex gap-2">
                    <select name="componentes[${idx}][producto_id]" id="comp_sel_${idx}" class="flex-1 border border-gray-300 rounded-lg px-2 py-2 text-sm">
                        <option value="">Seleccionar producto...</option>
                        ${compOpts.map(o => o.html).join('')}
                    </select>
                    <div class="flex flex-col">
                        <label class="text-xs text-gray-500">Cantidad</label>
                        <input type="number" name="componentes[${idx}][cantidad]" min="1" value="1" class="w-20 border border-gray-300 rounded-lg px-2 py-2 text-sm">
                    </div>
                    <button type="button" onclick="this.closest('div.flex.flex-col').remove()" class="text-red-600 text-sm">Quitar</button>
                </div>
            `;
            container.appendChild(div);
        }
        function filterComp(idx) {
            const q = (document.getElementById('comp_search_' + idx).value || '').toLowerCase();
            const sel = document.getElementById('comp_sel_' + idx);
            const current = sel.value;
            sel.innerHTML = '<option value="">Seleccionar producto...</option>' + compOpts.filter(o => !q || o.label.includes(q)).map(o => o.html).join('');
            sel.value = current;
        }
        addComponente();
        </script>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Notas</label>
        <textarea name="notas" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
    </div>
    <div class="flex gap-2">
        <button class="bg-pink-600 text-white px-4 py-2 rounded-lg">Guardar</button>
        <a href="{{ route('productos.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Cancelar</a>
    </div>
</form>
@endsection