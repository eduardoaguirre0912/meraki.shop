<?php

namespace App\Console\Commands;

use App\Models\Clienta;
use App\Models\DetalleVenta;
use App\Models\Entrega;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportarNotion extends Command
{
    protected $signature = 'importar:notion {--dry-run}';
    protected $description = 'Importa productos y ventas históricas desde CSVs de Notion';

    private string $downloads = '/Users/eduardoaguirre/Downloads';
    private string $ventasDir = '/Users/eduardoaguirre/Downloads/2dad07f5-49ff-48aa-b333-33563b1ec0cb%2FExportBlock-0459c7aa-0428-4dd8-ab77-9d8d87ffc400/VENTAS';

    public function handle(): int
    {
        $dry = $this->option('dry-run');

        // Products
        $prodCsv = $this->downloads . '/inventario.csv';
        $prodRows = $this->readCsv($prodCsv);
        $this->info('Productos encontrados: ' . count($prodRows));

        // Sales
        $ventasCsv = $this->ventasDir . '/Ventas 3dc1b84c5eb7806aa916d0f525323b80.csv';
        $ventaRows = $this->readCsv($ventasCsv);
        $this->info('Ventas encontradas: ' . count($ventaRows));

        if ($dry) {
            $this->info('Dry run: no se importó nada.');
            return 0;
        }

        DB::transaction(function () use ($prodRows, $ventaRows) {
            // Import products
            $prodImported = 0; $prodSkipped = 0;
            foreach ($prodRows as $r) {
                $sku = trim($r['SKU'] ?? '');
                if ($sku === '') continue;
                if (Producto::where('sku', $sku)->exists()) { $prodSkipped++; continue; }
                Producto::create([
                    'sku' => $sku,
                    'linea_negocio' => trim($r['Linea'] ?? 'Otros'),
                    'nombre' => trim($r['Nombre'] ?? ''),
                    'categoria' => trim($r['Categoria'] ?? '') ?: null,
                    'marca' => (trim($r['Marca'] ?? '') === 'NA' || trim($r['Marca'] ?? '') === '') ? null : trim($r['Marca']),
                    'variante' => trim($r['Variante'] ?? '') ?: null,
                    'stock_actual' => (int) ($r['Stock'] ?? 0),
                    'stock_minimo' => (int) ($r['StockMin'] ?? 0),
                    'costo_unitario' => $this->num($r['Costo'] ?? 0),
                    'precio_venta' => $this->num($r['Precio'] ?? 0),
                    'proveedor' => trim($r['Proveedor'] ?? '') ?: null,
                ]);
                $prodImported++;
            }
            $this->info("Productos importados: $prodImported, omitidos por existir: $prodSkipped");

            // Import sales
            $vImported = 0; $vSkipped = 0; $clientasNuevas = 0; $detalles = 0; $entregas = 0;
            foreach ($ventaRows as $r) {
                $clientName = trim($r['ID / Cliente'] ?? '');
                $phone = $this->normalizePhone($r['Contacto / Tel'] ?? '');
                $clienta = null;
                if ($phone) {
                    $clienta = Clienta::where('telefono', $phone)->first();
                    if (!$clienta) {
                        $clienta = Clienta::create(['telefono' => $phone, 'nombre' => $clientName ?: 'Sin nombre']);
                        $clientasNuevas++;
                    } elseif ($clientName && $clienta->nombre !== $clientName) {
                        // keep original name if empty
                        if (empty($clienta->nombre)) $clienta->update(['nombre' => $clientName]);
                    }
                } else {
                    // create clienta without phone? telefono nullable now
                    if ($clientName) {
                        $clienta = Clienta::create(['telefono' => null, 'nombre' => $clientName]);
                        $clientasNuevas++;
                    }
                }

                $fecha = $this->parseDate($r['Fecha Entrega'] ?? '');
                $costo = $this->num($r['Costo'] ?? 0);
                $precio = $this->num($r['Precio Venta'] ?? 0);
                $ganancia = $this->num($r['Ganancia'] ?? 0);
                $pagado = $this->num($r['Pagado'] ?? 0);
                $deuda = $this->num($r['Deuda'] ?? 0);
                $metodo = trim($r['Método de Pago'] ?? '');
                $estadoPago = trim($r['Estado de Pago'] ?? '');
                $estadoPago = str_replace(['🟢','🟡','🔴'], '', $estadoPago);
                $estadoPago = trim($estadoPago);
                if ($estadoPago === '') {
                    if ($deuda <= 0 && $precio > 0) $estadoPago = 'Liquidado';
                    elseif ($pagado > 0) $estadoPago = 'Parcial';
                    else $estadoPago = 'Pendiente';
                }
                if ($metodo === '') $metodo = 'Efectivo';

                $lugar = trim($r['Lugar de entrega'] ?? '');
                $notasOrig = trim($r['Notas'] ?? '');
                $modelo = trim($r['Modelo / Color'] ?? '');
                $talla = trim($r['Talla'] ?? '');
                $estado = trim($r['Estado'] ?? '');
                $llegada = trim($r['Llegada ML'] ?? '');
                $rastreo = trim($r['Link / Rastreo ML'] ?? '');

                $notas = "Importado desde Notion. ID original: " . trim($r['ID'] ?? '') . ".";
                if ($modelo) $notas .= " Producto(s): $modelo.";
                if ($talla) $notas .= " Talla(s): $talla.";
                if ($estado) $notas .= " Estado: $estado.";
                if ($llegada) $notas .= " Llegada ML: $llegada.";
                if ($rastreo) $notas .= " Rastreo: $rastreo.";
                if ($notasOrig) $notas .= " Notas originales: $notasOrig.";

                // idempotency check
                if ($clienta) {
                    $exists = Venta::where('clienta_id', $clienta->id)
                        ->where('fecha', $fecha)
                        ->where('total_pedido', $precio)
                        ->where('notas', 'like', '%ID original: ' . trim($r['ID'] ?? '') . '.%')
                        ->exists();
                    if ($exists) { $vSkipped++; continue; }
                }

                $venta = Venta::create([
                    'clienta_id' => $clienta?->id,
                    'fecha' => $fecha,
                    'total_pedido' => $precio,
                    'costo_total' => $costo,
                    'utilidad_bruta' => $ganancia,
                    'anticipo_pagado' => $pagado,
                    'saldo_pendiente' => $deuda,
                    'metodo_pago' => $metodo,
                    'estado_pago' => $estadoPago,
                    'notas' => $notas,
                ]);
                $vImported++;

                // detalle
                $matched = $this->matchProducto($modelo);
                if ($matched) {
                    DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $matched->id,
                        'cantidad' => 1,
                        'precio_unitario' => $precio,
                        'costo_unitario' => $costo,
                        'subtotal' => $precio,
                        'utilidad_linea' => $ganancia,
                    ]);
                    $detalles++;
                } else {
                    // leave detail without product (product_id nullable)
                    DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'producto_id' => null,
                        'cantidad' => 1,
                        'precio_unitario' => $precio,
                        'costo_unitario' => $costo,
                        'subtotal' => $precio,
                        'utilidad_linea' => $ganancia,
                    ]);
                    $detalles++;
                }

                // entrega
                $fechaEntrega = $this->parseDate($r['Fecha Entrega'] ?? '');
                $hora = $this->parseTime($r['Fecha Entrega'] ?? '');
                if ($fechaEntrega || $lugar) {
                    Entrega::create([
                        'venta_id' => $venta->id,
                        'lugar' => $lugar ?: null,
                        'direccion' => null,
                        'fecha_programada' => $fechaEntrega,
                        'hora_programada' => $hora,
                        'indicaciones' => $rastreo ?: ($notasOrig ?: null),
                        'nombre_recibe' => $clientName ?: null,
                        'telefono_contacto' => $phone,
                        'estado' => 'Entrega programada',
                        'historial' => json_encode([['fecha' => now()->toDateTimeString(), 'accion' => 'Importada desde Notion', 'estado' => 'Entrega programada']]),
                    ]);
                    $entregas++;
                }
            }

            $this->info("Clientas nuevas: $clientasNuevas");
            $this->info("Ventas importadas: $vImported, omitidas por duplicado: $vSkipped");
            $this->info("Detalles de venta creados: $detalles");
            $this->info("Entregas creadas: $entregas");
        });

        return 0;
    }

    private function readCsv(string $path): array
    {
        if (!file_exists($path)) {
            $this->error("No existe: $path");
            return [];
        }
        $rows = [];
        if (($h = fopen($path, 'r')) !== false) {
            // handle BOM
            $bom = fread($h, 3);
            if ($bom !== "\xEF\xBB\xBF") rewind($h);
            $header = fgetcsv($h);
            while (($data = fgetcsv($h)) !== false) {
                if (count($data) === 1 && $data[0] === null) continue;
                $row = [];
                foreach ($header as $i => $col) {
                    $row[trim($col)] = $data[$i] ?? '';
                }
                $rows[] = $row;
            }
            fclose($h);
        }
        return $rows;
    }

    private function num($v): float
    {
        $v = str_replace(['MXN', ' ', "\xc2\xa0"], '', (string) $v);
        $v = str_replace('.', '', $v); // remove thousands? values use comma decimal
        // Actually values like 229,00 -> replace comma with dot
        $v = str_replace(',', '.', $v);
        // But if we removed dots as thousands, and decimal comma, okay. However 1,234.56 not present.
        if ($v === '' ) return 0.0;
        return (float) $v;
    }

    private function normalizePhone($raw): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $raw);
        if (strlen($digits) === 12 && str_starts_with($digits, '52')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 10) return $digits;
        return null;
    }

    private function parseDate($raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') return null;
        // dd/mm/yyyy ...
        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $raw, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        return null;
    }

    private function parseTime($raw): ?string
    {
        $raw = trim($raw);
        if (preg_match('/(\d{1,2}):(\d{2})/', $raw, $m)) {
            return sprintf('%02d:%02d:00', $m[1], $m[2]);
        }
        return null;
    }

    private function matchProducto(string $modelo): ?Producto
    {
        $modeloNorm = $this->normalize($modelo);
        if ($modeloNorm === '') return null;
        $matches = [];
        foreach (Producto::all() as $p) {
            $nameNorm = $this->normalize($p->nombre . ' ' . ($p->variante ?? ''));
            // check if product name/variant is contained in modelo
            if ($nameNorm !== '' && str_contains($modeloNorm, $nameNorm)) {
                $matches[] = $p;
            }
        }
        if (count($matches) === 1) return $matches[0];
        return null;
    }

    private function normalize(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = str_replace(['á','é','í','ó','ú','ñ'], ['a','e','i','o','u','n'], $s);
        $s = preg_replace('/[^a-z0-9 ]/', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }
}