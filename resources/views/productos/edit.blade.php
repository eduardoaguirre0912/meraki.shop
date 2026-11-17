@extends('layouts.app')
@section('title', 'Editar Producto')
@section('content')
<h2 class="text-2xl font-bold mb-4">Editar Producto ({{ $producto->sku }})</h2>
<form method="POST" action="{{ route('productos.update', $producto) }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl space-y-4">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Línea de negocio</label>
            <select name="linea_negocio" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                @foreach(['Maquillaje','Uniformes','Accesorios','Otros'] as $l)
                    <option value="{{ $l }}" {{ $producto->linea_negocio===$l?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nombre</label>
            <input name="nombre" value="{{ $producto->nombre }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Categoría</label>
            <input name="categoria" value="{{ $producto->categoria }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Marca</label>
            <input name="marca" value="{{ $producto->marca }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Variante</label>
            <input name="variante" value="{{ $producto->variante }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Stock actual</label>
            <input type="number" name="stock_actual" value="{{ $producto->stock_actual }}" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Stock mínimo</label>
            <input type="number" name="stock_minimo" value="{{ $producto->stock_minimo }}" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Costo unitario</label>
            <input type="number" step="0.01" name="costo_unitario" value="{{ $producto->costo_unitario }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Precio venta</label>
            <input type="number" step="0.01" name="precio_venta" value="{{ $producto->precio_venta }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Proveedor</label>
            <input name="proveedor" value="{{ $producto->proveedor }}" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Fotos actuales</label>
        <div class="flex flex-wrap gap-2 mb-2">
            @foreach($producto->fotos as $f)
                <div class="relative">
                    <img src="{{ asset('storage/'.$f->path) }}" class="w-20 h-20 object-cover rounded border">
                    <label class="block text-xs"><input type="checkbox" name="delete_fotos[]" value="{{ $f->id }}"> Eliminar</label>
                </div>
            @endforeach
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Nueva foto principal (opcional)</label>
        <input type="file" name="foto" accept="image/*" class="w-full border border-gray-300 rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Agregar galería</label>
        <input type="file" name="fotos[]" accept="image/*" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2">
    </div>
    <div class="flex items-center gap-2">
        <input type="checkbox" name="es_combo" value="1" id="es_combo" {{ $producto->es_combo ? 'checked' : '' }}>
        <label for="es_combo" class="text-sm">Es combo (paquete de productos)</label>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Componentes del combo</label>
        @php $componentesDisponibles = \App\Models\Producto::where('es_combo', false)->where('id', '!=', $producto->id)->orderBy('nombre')->get(); @endphp
        @php $comps = $producto->componentes; @endphp
        @if($comps->isEmpty())
            @php $comps = collect([null]); @endphp
        @endif
        @foreach($comps as $i => $c)
            <div class="flex gap-2 mb-2">
                <select name="componentes[{{ $i }}][producto_id]" class="flex-1 border border-gray-300 rounded-lg px-2 py-2 text-sm">
                    <option value="">Seleccionar producto...</option>
                    @foreach($componentesDisponibles as $cp)
                        <option value="{{ $cp->id }}" {{ $c && $c->producto_id===$cp->id ? 'selected' : '' }}>{{ $cp->sku }} - {{ $cp->nombre }}{{ $cp->variante ? ' ('.$cp->variante.')' : '' }} - Stock: {{ $cp->stock_actual }}</option>
                    @endforeach
                </select>
                <input type="number" name="componentes[{{ $i }}][cantidad]" min="1" value="{{ $c->cantidad ?? 1 }}" class="w-20 border border-gray-300 rounded-lg px-2 py-2 text-sm">
            </div>
        @endforeach
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Notas</label>
        <textarea name="notas" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ $producto->notas }}</textarea>
    </div>
    <div class="flex gap-2">
        <button class="bg-pink-600 text-white px-4 py-2 rounded-lg">Actualizar</button>
        <a href="{{ route('productos.index') }}" class="px-4 py-2 rounded-lg border border-gray-300">Cancelar</a>
    </div>
</form>
@endsection