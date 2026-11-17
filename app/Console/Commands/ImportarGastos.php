<?php

namespace App\Console\Commands;

use App\Models\Gasto;
use App\Models\GastoCategoria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportarGastos extends Command
{
    protected $signature = 'importar:gastos {--dry-run}';
    protected $description = 'Importa gastos históricos desde CSV de Notion';

    private string $csv = '/Users/eduardoaguirre/Downloads/2dad07f5-49ff-48aa-b333-33563b1ec0cb%2FExportBlock-0459c7aa-0428-4dd8-ab77-9d8d87ffc400/VENTAS/Gastos 2e5c1c41ce2f4404a590d403c0c4c5f8.csv';

    public function handle(): int
    {
        $dry = $this->option('dry-run');
        $rows = $this->readCsv($this->csv);
        $this->info('Gastos encontrados: ' . count($rows));

        if ($dry) {
            $this->info('Dry run: no se importó nada.');
            return 0;
        }

        $imported = 0; $skipped = 0;
        DB::transaction(function () use ($rows, &$imported, &$skipped) {
            foreach ($rows as $r) {
                $concepto = trim($r['Concepto'] ?? '');
                $fecha = $this->parseDate($r['Fecha'] ?? '');
                $tipo = trim($r['Tipo de gasto'] ?? '');
                $producto = trim($r['Producto o insumo'] ?? '');
                $proveedor = trim($r['Proveedor / canal'] ?? '');
                $monto = $this->num($r['Importe total (MXN)'] ?? 0);
                $metodo = trim($r['Método de pago'] ?? '');
                $estado = trim($r['Estado'] ?? '');
                $cantidad = trim($r['Cantidad'] ?? '');
                $costoUnit = trim($r['Costo unitario (MXN)'] ?? '');
                $notasOrig = trim($r['Notas'] ?? '');
                $orden = trim($r['Orden / comprobante'] ?? '');
                $responsable = trim($r['Responsable'] ?? '');

                if ($metodo === '') $metodo = 'Efectivo';
                if ($estado === '') $estado = 'Pagado';

                // idempotency: same concepto+fecha+monto
                $exists = Gasto::where('concepto', $concepto)
                    ->where('fecha', $fecha)
                    ->where('monto', $monto)
                    ->exists();
                if ($exists) { $skipped++; continue; }

                $categoriaId = null;
                if ($tipo !== '') {
                    $cat = GastoCategoria::firstOrCreate(['nombre' => $tipo], ['activo' => true]);
                    $categoriaId = $cat->id;
                }

                $notas = 'Importado desde Notion.';
                if ($producto !== '') $notas .= " Producto/insumo: $producto.";
                if ($cantidad !== '') $notas .= " Cantidad: $cantidad.";
                if ($costoUnit !== '') $notas .= " Costo unitario: $costoUnit.";
                if ($proveedor !== '') $notas .= " Proveedor/canal: $proveedor.";
                if ($orden !== '') $notas .= " Orden/comprobante: $orden.";
                if ($responsable !== '') $notas .= " Responsable: $responsable.";
                if ($notasOrig !== '') $notas .= " Notas: $notasOrig.";

                Gasto::create([
                    'fecha' => $fecha,
                    'tipo_gasto' => $tipo !== '' ? $tipo : 'Otros',
                    'concepto' => $concepto,
                    'descripcion' => $producto !== '' ? $producto : null,
                    'monto' => $monto,
                    'metodo_pago' => $metodo,
                    'linea_negocio' => 'Generales',
                    'estado' => $estado,
                    'notas' => $notas,
                    'categoria_gasto_id' => $categoriaId,
                    'comprobante' => $orden !== '' ? $orden : null,
                ]);
                $imported++;
            }
        });

        $this->info("Gastos importados: $imported, omitidos por duplicado: $skipped");
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
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
        if ($v === '') return 0.0;
        return (float) $v;
    }

    private function parseDate($raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') return null;
        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $raw, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        return null;
    }
}