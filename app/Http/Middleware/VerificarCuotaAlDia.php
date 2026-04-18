<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Pago;
use App\Models\Ajuste;
class VerificarCuotaAlDia
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (!$user || $user->rol !== 'arbitro')
            return $next($request);

        
        $ajustes = Ajuste::first();
        $diaLimite = $ajustes->cuota_dia_vencimiento;

        $diaActual = now()->day;


        if ($diaActual > $diaLimite) {
            $pago = Pago::where('user_id', $user->id)
                ->where('mes', now()->month)
                ->where('anio', now()->year)
                ->where('estado', 'pagado')
                ->first();

            if (!$pago && !$request->routeIs('pagos.*')) {
                return redirect()->route('pagos.requerido');
            }
        }

        return $next($request);
    }
}