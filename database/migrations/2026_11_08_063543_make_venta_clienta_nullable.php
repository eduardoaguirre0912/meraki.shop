<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');
        DB::statement('CREATE TABLE ventas_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            folio TEXT UNIQUE,
            clienta_id INTEGER REFERENCES clientas(id) ON DELETE CASCADE,
            fecha TEXT NOT NULL,
            total_pedido NUMERIC DEFAULT 0,
            costo_total NUMERIC DEFAULT 0,
            utilidad_bruta NUMERIC DEFAULT 0,
            anticipo_pagado NUMERIC DEFAULT 0,
            saldo_pendiente NUMERIC DEFAULT 0,
            metodo_pago TEXT NOT NULL,
            estado_pago TEXT NOT NULL,
            notas TEXT,
            created_at TEXT,
            updated_at TEXT
        )');
        DB::statement('INSERT INTO ventas_new (id, folio, clienta_id, fecha, total_pedido, costo_total, utilidad_bruta, anticipo_pagado, saldo_pendiente, metodo_pago, estado_pago, notas, created_at, updated_at)
            SELECT id, folio, clienta_id, fecha, total_pedido, costo_total, utilidad_bruta, anticipo_pagado, saldo_pendiente, metodo_pago, estado_pago, notas, created_at, updated_at FROM ventas');
        DB::statement('DROP TABLE ventas');
        DB::statement('ALTER TABLE ventas_new RENAME TO ventas');
        DB::statement('PRAGMA foreign_keys=ON');
    }

    public function down(): void
    {
        //
    }
};