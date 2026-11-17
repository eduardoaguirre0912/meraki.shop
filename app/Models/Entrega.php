<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entrega extends Model
{
    protected $fillable = [
        'venta_id', 'punto_encuentro_id', 'lugar', 'direccion', 'fecha_programada',
        'hora_programada', 'indicaciones', 'nombre_recibe', 'telefono_contacto',
        'estado', 'historial',
    ];

    protected $casts = ['fecha_programada' => 'date'];

    public const ESTADOS = [
        'Pendiente de programar', 'Entrega programada', 'Confirmada', 'En camino',
        'Entregada', 'No se presentó la clienta', 'Reprogramada', 'Cancelada',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function puntoEncuentro(): BelongsTo
    {
        return $this->belongsTo(PuntoEncuentro::class);
    }
}