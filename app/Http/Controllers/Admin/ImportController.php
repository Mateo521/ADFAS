<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\SimpleExcel\SimpleExcelReader;
use App\Models\Partido;
use App\Models\Designacion;
use App\Models\User;

class ImportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/ImportarPartidos');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'documento' => 'required|mimes:xlsx,csv|max:5120', 
        ]);

        $file = $request->file('documento');
        $filas = SimpleExcelReader::create($file->getRealPath())->getRows();

        $partidosGuardados = 0;

        $filas->each(function(array $fila) use (&$partidosGuardados) {
            if (empty($fila['Local']) || empty($fila['Visitante'])) {
                return;
            }

         
            $partido = Partido::create([
                'categoria' => $fila['Categoria'] ?? 'Sin definir',
                'equipo_local' => $fila['Local'] ?? '',
                'equipo_visitante' => $fila['Visitante'] ?? '',
                'cancha' => $fila['Cancha'] ?? '',
                'fecha' => $fila['Fecha'] ?? '2026-03-29',  
                'hora_inicio' => $fila['Hora'] ?? '00:00:00',
                'estado' => 'publicado'
            ]);

      
            if (!empty($fila['Arbitro_Principal'])) {
                $this->asignarArbitro($partido->id, $fila['Arbitro_Principal'], 'ARBITRO PRINCIPAL');
            }

            if (!empty($fila['Asistente_1'])) {
                $this->asignarArbitro($partido->id, $fila['Asistente_1'], 'ASISTENTE 1');
            }

            $partidosGuardados++;
        });

        return redirect()->route('admin.importar.index')->with('success', "¡Planilla procesada! Se crearon $partidosGuardados partidos y se enviaron las designaciones.");
    }

 
    private function asignarArbitro($partidoId, $nombreExcel, $funcion)
    {
  
        $partes = explode(' ', trim($nombreExcel));
        $apellidoExcel = $partes[0];

     
        $arbitro = User::where('rol', 'arbitro')
            ->where('apellido', 'LIKE', '%' . $apellidoExcel . '%')
            ->first();

       
        if ($arbitro) {
            Designacion::create([
                'partido_id' => $partidoId,
                'user_id' => $arbitro->id,
                'funcion' => $funcion,
                'estado_confirmacion' => 'pendiente'
            ]);
        }
    }
}