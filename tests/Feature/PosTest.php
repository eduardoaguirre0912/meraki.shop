<?php

use App\Models\Producto;
use App\Models\Clienta;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('sku autogenerado', function () {
    $p = Producto::create(['linea_negocio' => 'Maquillaje', 'nombre' => 'Test', 'categoria' => 'Labios', 'stock_actual' => 1]);
    expect($p->sku)->toBe('MAQ-LABI-001');
    $p->delete();
});

test('venta descuenta stock', function () {
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);
    $p = Producto::create(['linea_negocio' => 'Maquillaje', 'nombre' => 'Test2', 'categoria' => 'Labios', 'stock_actual' => 5, 'costo_unitario' => 10, 'precio_venta' => 20]);
    $this->post('/ventas', [
        'telefono' => '5512345678',
        'nombre' => 'Ana',
        'fecha' => date('Y-m-d'),
        'metodo_pago' => 'Efectivo',
        'anticipo_pagado' => 0,
        'items' => [[
            'producto_id' => $p->id,
            'cantidad' => 2,
            'precio_unitario' => 20,
            'costo_unitario' => 10,
        ]],
    ])->assertRedirect(route('ventas.index'));
    expect($p->fresh()->stock_actual)->toBe(3);
    // cleanup
    \App\Models\Venta::query()->delete();
    \App\Models\DetalleVenta::query()->delete();
    \App\Models\Clienta::query()->delete();
    $p->delete();
});