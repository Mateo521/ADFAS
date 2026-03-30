<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\SimpleExcel\SimpleExcelReader;
use App\Models\Partido;

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
            'fecha_base' => 'required|date'
        ]);

        $file = $request->file('documento');
        $fechaBase = $request->fecha_base; 
        $extension = $file->getClientOriginalExtension();

        $reader = SimpleExcelReader::create($file->getRealPath(), $extension)->noHeaderRow();
        $filasCrudas = $reader->getRows();

        $partidosGuardados = 0;
        $partidosActualizados = 0; // <-- NUEVO: Contador de actualizados
        $titulosEncontrados = false;
        $indices = []; 

        // Pasamos por referencia las variables que vamos a modificar adentro
        $filasCrudas->each(function(array $fila) use (&$partidosGuardados, &$partidosActualizados, $fechaBase, &$titulosEncontrados, &$indices) {
            
            $filaTexto = array_map(function($item) {
                if ($item instanceof \DateTimeInterface) {
                    $item = $item->format('H:i:s');  
                }
                return strtoupper(trim((string)$item));
            }, $fila);

            // Buscamos las columnas dinámicamente
            if (!$titulosEncontrados && in_array('LOCAL', $filaTexto) && in_array('VISITANTE', $filaTexto)) {
                $titulosEncontrados = true;
                $indices['cat'] = array_search('CAT.', $filaTexto) !== false ? array_search('CAT.', $filaTexto) : array_search('CATEGORIA', $filaTexto);
                $indices['local'] = array_search('LOCAL', $filaTexto);
                $indices['visitante'] = array_search('VISITANTE', $filaTexto);
                $indices['cancha'] = array_search('CANCHA', $filaTexto);
                $indices['hora'] = array_search('HORA', $filaTexto);
                
                // NUEVO: Buscamos la columna DISCIPLINA
                $indices['disciplina'] = array_search('DISCIPLINA', $filaTexto);
                return; 
            }

            if ($titulosEncontrados) {
                // Si la fila está vacía o es otra vez un encabezado, la saltamos
                if (empty($fila[$indices['local']]) || empty($fila[$indices['visitante']]) || $fila[$indices['local']] === 'LOCAL' || $fila[$indices['local']] === 'LIGA SANLUISEÑA DE FÚTBOL') {
                    return;
                }

                $horaExacta = '00:00:00';
                if (isset($indices['hora']) && !empty($fila[$indices['hora']])) {
                    $horaCelda = $fila[$indices['hora']];
                    if ($horaCelda instanceof \DateTimeInterface) {
                        $horaExacta = $horaCelda->format('H:i:s');
                    } else {
                        // Limpieza de hora por si viene como "10:00" en texto
                        $horaTexto = (string) $horaCelda;
                        $horaExacta = strlen($horaTexto) == 5 ? $horaTexto . ':00' : $horaTexto;
                    }
                }

                // Extraemos los datos de forma segura
                $equipoLocal = (string)$fila[$indices['local']];
                $equipoVisitante = (string)$fila[$indices['visitante']];
                $categoria = isset($indices['cat']) && isset($fila[$indices['cat']]) ? (string)$fila[$indices['cat']] : 'Sin definir';
                $cancha = isset($indices['cancha']) && isset($fila[$indices['cancha']]) ? (string)$fila[$indices['cancha']] : 'A Confirmar';
                $disciplina = isset($indices['disciplina']) && isset($fila[$indices['disciplina']]) ? strtoupper(trim((string)$fila[$indices['disciplina']])) : null;

              
                $partido = Partido::updateOrCreate(
                    [
                      
                        'fecha' => $fechaBase,
                        'hora_inicio' => $horaExacta,
                        'equipo_local' => $equipoLocal,
                        'equipo_visitante' => $equipoVisitante,
                    ],
                    [
                      
                        'categoria' => $categoria,
                        'cancha' => $cancha,
                        'disciplina' => $disciplina, // Guardamos la 'F', '11' o 'I'
                        'estado' => 'publicado'  
                    ]
                );

                // Contamos qué fue lo que hizo
                if ($partido->wasRecentlyCreated) {
                    $partidosGuardados++;
                } else {
                    $partidosActualizados++;
                }
            }
        });

        return redirect()->route('admin.asignar.index')
            ->with('success', "¡Planilla procesada! Nuevos: $partidosGuardados | Actualizados: $partidosActualizados.");
    }
 
    private function intentarAsignar($partidoId, $nombreExcel, $funcion)
    {
        if (empty($nombreExcel)) return;
        
        $partes = explode(' ', trim($nombreExcel));
        $apellidoExcel = $partes[0];

        $arbitro = \App\Models\User::where('rol', 'arbitro')
            ->where('apellido', 'LIKE', '%' . $apellidoExcel . '%')
            ->first();

        if ($arbitro) {
            \App\Models\Designacion::create([
                'partido_id' => $partidoId,
                'user_id' => $arbitro->id,
                'funcion' => $funcion,
                'estado_confirmacion' => 'borrador'  
            ]);
        }
    }
}