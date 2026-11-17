<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $fillable = [
        'sku', 'linea_negocio', 'nombre', 'categoria', 'marca', 'variante',
        'stock_actual', 'stock_minimo', 'costo_unitario', 'precio_venta',
        'proveedor', 'notas', 'es_combo',
    ];

    protected $casts = [
        'es_combo' => 'boolean',
        'costo_unitario' => 'decimal:2',
        'precio_venta' => 'decimal:2',
    ];

    public static function lineaPrefix(string $linea): string
    {
        return match ($linea) {
            'Maquillaje' => 'MAQ',
            'Uniformes' => 'UNI',
            'Accesorios' => 'ACC',
            default => 'OTR',
        };
    }

    protected static function booted(): void
    {
        static::creating(function (Producto $producto) {
            if (empty($producto->sku)) {
                $lineaPrefix = self::lineaPrefix($producto->linea_negocio);
                $cat = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string) $producto->categoria), 0, 4));
                if ($cat === '') {
                    $cat = 'GENE';
                }
                $prefix = $lineaPrefix . '-' . $cat . '-';
                $last = self::where('sku', 'like', $prefix . '%')->orderByDesc('id')->value('sku');
                $num = 1;
                if ($last && preg_match('/-(\d+)$/', $last, $m)) {
                    $num = (int) $m[1] + 1;
                }
                $producto->sku = $prefix . str_pad($num, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function entradas(): HasMany
    {
        return $this->hasMany(EntradaInventario::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(ProductoFoto::class)->orderBy('principal', 'desc')->orderBy('orden');
    }

    public function componentes(): HasMany
    {
        return $this->hasMany(ComboComponente::class, 'combo_producto_id');
    }

    public function esComponenteDe()
    {
        return $this->hasMany(ComboComponente::class, 'producto_id');
    }

    public function fotoPrincipal(): ?string
    {
        $f = $this->fotos->firstWhere('principal', true) ?? $this->fotos->first();
        return $f?->path;
    }
}