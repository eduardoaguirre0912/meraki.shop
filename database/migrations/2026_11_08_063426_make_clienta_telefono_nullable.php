<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');
        DB::statement('CREATE TABLE clientas_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            telefono TEXT UNIQUE,
            nombre TEXT NOT NULL,
            notas TEXT,
            created_at TEXT,
            updated_at TEXT
        )');
        DB::statement('INSERT INTO clientas_new (id, telefono, nombre, notas, created_at, updated_at) SELECT id, telefono, nombre, notas, created_at, updated_at FROM clientas');
        DB::statement('DROP TABLE clientas');
        DB::statement('ALTER TABLE clientas_new RENAME TO clientas');
        DB::statement('PRAGMA foreign_keys=ON');
    }

    public function down(): void
    {
        // not reversible safely
    }
};