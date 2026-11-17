<?php

namespace App\Http\Controllers;

use App\Models\Entrega;
use App\Models\PuntoEncuentro;
use App\Models\Venta;
use Illuminate\Http\Request;

class EntregaController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->get('view', 'week');
        $fecha = $request->get('fecha', date('Y-m-d'));
        $estado = $request->get('estado');
        $punto = $request->get('punto');
        $q = $request->get('q');

        $base = \Carbon\Carbon::parse($fecha);
        if ($view === 'week') {
            $start = $base->copy()->startOfWeek();
            $end = $base->copy()->endOfWeek();
        } elseif ($view === 'month') {
            $start = $base->copy()->startOfMonth();
            $end = $base->copy()->endOfMonth();
        } else {
            $start = $base->copy()->startOfDay();
            $end = $base->copy()->endOfDay();
            $view = 'day';
        }

        $ventas = Venta::with(['clienta', 'entrega.puntoEncuentro', 'detalles.producto'])
            ->when($q, fn ($x) => $x->where(function ($w) use ($q) {
                $w->where('folio', 'like', "%$q%")
                  ->orWhereHas('clienta', fn ($c) => $c->where('nombre', 'like', "%$q%")->orWhere('telefono', 'like', "%$q%"))
                  ->orWhere('notas', 'like', "%$q%");
            }))
            ->get()
            ->filter(function ($v) use ($start, $end) {
                $d = $v->entrega && $v->entrega->fecha_programada ? $v->entrega->fecha_programada : $v->fecha;
                return $d && $d->between($start, $end);
            })
            ->sortBy(fn($v) => ($v->entrega && $v->entrega->fecha_programada ? $v->entrega->fecha_programada : $v->fecha)->format('Y-m-d') . ' ' . ($v->entrega->hora_programada ?? ''));

        $puntos = PuntoEncuentro::where('activo', true)->orderBy('nombre')->get();
        $estados = Entrega::ESTADOS;

        return view('entregas.index', compact('ventas', 'view', 'fecha', 'estado', 'punto', 'q', 'puntos', 'estados', 'start', 'end'));
    }

    public function create(Request $request)
    {
        $ventas = Venta::with('clienta')->whereDoesntHave('entrega')->orderByDesc('id')->get();
        $puntos = PuntoEncuentro::where('activo', true)->orderBy('nombre')->get();
        $ventaId = $request->get('venta_id');
        return view('entregas.create', compact('ventas', 'puntos', 'ventaId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'punto_encuentro_id' => 'nullable|exists:puntos_encuentro,id',
            'lugar' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'fecha_programada' => 'nullable|date',
            'hora_programada' => 'nullable',
            'indicaciones' => 'nullable|string',
            'nombre_recibe' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:20',
            'estado' => 'required|string',
        ]);

        // conflict check
        if (!empty($data['fecha_programada']) && !empty($data['hora_programada'])) {
            $conflict = Entrega::where('fecha_programada', $data['fecha_programada'])
                ->where('hora_programada', $data['hora_programada'])->exists();
            if ($conflict) {
                return redirect()->back()->withInput()->with('error', 'Ya existe una entrega programada en esa misma fecha y hora.');
            }
        }

        if (empty($data['lugar']) && !empty($data['punto_encuentro_id'])) {
            $pe = PuntoEncuentro::find($data['punto_encuentro_id']);
            $data['lugar'] = $pe?->nombre;
            $data['direccion'] = $data['direccion'] ?: $pe?->direccion;
        }

        $data['historial'] = json_encode([['fecha' => now()->toDateTimeString(), 'accion' => 'Creada', 'estado' => $data['estado']]]);

        Entrega::create($data);
        return redirect()->route('entregas.index')->with('success', 'Entrega registrada.');
    }

    public function show(Entrega $entrega)
    {
        $entrega->load(['venta.clienta', 'venta.detalles.producto', 'puntoEncuentro']);
        return view('entregas.show', compact('entrega'));
    }

    public function edit(Entrega $entrega)
    {
        $ventas = Venta::with('clienta')->get();
        $puntos = PuntoEncuentro::where('activo', true)->orderBy('nombre')->get();
        return view('entregas.edit', compact('entrega', 'ventas', 'puntos'));
    }

    public function update(Request $request, Entrega $entrega)
    {
        $data = $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'punto_encuentro_id' => 'nullable|exists:puntos_encuentro,id',
            'lugar' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'fecha_programada' => 'nullable|date',
            'hora_programada' => 'nullable',
            'indicaciones' => 'nullable|string',
            'nombre_recibe' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:20',
            'estado' => 'required|string',
        ]);

        if (!empty($data['fecha_programada']) && !empty($data['hora_programada'])) {
            $conflict = Entrega::where('fecha_programada', $data['fecha_programada'])
                ->where('hora_programada', $data['hora_programada'])
                ->where('id', '!=', $entrega->id)->exists();
            if ($conflict) {
                return redirect()->back()->withInput()->with('error', 'Ya existe una entrega programada en esa misma fecha y hora.');
            }
        }

        if (empty($data['lugar']) && !empty($data['punto_encuentro_id'])) {
            $pe = PuntoEncuentro::find($data['punto_encuentro_id']);
            $data['lugar'] = $pe?->nombre;
            $data['direccion'] = $data['direccion'] ?: $pe?->direccion;
        }

        $hist = $entrega->historial ? json_decode($entrega->historial, true) : [];
        $hist[] = ['fecha' => now()->toDateTimeString(), 'accion' => 'Actualizada', 'estado' => $data['estado']];
        $data['historial'] = json_encode($hist);

        $entrega->update($data);
        return redirect()->route('entregas.index')->with('success', 'Entrega actualizada.');
    }

    public function destroy(Entrega $entrega)
    {
        $entrega->delete();
        return redirect()->route('entregas.index')->with('success', 'Entrega eliminada.');
    }
}