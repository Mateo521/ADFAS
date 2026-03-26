<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Partido;
use App\Models\Designacion;
use App\Models\Noticia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Creamos al Administrador
        $admin = User::firstOrCreate(
            ['email' => 'admin@adfas.com'],
            [
                'name' => 'Mateo',
                'apellido' => 'Admin',
                'telefono' => '2664000000',
                'password' => Hash::make('admin1234'),
                'rol' => 'admin'
            ]
        );

        // 2. Creamos a los árbitros reales
        $arbitrosReales = ['Noguera', 'Lucero', 'Chavez', 'Gallardo', 'Parodi', 'Escudero', 'Morales', 'Peralta', 'Vargas', 'Quiroga'];
        $usuariosArbitros = [];
        
        foreach ($arbitrosReales as $apellido) {
            $usuariosArbitros[$apellido] = User::firstOrCreate(
                ['email' => strtolower($apellido) . '@adfas.com'],
                [
                    'name' => 'Árbitro', 
                    'apellido' => $apellido,
                    'telefono' => '2664000000',
                    'password' => Hash::make('password'),
                    'rol' => 'arbitro'
                ]
            );
        }

        // 3. Creamos Partidos con FECHAS FUTURAS para ver en el Monitor
        $fechaProxima = Carbon::now()->addDays(2)->toDateString();
        
        $partidosData = [
            ['cat' => 'PRIMERA A', 'l' => 'JUVENTUD', 'v' => 'ESTUDIANTES', 'c' => 'EL VOLCAN', 'h' => '15:00:00'],
            ['cat' => 'PRIMERA B', 'l' => 'VICTORIA', 'v' => 'HURACAN', 'c' => 'SAN IGNACIO', 'h' => '16:30:00'],
            ['cat' => 'RESERVA', 'l' => 'PUEYRREDON', 'v' => 'EFIC', 'c' => 'CAMPUS ULP', 'h' => '11:00:00'],
            ['cat' => 'PRIMERA A', 'l' => 'GEPU', 'v' => 'UNION', 'c' => 'PANORAMICO', 'h' => '17:00:00'],
        ];

        foreach ($partidosData as $p) {
            $nuevoPartido = Partido::create([
                'categoria' => $p['cat'],
                'equipo_local' => $p['l'],
                'equipo_visitante' => $p['v'],
                'cancha' => $p['c'],
                'fecha' => $fechaProxima,
                'hora_inicio' => $p['h'],
                'estado' => 'publicado'  
            ]);

            // Asignamos a Noguera como principal a todos para probar el monitor
            Designacion::create([
                'partido_id' => $nuevoPartido->id,
                'user_id' => $usuariosArbitros['Noguera']->id,
                'funcion' => 'ARBITRO PRINCIPAL',
                'estado_confirmacion' => 'pendiente'
            ]);
        }

        // 4. GENERADOR DE NOTICIAS (15 noticias para probar paginación)
        $tipos = ['Información', 'Citación', 'Urgente', 'Actualización de Reglas'];
        
        for ($i = 1; $i <= 15; $i++) {
            Noticia::create([
                'user_id' => $admin->id,
                'tipo' => $tipos[array_rand($tipos)],
                'titulo' => "Comunicado Oficial N° $i: Actualización del Protocolo ADFAS",
                'contenido' => "Este es el contenido detallado de la noticia número $i. En este apartado se informan las novedades respecto al desempeño arbitral en la provincia de San Luis, recordamos a todos los colegiados mantener la integridad y puntualidad en los estadios asignados. Se adjuntan directivas para la próxima jornada del Torneo Apertura 2026.",
                'imagen_ruta' => null, // Puedes subir una imagen a storage/app/public/ y poner el nombre aquí
                'archivo_ruta' => null,
                'created_at' => Carbon::now()->subHours($i * 2), // Para que aparezcan ordenadas por tiempo
            ]);
        }
    }
}