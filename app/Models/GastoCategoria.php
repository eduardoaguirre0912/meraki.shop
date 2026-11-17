<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GastoCategoria extends Model
{
    protected $fillable = ['nombre', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class, 'categoria_gasto_id');
    }
}