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

        $reader = \Spatie\SimpleExcel\SimpleExcelReader::create($file->getRealPath(), $extension)->noHeaderRow();
        $filasCrudas = $reader->getRows();

        $partidosGuardados = 0;
        $titulosEncontrados = false;
        $indices = []; 

        $filasCrudas->each(function(array $fila) use (&$partidosGuardados, $fechaBase, &$titulosEncontrados, &$indices) {
           
            $filaTexto = array_map(function($item) {
                if ($item instanceof \DateTimeInterface) {
                    $item = $item->format('H:i:s');  
                }
                return strtoupper(trim((string)$item));
            }, $fila);

            if (!$titulosEncontrados && in_array('LOCAL', $filaTexto) && in_array('VISITANTE', $filaTexto)) {
                $titulosEncontrados = true;
                $indices['cat'] = array_search('CAT.', $filaTexto) !== false ? array_search('CAT.', $filaTexto) : array_search('CATEGORIA', $filaTexto);
                $indices['local'] = array_search('LOCAL', $filaTexto);
                $indices['visitante'] = array_search('VISITANTE', $filaTexto);
                $indices['cancha'] = array_search('CANCHA', $filaTexto);
                $indices['hora'] = array_search('HORA', $filaTexto);
                return; 
            }

            if ($titulosEncontrados) {
                if (empty($fila[$indices['local']]) || empty($fila[$indices['visitante']]) || $fila[$indices['local']] === 'LOCAL' || $fila[$indices['local']] === 'LIGA SANLUISEÑA DE FÚTBOL') {
                    return;
                }

                $horaExacta = '00:00:00';
                if (isset($indices['hora']) && !empty($fila[$indices['hora']])) {
                    $horaCelda = $fila[$indices['hora']];
                    if ($horaCelda instanceof \DateTimeInterface) {
                        $horaExacta = $horaCelda->format('H:i:s');
                    } else {
                        $horaExacta = (string) $horaCelda;
                    }
                }

                \App\Models\Partido::create([
                    'categoria' => isset($indices['cat']) ? (string)$fila[$indices['cat']] : 'Sin definir',
                    'equipo_local' => (string)$fila[$indices['local']],
                    'equipo_visitante' => (string)$fila[$indices['visitante']],
                    'cancha' => isset($indices['cancha']) ? (string)$fila[$indices['cancha']] : 'A Confirmar',
                    'fecha' => $fechaBase,
                    'hora_inicio' => $horaExacta,
                    'estado' => 'publicado'  
                ]);

                $partidosGuardados++;
            }
        });

        return redirect()->route('admin.asignar.index')
            ->with('success', "¡Planilla procesada! Se importaron $partidosGuardados partidos a la Pizarra.");
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