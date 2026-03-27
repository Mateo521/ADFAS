<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Designacion;
use Illuminate\Support\Facades\Auth;

class DesignacionController extends Controller
{
    public function responder(Request $request, Designacion $designacion)
    {
        
        if ($designacion->user_id !== Auth::id()) {
            abort(403, 'Acceso denegado. Este no es tu partido.');
        }

       
        $request->validate([
            'estado' => 'required|in:confirmado,rechazado'
        ]);

       
        $designacion->update([
            'estado_confirmacion' => $request->estado
        ]);

        
        return back();
    }
}