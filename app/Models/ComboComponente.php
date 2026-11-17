<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboComponente extends Model
{
    protected $fillable = ['combo_producto_id', 'producto_id', 'cantidad'];
    protected $casts = ['cantidad' => 'integer'];

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'combo_producto_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}