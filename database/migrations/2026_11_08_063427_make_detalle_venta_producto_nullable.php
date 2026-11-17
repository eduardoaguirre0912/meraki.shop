<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');
        DB::statement('CREATE TABLE detalle_ventas_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            venta_id INTEGER NOT NULL REFERENCES ventas(id) ON DELETE CASCADE,
            producto_id INTEGER REFERENCES productos(id),
            cantidad INTEGER NOT NULL,
            precio_unitario NUMERIC NOT NULL,
            costo_unitario NUMERIC NOT NULL,
            subtotal NUMERIC NOT NULL,
            utilidad_linea NUMERIC NOT NULL,
            created_at TEXT,
            updated_at TEXT
        )');
        DB::statement('INSERT INTO detalle_ventas_new (id, venta_id, producto_id, cantidad, precio_unitario, costo_unitario, subtotal, utilidad_linea, created_at, updated_at)
            SELECT id, venta_id, producto_id, cantidad, precio_unitario, costo_unitario, subtotal, utilidad_linea, created_at, updated_at FROM detalle_ventas');
        DB::statement('DROP TABLE detalle_ventas');
        DB::statement('ALTER TABLE detalle_ventas_new RENAME TO detalle_ventas');
        DB::statement('PRAGMA foreign_keys=ON');
    }

    public function down(): void
    {
        //
    }
};