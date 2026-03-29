<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAprobado
{
    public function handle(Request $request, Closure $next)
    {
       
        if (Auth::check() && Auth::user()->rol !== 'admin' && Auth::user()->estado !== 'aprobado') {
            
           
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            
            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta se está revisando por la administración. Te vamos a avisar cuando se apruebe.'
            ]);
        }

        return $next($request);
    }
}