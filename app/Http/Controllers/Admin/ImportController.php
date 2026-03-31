<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\SimpleExcel\SimpleExcelReader;
use App\Models\Partido;
use Carbon\Carbon;

class ImportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/ImportarPartidos');
    }

    public function upload(Request $request)
    {
        // 1. Volvemos a pedir la fecha base
        $request->validate([
            'documento' => 'required|mimes:xlsx,csv|max:5120',
            'fecha_base' => 'required|date' 
        ]);

        $file = $request->file('documento');
        $fechaBase = Carbon::parse($request->fecha_base); // Ej: 2026-03-20
        $extension = $file->getClientOriginalExtension();

        $reader = SimpleExcelReader::create($file->getRealPath(), $extension)->noHeaderRow();
        $filasCrudas = $reader->getRows();

        $partidosGuardados = 0;
        $partidosActualizados = 0;  
        $titulosEncontrados = false;
        $indices = []; 

        $filasCrudas->each(function(array $fila) use (&$partidosGuardados, &$partidosActualizados, &$titulosEncontrados, &$indices, $fechaBase) {
            
            $filaTexto = array_map(function($item) {
                if ($item instanceof \DateTimeInterface) {
                    return $item->format('Y-m-d H:i:s');  
                }
                return strtoupper(trim((string)$item));
            }, $fila);

            // Buscamos los encabezados (Incluyendo DIA)
            if (!$titulosEncontrados && in_array('LOCAL', $filaTexto) && in_array('VISITANTE', $filaTexto)) {
                $titulosEncontrados = true;
                $indices['cat'] = array_search('CAT.', $filaTexto) !== false ? array_search('CAT.', $filaTexto) : array_search('CATEGORIA', $filaTexto);
                $indices['local'] = array_search('LOCAL', $filaTexto);
                $indices['visitante'] = array_search('VISITANTE', $filaTexto);
                $indices['cancha'] = array_search('CANCHA', $filaTexto);
                $indices['hora'] = array_search('HORA', $filaTexto);
                $indices['disciplina'] = array_search('DISCIPLINA', $filaTexto);
                $indices['dia'] = array_search('DIA', $filaTexto) !== false ? array_search('DIA', $filaTexto) : (array_search('DÍA', $filaTexto) !== false ? array_search('DÍA', $filaTexto) : array_search('FECHA', $filaTexto));
                
                return; 
            }

            if ($titulosEncontrados) {
                
                if (empty($fila[$indices['local']]) || empty($fila[$indices['visitante']]) || $fila[$indices['local']] === 'LOCAL' || $fila[$indices['local']] === 'LIGA SANLUISEÑA DE FÚTBOL') {
                    return;
                }

                // Extracción de HORA
                $horaExacta = '00:00:00';
                if (isset($indices['hora']) && !empty($fila[$indices['hora']])) {
                    $horaCelda = $fila[$indices['hora']];
                    if ($horaCelda instanceof \DateTimeInterface) {
                        $horaExacta = $horaCelda->format('H:i:s');
                    } else {
                        $horaTexto = (string) $horaCelda;
                        $horaExacta = strlen($horaTexto) == 5 ? $horaTexto . ':00' : $horaTexto;
                    }
                }

                // 2. LA MAGIA: Combinar Fecha Base con el Día del Excel
                $fechaExactaGuardar = $fechaBase->format('Y-m-d'); // Fallback por si la celda está vacía
                
                if (isset($indices['dia']) && !empty($fila[$indices['dia']])) {
                    $celdaDia = (string) $fila[$indices['dia']]; // Ej: "SABADO 21"
                    
                    // Buscamos los números dentro del texto usando Expresiones Regulares
                    preg_match('/\d+/', $celdaDia, $matches);
                    
                    if (!empty($matches)) {
                        $diaNumero = (int) $matches[0]; // Extrae el "21"
                        
                        // Creamos una copia de la fecha base para no alterar el mes/año original
                        // y le seteamos el día que sacamos del excel.
                        try {
                            $fechaExactaGuardar = $fechaBase->copy()->day($diaNumero)->format('Y-m-d');
                        } catch (\Exception $e) {
                            // Si por algún motivo el día no es válido, se usa el fallback
                        }
                    }
                }

                $equipoLocal = (string)$fila[$indices['local']];
                $equipoVisitante = (string)$fila[$indices['visitante']];
                $categoria = isset($indices['cat']) && isset($fila[$indices['cat']]) ? (string)$fila[$indices['cat']] : 'Sin definir';
                $cancha = isset($indices['cancha']) && isset($fila[$indices['cancha']]) ? (string)$fila[$indices['cancha']] : 'A Confirmar';
                $disciplina = isset($indices['disciplina']) && isset($fila[$indices['disciplina']]) ? strtoupper(trim((string)$fila[$indices['disciplina']])) : null;

                // 3. GUARDAMOS con la fecha correctamente procesada
                $partido = Partido::updateOrCreate(
                    [
                        'fecha' => $fechaExactaGuardar,  
                        'hora_inicio' => $horaExacta,
                        'equipo_local' => $equipoLocal,
                        'equipo_visitante' => $equipoVisitante,
                    ],
                    [
                        'categoria' => $categoria,
                        'cancha' => $cancha,
                        'disciplina' => $disciplina,  
                        'estado' => 'publicado'  
                    ]
                );

                if ($partido->wasRecentlyCreated) {
                    $partidosGuardados++;
                } else {
                    $partidosActualizados++;
                }
            }
        });

       return redirect()->route('admin.asignar.index')
            ->with('import_summary', [
                'nuevos' => $partidosGuardados,
                'actualizados' => $partidosActualizados,
                'total_filas' => $partidosGuardados + $partidosActualizados
            ])
            ->with('success', "Planilla procesada con éxito");
    }
}