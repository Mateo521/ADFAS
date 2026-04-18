<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Partido;
use App\Models\User;
use App\Models\Designacion;

class AsignacionController extends Controller
{
    public function index()
    {
        $partidos = Partido::doesntHave('designaciones')
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();


        $arbitros = User::where('rol', 'arbitro')
            ->with([
                'licencias' => function ($query) {
                    $query->where('estado', 'aprobado');
                },
                'designaciones' => function ($query) {

                    $query->with('partido')->latest()->take(10);
                }
            ])
            ->orderBy('apellido', 'asc')
            ->get();

        return Inertia::render('Admin/AsignarArbitros', [
            'partidos' => $partidos,
            'arbitros' => $arbitros,
            'import_summary' => session('import_summary')
        ]);
    }


    public function pizarraEnVivo(Request $request)
    {
        $query = Partido::with(['designaciones.user']);


        if ($request->has('jornada')) {
            $query->where('jornada', $request->jornada);
        } else {

            $query->where('fecha', '>=', now()->subDays(1)->toDateString());
        }

        $partidos = $query->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        $arbitros = User::where('rol', 'arbitro')->orderBy('apellido', 'asc')->get();

        return Inertia::render('Admin/AsignarArbitros', [
            'partidos' => $partidos,
            'arbitros' => $arbitros,
            'isEspectador' => true,
            'jornadaActual' => $request->jornada
        ]);
    }


    public function store(Request $request)
    {

        $request->validate([
            'asignaciones' => 'required|array',
            'asignaciones.*.partido_id' => 'required|exists:partidos,id',
            'asignaciones.*.principal_id' => 'nullable|exists:users,id',
            'asignaciones.*.asistente_id' => 'nullable|exists:users,id',
            'asignaciones.*.asistente_2_id' => 'nullable|exists:users,id',
            'asignaciones.*.asistente_3_id' => 'nullable|exists:users,id',
        ]);

        $partidosAsignados = 0;

        foreach ($request->asignaciones as $asignacion) {

            if (!empty($asignacion['principal_id']) || !empty($asignacion['asistente_id']) || !empty($asignacion['asistente_2_id']) || !empty($asignacion['asistente_3_id'])) {

                if (!empty($asignacion['principal_id'])) {
                    Designacion::create([
                        'partido_id' => $asignacion['partido_id'],
                        'user_id' => $asignacion['principal_id'],
                        'funcion' => 'ARBITRO PRINCIPAL',
                        'estado_confirmacion' => 'pendiente'
                    ]);
                }

                if (!empty($asignacion['asistente_id'])) {
                    Designacion::create([
                        'partido_id' => $asignacion['partido_id'],
                        'user_id' => $asignacion['asistente_id'],
                        'funcion' => 'ASISTENTE 1',
                        'estado_confirmacion' => 'pendiente'
                    ]);
                }


                if (!empty($asignacion['asistente_2_id'])) {
                    Designacion::create([
                        'partido_id' => $asignacion['partido_id'],
                        'user_id' => $asignacion['asistente_2_id'],
                        'funcion' => 'ASISTENTE 2',
                        'estado_confirmacion' => 'pendiente'
                    ]);
                }


                if (!empty($asignacion['asistente_3_id'])) {
                    Designacion::create([
                        'partido_id' => $asignacion['partido_id'],
                        'user_id' => $asignacion['asistente_3_id'],
                        'funcion' => 'ASISTENTE 3 / CRONO',
                        'estado_confirmacion' => 'pendiente'
                    ]);
                }

                Partido::where('id', $asignacion['partido_id'])->update([
                    'estado' => 'publicado'
                ]);

                $partidosAsignados++;
            }
        }

        return redirect()->route('dashboard')->with('success', "¡Se publicaron las designaciones para $partidosAsignados partidos!");
    }


    public function historial(Request $request)
    {
        $query = Partido::with(['designaciones.user'])->has('designaciones');

        if ($request->filled('fecha')) {
            $query->where('fecha', $request->fecha);
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', 'LIKE', '%' . $request->categoria . '%');
        }

        if ($request->filled('disciplina')) {
            $query->where('disciplina', 'LIKE', '%' . $request->disciplina . '%');
        }


        if ($request->filled('jornada')) {
            $query->where('jornada', $request->jornada);
        }

        if ($request->filled('equipo')) {
            $query->where(function ($q) use ($request) {
                $q->where('equipo_local', 'LIKE', '%' . $request->equipo . '%')
                    ->orWhere('equipo_visitante', 'LIKE', '%' . $request->equipo . '%');
            });
        }

        if ($request->filled('arbitro')) {
            $query->whereHas('designaciones.user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->arbitro . '%')
                    ->orWhere('apellido', 'LIKE', '%' . $request->arbitro . '%');
            });
        }

        $partidos = $query->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->paginate(20)
            ->withQueryString();

        $arbitros = \App\Models\User::where('rol', 'arbitro')->orderBy('apellido', 'asc')->get();


        $jornadas = Partido::whereNotNull('jornada')
            ->where('jornada', '!=', '')
            ->distinct()
            ->pluck('jornada');

        return Inertia::render('Admin/HistorialAsignaciones', [
            'partidos' => $partidos,
            'arbitros' => $arbitros,
            'jornadas' => $jornadas,
            'filtros' => $request->only(['fecha', 'categoria', 'equipo', 'arbitro', 'disciplina', 'jornada'])
        ]);
    }

    public function updateReasignacion(Request $request, Partido $partido)
    {

        $request->validate([
            'principal_id' => 'required|exists:users,id',
            'asistente_id' => 'nullable|exists:users,id',
            'asistente_2_id' => 'nullable|exists:users,id',
            'asistente_3_id' => 'nullable|exists:users,id'
        ]);

        $partido->designaciones()->delete();

        \App\Models\Designacion::create([
            'partido_id' => $partido->id,
            'user_id' => $request->principal_id,
            'funcion' => 'ARBITRO PRINCIPAL',
            'estado_confirmacion' => 'pendiente'
        ]);

        if ($request->asistente_id) {
            \App\Models\Designacion::create([
                'partido_id' => $partido->id,
                'user_id' => $request->asistente_id,
                'funcion' => 'ASISTENTE 1',
                'estado_confirmacion' => 'pendiente'
            ]);
        }


        if ($request->asistente_2_id) {
            \App\Models\Designacion::create([
                'partido_id' => $partido->id,
                'user_id' => $request->asistente_2_id,
                'funcion' => 'ASISTENTE 2',
                'estado_confirmacion' => 'pendiente'
            ]);
        }


        if ($request->asistente_3_id) {
            \App\Models\Designacion::create([
                'partido_id' => $partido->id,
                'user_id' => $request->asistente_3_id,
                'funcion' => 'ASISTENTE 3 / CRONO',
                'estado_confirmacion' => 'pendiente'
            ]);
        }

        return back();
    }
}