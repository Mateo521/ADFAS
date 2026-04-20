<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Pago;
use App\Models\Ajuste;
use App\Models\Designacion;

class VerificarCuotaAlDia
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();


        if (!$user || $user->rol !== 'arbitro') {
            return $next($request);
        }


        $ajustes = Ajuste::first();
        $diaLimite = $ajustes ? $ajustes->cuota_dia_vencimiento : 10;
        $diaActual = now()->day;


        if ($diaActual > $diaLimite) {


            $mesFacturacion = now()->subMonth()->month;
            $anioFacturacion = now()->subMonth()->year;


            $pago = Pago::where('user_id', $user->id)
                ->where('mes', $mesFacturacion)
                ->where('anio', $anioFacturacion)
                ->where('estado', 'pagado')
                ->first();


            if (!$pago) {


                $tienePartidos = Designacion::where('user_id', $user->id)
                    ->where('estado_confirmacion', 'confirmado')
                    ->whereHas('partido', function ($q) use ($mesFacturacion, $anioFacturacion) {
                        $q->whereMonth('fecha', $mesFacturacion)->whereYear('fecha', $anioFacturacion);
                    })->exists();


                if (!$tienePartidos) {
                    Pago::create([
                        'user_id' => $user->id,
                        'mes' => $mesFacturacion,
                        'anio' => $anioFacturacion,
                        'total_ganado' => 0,
                        'detalle_ticket' => [],
                        'monto' => 0,
                        'estado' => 'pagado',
                        'metodo_pago' => 'Exento (Sin partidos)',
                        'fecha_pago' => now()
                    ]);


                    return $next($request);
                }


                if (!$request->routeIs('pagos.*')) {
                    return redirect()->route('pagos.requerido');
                }
            }
        }

        return $next($request);
    }
}