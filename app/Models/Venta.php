<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $fillable = [
        'folio', 'clienta_id', 'fecha', 'total_pedido', 'costo_total',
        'utilidad_bruta', 'anticipo_pagado', 'saldo_pendiente', 'metodo_pago',
        'estado_pago', 'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total_pedido' => 'decimal:2',
        'costo_total' => 'decimal:2',
        'utilidad_bruta' => 'decimal:2',
        'anticipo_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Venta $venta) {
            if (empty($venta->folio)) {
                $last = self::orderByDesc('id')->value('folio');
                $num = 1;
                if ($last && preg_match('/(\d+)$/', $last, $m)) {
                    $num = (int) $m[1] + 1;
                }
                $venta->folio = 'V-' . str_pad($num, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function clienta(): BelongsTo
    {
        return $this->belongsTo(Clienta::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function entrega(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Entrega::class);
    }
}