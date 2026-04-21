<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Noticia;
use App\Models\Licencia;
use App\Models\Tarifa;
use App\Models\Partido;
use App\Models\Designacion;
use App\Models\Pago;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CREAR ADMINISTRADOR
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'apellido' => 'Principal',
                'telefono' => '5492664000000',
                'password' => Hash::make('admin1234'),
                'rol' => 'admin',
                'estado' => 'aprobado'
            ]
        );

        // 2. CREAR UN EJÉRCITO DE ÁRBITROS (40 Árbitros para simular volumen)
        $arbitros = [];
        for ($i = 1; $i <= 40; $i++) {
            $arbitros[] = clone User::firstOrCreate(
                ['email' => "arbitro{$i}@gmail.com"],
                [
                    'name' => 'Árbitro',
                    'apellido' => "Prueba {$i}",
                    'telefono' => '5492664' . rand(100000, 999999),
                    'password' => Hash::make('password'),
                    'rol' => 'arbitro',
                    'estado' => 'aprobado'
                ]
            );
        }

        // 3. TARIFAS BASE
        $tarifasData = [
            ['cat' => 'PRIMERA A', 'disc' => 'FUTBOL 11', 'prin' => 24000, 'asis' => 10000],
            ['cat' => 'PRIMERA B', 'disc' => 'FUTBOL 11', 'prin' => 20000, 'asis' => 9000],
            ['cat' => 'SUB 15', 'disc' => 'FUTBOL 11', 'prin' => 18000, 'asis' => 8000],
            ['cat' => 'VETERANO A', 'disc' => 'FUTSAL', 'prin' => 15000, 'asis' => 7000],
        ];

        foreach ($tarifasData as $t) {
            Tarifa::firstOrCreate(
                ['categoria' => $t['cat'], 'disciplina' => $t['disc']],
                ['monto_principal' => $t['prin'], 'monto_asistente' => $t['asis']]
            );
        }

        // Listas para datos aleatorios
        $equipos = ['JUVENTUD', 'ESTUDIANTES', 'ASEBA', 'CAI', 'SAN MARTIN', 'HURACAN', 'DEFENSORES', 'EL LINCE', 'VICTORIA', 'UNION'];
        $canchas = ['Juan Gilberto Funes', 'Cancha ASEBA', 'El Lince', 'Suela 13', 'Estadio Central', 'Predio Huracán'];
        $jornadas = ['FECHA 1', 'FECHA 2', 'FECHA 3', 'FECHA 4', 'FECHA 5', 'FECHA 6', 'CUARTOS', 'SEMIFINAL', 'FINAL'];

        // 4. LA MÁQUINA DEL TIEMPO (Generar partidos desde hace 5 años hasta 2 meses en el futuro)
        $fechaInicio = Carbon::now()->subYears(5);
        $fechaFin = Carbon::now()->addMonths(2);

        $fechaActual = clone $fechaInicio;

        // Desactivar temporalmente los logs de DB para que no colapse la memoria al sembrar tanto
        DB::disableQueryLog();

        echo "Generando 5 años de historial... Esto puede tomar unos segundos...\n";

        $partidosInsertar = [];
        $designacionesInsertar = [];

        while ($fechaActual <= $fechaFin) {
            // Solo generamos partidos los sábados (6) y domingos (0) para ser realistas
            if ($fechaActual->dayOfWeek === 6 || $fechaActual->dayOfWeek === 0) {

                // Generar entre 2 y 5 partidos por día de fin de semana
                $cantidadPartidos = rand(2, 5);

                for ($p = 0; $p < $cantidadPartidos; $p++) {
                    $tarifa = $tarifasData[array_rand($tarifasData)];
                    $esPasado = $fechaActual < Carbon::now();

                    // Crear el Partido
                    // Crear el Partido
                    $partido = Partido::create([
                        'fecha' => $fechaActual->format('Y-m-d'),
                        'hora_inicio' => str_pad(rand(9, 21), 2, '0', STR_PAD_LEFT) . ':00:00',
                        'equipo_local' => $equipos[array_rand($equipos)],
                        'equipo_visitante' => $equipos[array_rand($equipos)],
                        'categoria' => $tarifa['cat'],
                        'disciplina' => $tarifa['disc'],
                        'cancha' => $canchas[array_rand($canchas)],
                        'jornada' => $jornadas[array_rand($jornadas)],




                        'estado' => 'publicado',

                        'goles_local' => $esPasado ? rand(0, 5) : null,
                        'goles_visitante' => $esPasado ? rand(0, 5) : null,
                    ]);

                    // Designar 3 árbitros aleatorios para este partido
                    $arbitrosSeleccionados = array_rand($arbitros, 3);
                    $funciones = ['ARBITRO PRINCIPAL', 'ASISTENTE 1', 'ASISTENTE 2'];

                    foreach ($arbitrosSeleccionados as $index => $arbitroKey) {
                        $estadoConfirmacion = 'pendiente';
                        if ($esPasado) {
                            // En el pasado, casi todos confirmaron, algunos pocos rechazaron
                            $estadoConfirmacion = (rand(1, 100) > 5) ? 'confirmado' : 'rechazado';
                        } else {
                            // En el futuro (ej: 27 de abril en adelante), mezclamos
                            $estadoConfirmacion = ['pendiente', 'confirmado', 'rechazado'][rand(0, 2)];
                        }

                        Designacion::create([
                            'partido_id' => $partido->id,
                            'user_id' => $arbitros[$arbitroKey]->id,
                            'funcion' => $funciones[$index],
                            'estado_confirmacion' => $estadoConfirmacion
                        ]);
                    }
                }
            }
            $fechaActual->addDay();
        }

        // 5. GENERAR PAGOS HISTÓRICOS FALSOS (Para ver la pestaña de liquidaciones llena)
        echo "Generando pagos históricos...\n";
        for ($mesesAtras = 1; $mesesAtras <= 12; $mesesAtras++) {
            $fechaPago = Carbon::now()->subMonths($mesesAtras);

            // Simular que los primeros 10 árbitros pagaron sus cuotas
            for ($i = 0; $i < 10; $i++) {
                Pago::create([
                    'user_id' => $arbitros[$i]->id,
                    'mes' => $fechaPago->month,
                    'anio' => $fechaPago->year,
                    'total_ganado' => rand(30000, 100000),
                    'detalle_ticket' => [
                        ['fecha' => $fechaPago->format('Y-m-d'), 'partido' => 'Historial Simulado', 'monto' => rand(30000, 100000)]
                    ],
                    'monto' => rand(3000, 10000), // El 10% aprox
                    'estado' => 'pagado',
                    'metodo_pago' => 'transferencia',
                    'fecha_pago' => $fechaPago->addDays(rand(1, 5))->format('Y-m-d H:i:s')
                ]);
            }
        }

        // 6. NOTICIAS MASIVAS
        $tipos = ['Aviso del Sistema', 'Institucional', 'Información'];
        for ($i = 1; $i <= 20; $i++) {
            Noticia::create([
                'user_id' => $admin->id,
                'tipo' => $tipos[array_rand($tipos)],
                'titulo' => "Comunicado Histórico N° {$i}",
                'contenido' => "Contenido de prueba generado para estrés de la base de datos.",
                'created_at' => Carbon::now()->subDays(rand(1, 365)),
            ]);
        }

        echo "¡Sembrado masivo finalizado con éxito!\n";
    }
}