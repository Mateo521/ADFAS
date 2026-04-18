<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsEspectador
{
    public function handle(Request $request, Closure $next): Response
    {
       
        if (auth()->check() && in_array(auth()->user()->rol, ['admin', 'espectador'])) {
            return $next($request);
        }

        abort(403, 'Acceso denegado. Esta sección es exclusiva para visualizadores y administradores.');
    }
}