<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pago;
use App\Models\Tarifa;
use App\Models\Designacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);

        // Traemos las tarifas y las guardamos en memoria para no consultar la BD por cada partido
        $tarifas = Tarifa::all();

        $arbitros = User::where('rol', 'arbitro')
            ->orderBy('apellido', 'asc')
            ->get()
            ->map(function ($user) use ($mes, $anio, $tarifas) {

                // 1. Buscamos si ya se le generó un pago pagado para este mes
                $pago = Pago::where('user_id', $user->id)
                    ->where('mes', $mes)
                    ->where('anio', $anio)
                    ->where('estado', 'pagado')
                    ->first();

                // 2. Si NO ha pagado, le calculamos la deuda "en vivo"
                if (!$pago) {
                    // Buscamos sus partidos confirmados del mes seleccionado
                    $designaciones = Designacion::with('partido')
                        ->where('user_id', $user->id)
                        ->where('estado_confirmacion', 'confirmado')
                        ->whereHas('partido', function ($q) use ($mes, $anio) {
                        $q->whereMonth('fecha', $mes)->whereYear('fecha', $anio);
                    })->get();

                    $total_ganado = 0;
                    $ticket = [];

                    foreach ($designaciones as $desig) {
                        $partido = $desig->partido;

                        // Buscamos la tarifa que coincida con la categoría y disciplina del partido
                        $tarifaAplicada = $tarifas->first(function ($t) use ($partido) {
                            return strtoupper($t->categoria) === strtoupper($partido->categoria) &&
                                strtoupper($t->disciplina) === strtoupper($partido->disciplina);
                        });

                        $monto_ganado = 0;

                        // Si encontramos el precio de la categoría, vemos qué rol cumplió
                        if ($tarifaAplicada) {
                            if ($desig->funcion === 'ARBITRO PRINCIPAL') {
                                $monto_ganado = $tarifaAplicada->monto_principal;
                            } else {
                                $monto_ganado = $tarifaAplicada->monto_asistente;
                            }
                        }

                        $total_ganado += $monto_ganado;

                        // Armamos la línea del recibo
                        $ticket[] = [
                            'fecha' => $partido->fecha,
                            'partido' => $partido->equipo_local . ' vs ' . $partido->equipo_visitante,
                            'categoria' => $partido->categoria,
                            'funcion' => $desig->funcion,
                            'monto' => $monto_ganado
                        ];
                    }

                    // Calculamos el 10% para la asociación
                    $monto_a_pagar = $total_ganado * 0.10;

                    $user->total_ganado = $total_ganado;
                    $user->deuda_calculada = $monto_a_pagar;
                    $user->ticket_preview = $ticket;
                    $user->esta_al_dia = false;
                } else {
                    // Si ya pagó, mostramos los datos guardados en la BD
                    $user->pago_info = $pago;
                    $user->esta_al_dia = true;
                    $user->total_ganado = $pago->total_ganado;
                    $user->deuda_calculada = $pago->monto;
                    $user->ticket_preview = $pago->detalle_ticket;
                }

                return $user;
            });

        return Inertia::render('Admin/Pagos/Index', [
            'arbitros' => $arbitros,
            'filtros' => [
                'mes' => (int) $mes,
                'anio' => (int) $anio
            ]
        ]);
    }


    public function pantallaBloqueo(Request $request)
    {
        $user = auth()->user();

        if ($user->rol !== 'arbitro') {
            return redirect()->route('dashboard');
        }


        $mesFacturacion = now()->subMonth()->month;
        $anioFacturacion = now()->subMonth()->year;

        $yaPago = \App\Models\Pago::where('user_id', $user->id)
            ->where('mes', $mesFacturacion)
            ->where('anio', $anioFacturacion)
            ->where('estado', 'pagado')
            ->exists();

        if ($yaPago) {
            return redirect()->route('dashboard');
        }

        $tarifas = Tarifa::all();
        $designaciones = Designacion::with('partido')
            ->where('user_id', $user->id)
            ->where('estado_confirmacion', 'confirmado')
            ->whereHas('partido', function ($q) use ($mesFacturacion, $anioFacturacion) {
                $q->whereMonth('fecha', $mesFacturacion)->whereYear('fecha', $anioFacturacion);
            })->get();

        $total_ganado = 0;
        $ticket = [];

        foreach ($designaciones as $desig) {
            $partido = $desig->partido;
            $tarifaAplicada = $tarifas->first(function ($t) use ($partido) {
                return strtoupper($t->categoria) === strtoupper($partido->categoria) &&
                    strtoupper($t->disciplina) === strtoupper($partido->disciplina);
            });

            $monto_ganado = 0;
            if ($tarifaAplicada) {
                $monto_ganado = ($desig->funcion === 'ARBITRO PRINCIPAL')
                    ? $tarifaAplicada->monto_principal
                    : $tarifaAplicada->monto_asistente;
            }

            $total_ganado += $monto_ganado;
            $ticket[] = [
                'fecha' => $partido->fecha,
                'cancha' => $partido->cancha,
                'partido' => $partido->equipo_local . ' vs ' . $partido->equipo_visitante,
                'categoria' => $partido->categoria,
                'funcion' => $desig->funcion,
                'monto' => $monto_ganado
            ];
        }


        $ajustes = \App\Models\Ajuste::first();
        $dia_vencimiento = $ajustes ? $ajustes->cuota_dia_vencimiento : 10;

        return Inertia::render('Pagos/Requerido', [
            'ticket' => $ticket,
            'total_ganado' => $total_ganado,
            'monto_a_pagar' => $total_ganado * 0.10, // 10%
            'mes_nombre' => now()->subMonth()->translatedFormat('F'),
            'dia_vencimiento' => $dia_vencimiento
        ]);
    }

    public function registrarManual(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'mes' => 'required|integer',
            'anio' => 'required|integer'
        ]);

        $mes = $request->mes;
        $anio = $request->anio;
        $user_id = $request->user_id;


        $tarifas = Tarifa::all();
        $designaciones = Designacion::with('partido')
            ->where('user_id', $user_id)
            ->where('estado_confirmacion', 'confirmado')
            ->whereHas('partido', function ($q) use ($mes, $anio) {
                $q->whereMonth('fecha', $mes)->whereYear('fecha', $anio);
            })->get();

        $total_ganado = 0;
        $ticket = [];

        foreach ($designaciones as $desig) {
            $partido = $desig->partido;
            $tarifaAplicada = $tarifas->first(function ($t) use ($partido) {
                return strtoupper($t->categoria) === strtoupper($partido->categoria) &&
                    strtoupper($t->disciplina) === strtoupper($partido->disciplina);
            });

            $monto_ganado = 0;
            if ($tarifaAplicada) {
                $monto_ganado = ($desig->funcion === 'ARBITRO PRINCIPAL')
                    ? $tarifaAplicada->monto_principal
                    : $tarifaAplicada->monto_asistente;
            }

            $total_ganado += $monto_ganado;
            $ticket[] = [
                'fecha' => $partido->fecha,
                'partido' => $partido->equipo_local . ' vs ' . $partido->equipo_visitante,
                'categoria' => $partido->categoria,
                'funcion' => $desig->funcion,
                'monto' => $monto_ganado
            ];
        }


        Pago::updateOrCreate(
            [
                'user_id' => $user_id,
                'mes' => $mes,
                'anio' => $anio,
            ],
            [
                'total_ganado' => $total_ganado,
                'detalle_ticket' => $ticket,
                'monto' => $total_ganado * 0.10,
                'estado' => 'pagado',
                'metodo_pago' => 'efectivo / manual',
                'fecha_pago' => now()
            ]
        );

        return back()->with('success', 'Pago registrado y ticket generado.');
    }
}