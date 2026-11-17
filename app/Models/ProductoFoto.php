<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoFoto extends Model
{
    protected $fillable = ['producto_id', 'path', 'principal', 'orden'];

    protected $casts = ['principal' => 'boolean'];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}