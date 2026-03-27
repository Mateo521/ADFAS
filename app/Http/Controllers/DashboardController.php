<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Designacion;
use Illuminate\Support\Facades\Auth;
use App\Models\Noticia;
use App\Models\User;  

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        
        if ($user->rol === 'admin') {
            return Inertia::render('Dashboard', [
                'esAdmin' => true,
                'statsAdmin' => [
                    'totalPartidos' => \App\Models\Partido::count(),
                    'confirmadas' => \App\Models\Designacion::where('estado_confirmacion', 'confirmado')->count(),
                    'pendientes' => \App\Models\Designacion::where('estado_confirmacion', 'pendiente')->count(),
                    'rechazadas' => \App\Models\Designacion::where('estado_confirmacion', 'rechazado')->count(),
                ]
            ]);
        }

      
        $designaciones = Designacion::with('partido')
            ->select('designaciones.*')
            ->join('partidos', 'partidos.id', '=', 'designaciones.partido_id')
            ->where('designaciones.user_id', $user->id)
            ->where('partidos.fecha', '>=', now()->toDateString())
            ->orderBy('partidos.fecha', 'asc')
            ->orderBy('partidos.hora_inicio', 'asc')
            ->get(); 

        
        $admin = User::where('rol', 'admin')->first();
        
        $telefonoAdmin = $admin && $admin->telefono ? $admin->telefono : '5492664000000'; 

      
        $partidosConfirmados = Designacion::where('user_id', $user->id)
            ->where('estado_confirmacion', 'confirmado')
            ->count();
        $totalDesignaciones = Designacion::where('user_id', $user->id)->count();

       
        $ultimasNoticias = Noticia::latest()->take(3)->get();

        return Inertia::render('Dashboard', [
            'esAdmin' => false,
            'designaciones' => $designaciones,  
            'telefonoAdmin' => $telefonoAdmin,  
            'stats' => [
                'confirmados' => $partidosConfirmados,
                'total' => $totalDesignaciones
            ],
            'noticias' => $ultimasNoticias 
        ]);
    }

    // (Asisto / no asisto)
    public function responderDesignacion(Request $request, Designacion $designacion)
    {
        if ($designacion->user_id !== Auth::id()) {
            abort(403, 'No autorizado');
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