<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Designacion;
use Illuminate\Support\Facades\Auth;

class DesignacionController extends Controller
{
    public function responder(Request $request, Designacion $designacion)
    {
        // Seguridad: Verificamos que esta designación realmente le pertenezca a quien hizo clic
        if ($designacion->user_id !== Auth::id()) {
            abort(403, 'Acceso denegado. Este no es tu partido.');
        }

        // Validamos que la respuesta sea solo lo permitido
        $request->validate([
            'estado' => 'required|in:confirmado,rechazado'
        ]);

        // Guardamos el nuevo estado en la base de datos
        $designacion->update([
            'estado_confirmacion' => $request->estado
        ]);

        // Volvemos a la misma página (Inertia hará el resto de forma invisible)
        return back();
    }
}