@extends('layouts.app')
@section('title', 'Nueva Venta')
@section('content')
<h2 class="text-2xl font-bold mb-4">Registrar Nueva Venta</h2>
<form method="POST" action="{{ route('ventas.store') }}" id="ventaForm" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Teléfono clienta (10 dígitos)</label>
            <input type="text" name="telefono" id="telefono" maxlength="10" required class="w-full border border-gray-300 rounded-lg px-3 py-2" onblur="buscarClienta()">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nombre clienta</label>
            <input type="text" name="nombre" id="nombre" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Fecha</label>
            <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Método de pago</label>
            <select name="metodo_pago" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @foreach(['Efectivo','Transferencia','Tarjeta','Depósito','Mixto'] as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Anticipo pagado</label>
            <input type="number" step="0.01" name="anticipo_pagado" value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Notas</label>
            <input type="text" name="notas" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
    </div>

    <div>
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-semibold">Productos</h3>
            <button type="button" onclick="addRow()" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm">+ Agregar producto</button>
        </div>
        <div id="itemsContainer" class="space-y-3"></div>
    </div>

    <div class="border border-gray-200 rounded-lg p-4 space-y-3">
        <h3 class="font-semibold">Entrega (opcional)</h3>
        @php $puntos = \App\Models\PuntoEncuentro::where('activo', true)->orderBy('nombre')->get(); @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Punto de encuentro frecuente</label>
                <select name="entrega_punto_encuentro_id" id="entrega_punto" class="w-full border border-gray-300 rounded-lg px-3 py-2" onchange="prefillPuntoVenta()">
                    <option value="">Otro (especificar)</option>
                    @foreach($puntos as $p)
                        <option value="{{ $p->id }}" data-nombre="{{ $p->nombre }}" data-dir="{{ $p->direccion }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Lugar de entrega</label>
                <input name="entrega_lugar" id="entrega_lugar" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Dirección / ubicación</label>
                <textarea name="entrega_direccion" id="entrega_direccion" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Fecha programada</label>
                <input type="date" name="entrega_fecha" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Hora programada (24h)</label>
                <input type="text" name="entrega_hora" pattern="[0-9]{2}:[0-9]{2}" placeholder="HH:MM" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <p class="text-xs text-gray-400 mt-1">Usa formato 24h, ej. 15:00</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nombre quien recibe</label>
                <input name="entrega_nombre_recibe" id="entrega_nombre_recibe" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Teléfono contacto</label>
                <input name="entrega_telefono" id="entrega_telefono" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Estado entrega</label>
                <select name="entrega_estado" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    @foreach(\App\Models\Entrega::ESTADOS as $e)
                        <option value="{{ $e }}" {{ $e==='Pendiente de programar'?'selected':'' }}>{{ $e }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Indicaciones adicionales</label>
            <textarea name="entrega_indicaciones" class="w-full border border-gray-300 rounded-lg px-3 py-2"></textarea>
        </div>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="bg-pink-600 text-white px-4 py-2 rounded-lg">Guardar Venta</button>
        <a href="{{ route('ventas.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Cancelar</a>
    </div>
</form>

<script>
const productos = @json($productos);

function buscarClienta() {
    const tel = document.getElementById('telefono').value;
    if (tel.length !== 10) return;
    fetch('/clientas-buscar?telefono=' + encodeURIComponent(tel))
        .then(r => r.json())
        .then(data => {
            if (data && data.nombre) {
                document.getElementById('nombre').value = data.nombre;
                document.getElementById('entrega_nombre_recibe').value = data.nombre;
                document.getElementById('entrega_telefono').value = data.telefono || '';
            }
        });
}

let rowIndex = 0;
function addRow() {
    const container = document.getElementById('itemsContainer');
    const idx = rowIndex++;
    const div = document.createElement('div');
    div.className = 'grid grid-cols-1 md:grid-cols-6 gap-2 items-end border border-gray-200 rounded-lg p-3';
    div.innerHTML = `
        <div class="flex items-end">
            <img id="img_${idx}" src="" class="w-16 h-16 object-cover rounded border border-gray-200 hidden">
        </div>
        <div class="md:col-span-2 space-y-1">
            <label class="block text-xs font-medium mb-1">Buscar producto</label>
            <input type="text" id="search_${idx}" placeholder="SKU, nombre, tono o marca..." oninput="filterProducts(${idx})" class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-sm">
            <label class="block text-xs font-medium mb-1">Producto</label>
            <select name="items[${idx}][producto_id]" id="sel_${idx}" onchange="fillProduct(this, ${idx})" required class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm">
                <option value="">Seleccionar...</option>
                ${renderOptions(productos)}
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium mb-1">Stock disp.</label>
            <input type="text" id="stock_${idx}" readonly class="w-full border border-gray-200 bg-gray-50 rounded-lg px-2 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1">Cantidad</label>
            <input type="number" name="items[${idx}][cantidad]" min="1" value="1" required class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1">Precio unit.</label>
            <input type="number" step="0.01" name="items[${idx}][precio_unitario]" id="precio_${idx}" required class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1">Costo unit.</label>
            <input type="number" step="0.01" name="items[${idx}][costo_unitario]" id="costo_${idx}" required class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm">
        </div>
        <div class="flex items-end">
            <button type="button" onclick="this.closest('div.grid').remove()" class="text-red-600 text-sm hover:underline">Quitar</button>
        </div>
    `;
    container.appendChild(div);
}

function productLabel(p) {
    let label = [p.sku, p.nombre, p.variante, p.marca].filter(Boolean).join(' - ');
    if (p.es_combo && p.componentes && p.componentes.length) {
        const comp = p.componentes.map(c => (c.producto ? c.producto.nombre : '') + (c.producto && c.producto.variante ? ' (' + c.producto.variante + ')' : '') + ' x' + c.cantidad).join(', ');
        label += ' | Combo: ' + comp;
    }
    return label;
}
function optionHtml(p) {
    const foto = (p.fotos && p.fotos.length) ? (p.fotos.find(f=>f.principal) || p.fotos[0]).path : '';
    let stock = p.stock_actual;
    if (p.es_combo && p.componentes && p.componentes.length) {
        const stocks = p.componentes.map(c => c.producto ? Math.floor(c.producto.stock_actual / Math.max(1, c.cantidad)) : 0);
        stock = stocks.length ? Math.min(...stocks) : 0;
    }
    return `<option value="${p.id}" data-precio="${p.precio_venta}" data-costo="${p.costo_unitario}" data-stock="${stock}" data-foto="${foto}">${productLabel(p)}</option>`;
}
function renderOptions(list) {
    return '<option value="">Seleccionar...</option>' + list.map(optionHtml).join('');
}
function filterProducts(idx) {
    const q = (document.getElementById('search_' + idx).value || '').toLowerCase();
    const filtered = q ? productos.filter(p => {
        const t = [p.sku, p.nombre, p.variante, p.marca].join(' ').toLowerCase();
        return t.includes(q);
    }) : productos;
    document.getElementById('sel_' + idx).innerHTML = renderOptions(filtered);
}

function prefillPuntoVenta() {
    const sel = document.getElementById('entrega_punto');
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;
    document.getElementById('entrega_lugar').value = opt.dataset.nombre || '';
    document.getElementById('entrega_direccion').value = opt.dataset.dir || '';
}

function fillProduct(sel, idx) {
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;
    document.getElementById('stock_' + idx).value = opt.dataset.stock;
    document.getElementById('precio_' + idx).value = opt.dataset.precio;
    document.getElementById('costo_' + idx).value = opt.dataset.costo;
    const img = document.getElementById('img_' + idx);
    if (opt.dataset.foto) {
        img.src = '/storage/' + opt.dataset.foto;
        img.classList.remove('hidden');
    } else {
        img.classList.add('hidden');
    }
}

// add one row initially
addRow();
</script>
@endsection