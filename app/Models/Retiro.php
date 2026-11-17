<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retiro extends Model
{
    protected $fillable = ['fecha', 'socio', 'monto', 'notas'];
    protected $casts = ['fecha' => 'date', 'monto' => 'decimal:2'];
}