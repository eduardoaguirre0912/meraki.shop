<?php

namespace App\Console\Commands;

use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RelacionarVentasProductos extends Command
{
    protected $signature = 'relacionar:ventas-productos {--dry-run}';
    protected $description = 'Relaciona detalles de venta con productos existentes o los crea';

    public function handle(): int
    {
        $dry = $this->option('dry-run');
        $details = DetalleVenta::whereNull('producto_id')->with('venta')->get();
        $this->info('Detalles sin producto: ' . $details->count());

        $related = 0; $created = 0; $skipped = 0;
        DB::transaction(function () use ($details, $dry, &$related, &$created, &$skipped) {
            foreach ($details as $d) {
                $venta = $d->venta;
                $modelo = '';
                if ($venta && $venta->notas && preg_match('/Producto\(s\): (.*?)\./', $venta->notas, $m)) {
                    $modelo = trim($m[1]);
                }

                $product = $this->findProduct($modelo);
                if ($product) {
                    if (!$dry) $d->update(['producto_id' => $product->id]);
                    $related++;
                    continue;
                }

                // create product if not found
                if ($modelo === '') { $skipped++; continue; }
                if ($dry) { $created++; continue; }
                $p = Producto::create([
                    'linea_negocio' => 'Uniformes',
                    'nombre' => $modelo,
                    'categoria' => null,
                    'marca' => null,
                    'variante' => null,
                    'stock_actual' => 0,
                    'stock_minimo' => 0,
                    'costo_unitario' => (float) $d->costo_unitario,
                    'precio_venta' => (float) $d->precio_unitario,
                    'proveedor' => null,
                    'notas' => 'Creado automáticamente desde importación de ventas.',
                ]);
                $d->update(['producto_id' => $p->id]);
                $created++;
            }
        });

        $this->info("Relacionados con existentes: $related");
        $this->info("Productos creados: $created");
        $this->info("Omitidos (sin descripción): $skipped");
        return 0;
    }

    private function findProduct(string $modelo): ?Producto
    {
        $modeloNorm = $this->normalize($modelo);
        if ($modeloNorm === '') return null;
        $modeloWords = array_filter(explode(' ', $modeloNorm));

        $best = null; $bestScore = 0;
        foreach (Producto::all() as $p) {
            $pname = $this->normalize($p->nombre);
            $pWords = array_filter(explode(' ', $pname));
            // all product words must be in modelo
            $allIn = true;
            foreach ($pWords as $w) {
                if (!in_array($w, $modeloWords, true)) { $allIn = false; break; }
            }
            if ($allIn && count($pWords) > 0) {
                // prefer longer name
                if (count($pWords) > $bestScore) {
                    $bestScore = count($pWords);
                    $best = $p;
                }
            }
        }
        return $best;
    }

    private function normalize(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $s);
        $s = str_replace(['+', ',', '&'], ' ', $s);
        $s = preg_replace('/[^a-z0-9 ]/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }
}