<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entregas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->unique()->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('punto_encuentro_id')->nullable()->constrained('puntos_encuentro')->nullOnDelete();
            $table->string('lugar')->nullable();
            $table->text('direccion')->nullable();
            $table->date('fecha_programada')->nullable();
            $table->time('hora_programada')->nullable();
            $table->text('indicaciones')->nullable();
            $table->string('nombre_recibe')->nullable();
            $table->string('telefono_contacto', 20)->nullable();
            $table->string('estado')->default('Pendiente de programar');
            $table->text('historial')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};