<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntradaInventario extends Model
{
    protected $table = 'entradas_inventario';

    protected $fillable = [
        'fecha', 'producto_id', 'cantidad', 'costo_unitario',
        'total_compra', 'proveedor', 'referencia', 'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'costo_unitario' => 'decimal:2',
        'total_compra' => 'decimal:2',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}