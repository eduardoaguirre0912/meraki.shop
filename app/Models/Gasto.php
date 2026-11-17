<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $fillable = [
        'fecha', 'tipo_gasto', 'concepto', 'monto', 'metodo_pago',
        'linea_negocio', 'estado', 'notas', 'categoria_gasto_id',
        'descripcion', 'comprobante', 'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function categoria(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GastoCategoria::class, 'categoria_gasto_id');
    }
}