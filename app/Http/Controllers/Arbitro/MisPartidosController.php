<?php

namespace App\Http\Controllers\Arbitro;

use App\Http\Controllers\Controller;
use App\Models\Designacion;
use App\Models\Tarifa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MisPartidosController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();


        if ($user->rol !== 'arbitro') {
            return redirect()->route('dashboard');
        }

        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);

        $tarifas = Tarifa::all();


        $designaciones = Designacion::with('partido')
            ->where('user_id', $user->id)
            ->whereHas('partido', function ($q) use ($mes, $anio) {
                $q->whereMonth('fecha', $mes)->whereYear('fecha', $anio);
            })
            ->get()
            ->map(function ($desig) use ($tarifas) {
                $partido = $desig->partido;

                $tarifaAplicada = $tarifas->first(function ($t) use ($partido) {
                    return strtoupper($t->categoria) === strtoupper($partido->categoria) &&
                        strtoupper($t->disciplina) === strtoupper($partido->disciplina);
                });

                $monto = 0;

                if ($tarifaAplicada && $desig->estado_confirmacion === 'confirmado') {
                    $monto = ($desig->funcion === 'ARBITRO PRINCIPAL')
                        ? $tarifaAplicada->monto_principal
                        : $tarifaAplicada->monto_asistente;
                }

                return [
                    'id' => $desig->id,
                    'fecha' => $partido->fecha,
                    'hora' => \Carbon\Carbon::parse($partido->hora_inicio)->format('H:i'),
                    'partido' => $partido->equipo_local . ' vs ' . $partido->equipo_visitante,
                    'categoria' => $partido->categoria,
                    'disciplina' => $partido->disciplina,
                    'cancha' => $partido->cancha,
                    'funcion' => $desig->funcion,
                    'estado_confirmacion' => $desig->estado_confirmacion,
                    'monto' => $monto
                ];
            })->sortByDesc('fecha')->values();


        $total_ganado = $designaciones->sum('monto');

        return Inertia::render('Arbitro/MisPartidos/Index', [
            'partidos' => $designaciones,
            'resumen' => [
                'total_ganado' => $total_ganado,
                'a_pagar' => $total_ganado * 0.10,
                'cantidad' => $designaciones->count(),
                'confirmados' => $designaciones->where('estado_confirmacion', 'confirmado')->count()
            ],
            'filtros' => [
                'mes' => (int) $mes,
                'anio' => (int) $anio
            ]
        ]);
    }
}