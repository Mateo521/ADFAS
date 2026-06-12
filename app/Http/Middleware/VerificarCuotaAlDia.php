<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Pago;
use App\Models\Ajuste;
use App\Models\Designacion;
use Carbon\Carbon;

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


        $fechaLimite = $diaActual > $diaLimite ? now()->startOfMonth() : now()->subMonth()->startOfMonth();


        $designacionesHistoricas = Designacion::where('user_id', $user->id)
            ->where('estado_confirmacion', 'confirmado')
            ->whereHas('partido', function ($q) use ($fechaLimite) {
                $q->where('fecha', '<', $fechaLimite);
            })
            ->get();


        if ($designacionesHistoricas->isEmpty()) {
            return $next($request);
        }


        $mesesTrabajados = $designacionesHistoricas->groupBy(function ($desig) {
            return Carbon::parse($desig->partido->fecha)->format('Y-m');
        });


        foreach ($mesesTrabajados as $mesAnio => $designaciones) {
            [$anio, $mes] = explode('-', $mesAnio);

            $pago = Pago::where('user_id', $user->id)
                ->where('anio', $anio)
                ->where('mes', $mes)
                ->where('estado', 'pagado')
                ->first();


            if (!$pago) {
                if (!$request->routeIs('pagos.*')) {
                    return redirect()->route('pagos.requerido');
                }
                return $next($request);
            }
        }

        return $next($request);
    }
}