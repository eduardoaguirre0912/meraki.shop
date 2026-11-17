<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->foreignId('categoria_gasto_id')->nullable()->constrained('gasto_categorias')->nullOnDelete();
            $table->text('descripcion')->nullable();
            $table->string('comprobante')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->dropForeign(['categoria_gasto_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['categoria_gasto_id', 'descripcion', 'comprobante', 'user_id']);
        });
    }
};