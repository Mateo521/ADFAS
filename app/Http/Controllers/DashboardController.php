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
            
            $pendientes = \App\Models\Designacion::with(['user', 'partido'])
                ->where('estado_confirmacion', 'pendiente')
                ->get();

            $rechazadas = \App\Models\Designacion::with(['user', 'partido'])
                ->where('estado_confirmacion', 'rechazado')
                ->get();

         
            $licenciasPendientes = \App\Models\Licencia::with('user')
                ->where('estado', 'pendiente')
                ->get();

            return Inertia::render('Dashboard', [
                'esAdmin' => true,
                'statsAdmin' => [
                    'totalPartidos' => \App\Models\Partido::count(),
                    'confirmadas' => \App\Models\Designacion::where('estado_confirmacion', 'confirmado')->count(),
                    'pendientes' => $pendientes->count(),
                    'rechazadas' => $rechazadas->count(),
                ],
                'listaPendientes' => $pendientes,
                'listaRechazadas' => $rechazadas,
                'licenciasPendientes' => $licenciasPendientes,  
            ]);
        }


 
        $designaciones = \App\Models\Designacion::with(['partido.designaciones.user'])  
            ->select('designaciones.*')
            ->join('partidos', 'partidos.id', '=', 'designaciones.partido_id')
            ->where('designaciones.user_id', $user->id)
            ->where('partidos.fecha', '>=', now()->toDateString())
            ->orderByRaw("FIELD(designaciones.estado_confirmacion, 'pendiente', 'confirmado', 'rechazado')") 
            ->orderBy('partidos.fecha', 'asc')
            ->orderBy('partidos.hora_inicio', 'asc')
            ->get(); 

        $admin = \App\Models\User::where('rol', 'admin')->first();
        $telefonoAdmin = $admin && $admin->telefono ? $admin->telefono : '5492664000000'; 

        $partidosConfirmados = \App\Models\Designacion::where('user_id', $user->id)
            ->where('estado_confirmacion', 'confirmado')
            ->count();
            
        $totalDesignaciones = \App\Models\Designacion::where('user_id', $user->id)->count();

        $ultimasNoticias = \App\Models\Noticia::latest()->take(3)->get();

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