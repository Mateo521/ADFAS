<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Designacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ArbitroController extends Controller
{
   
   public function index(Request $request)
    {
       
        $query = User::where('rol', 'arbitro')->where('estado', 'aprobado');

        if ($request->has('buscar') && $request->buscar !== null) {
            $busqueda = $request->buscar;
            $query->where(function ($q) use ($busqueda) {
                $q->where('name', 'LIKE', "%{$busqueda}%")
                  ->orWhere('apellido', 'LIKE', "%{$busqueda}%");
            });
        }

        $arbitros = $query->orderBy('apellido', 'asc')->paginate(15)->withQueryString();

   
        $pendientes = User::where('rol', 'arbitro')->where('estado', 'pendiente')->get();

        return Inertia::render('Admin/Arbitros/Index', [
            'arbitros' => $arbitros,
            'pendientes' => $pendientes,  
            'filtros' => $request->only('buscar')
        ]);
    }

     
    public function aprobar(User $user)
    {
        $user->update(['estado' => 'aprobado']);
        return back()->with('success', 'El árbitro ha sido aprobado y ya puede ingresar al sistema.');
    }

    
    public function show(User $user)
    {
        if ($user->rol !== 'arbitro') {
            abort(404, 'El usuario solicitado no es un árbitro del plantel.');
        }

        $stats = [
            'total' => Designacion::where('user_id', $user->id)->count(),
            'confirmadas' => Designacion::where('user_id', $user->id)->where('estado_confirmacion', 'confirmado')->count(),
            'rechazadas' => Designacion::where('user_id', $user->id)->where('estado_confirmacion', 'rechazado')->count(),
            'pendientes' => Designacion::where('user_id', $user->id)->where('estado_confirmacion', 'pendiente')->count(),
        ];

        $historial = Designacion::with('partido')
            ->select('designaciones.*')
            ->join('partidos', 'partidos.id', '=', 'designaciones.partido_id')
            ->where('designaciones.user_id', $user->id)
            ->orderBy('partidos.fecha', 'desc')
            ->orderBy('partidos.hora_inicio', 'desc')
            ->take(10)
            ->get();

        
        $licencias = $user->licencias()->orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Arbitros/Show', [
            'arbitro' => $user,
            'stats' => $stats,
            'historial' => $historial,
            'licencias' => $licencias  
        ]);
    }
}