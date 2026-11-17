<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PuntoEncuentro extends Model
{
    protected $table = 'puntos_encuentro';

    protected $fillable = ['nombre', 'direccion', 'referencias', 'observaciones', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function entregas(): HasMany
    {
        return $this->hasMany(Entrega::class);
    }
}