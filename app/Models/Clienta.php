<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clienta extends Model
{
    protected $fillable = ['telefono', 'nombre', 'notas'];

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }
}