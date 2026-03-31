<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
     
        if (auth()->check() && auth()->user()->rol !== 'admin') {
            abort(403, 'No tenés permisos de administrador para acceder a esta sección.');
            
        }

        return $next($request);
    }
}