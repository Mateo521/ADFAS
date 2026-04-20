<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Noticia;
use App\Models\Licencia;
use App\Models\Tarifa;
use App\Models\Partido;
use App\Models\Designacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Administrador
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
        
        // 2. Crear 15 Árbitros Aprobados
        for ($i = 1; $i <= 15; $i++) {
            User::firstOrCreate(
                ['email' => "arbitro{$i}@gmail.com"],
                [
                    'name' => 'Árbitro',
                    'apellido' => "Prueba {$i}",
                    'telefono' => '54926641111' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                    'rol' => 'arbitro',
                    'estado' => 'aprobado' 
                ]
            );
        }

        // 3. Crear 5 Árbitros Pendientes
        for ($i = 16; $i <= 20; $i++) {
            User::firstOrCreate(
                ['email' => "arbitro{$i}@adfas.com"],
                [
                    'name' => 'Árbitro',
                    'apellido' => "Nuevo {$i}",
                    'telefono' => '54926642222' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                    'rol' => 'arbitro',
                    'estado' => 'pendiente'
                ]
            );
        }
 
        // 4. Crear Noticias
        $tipos = ['Aviso del Sistema', 'Institucional', 'Información'];
        for ($i = 1; $i <= 3; $i++) {
            Noticia::create([
                'user_id' => $admin->id,
                'tipo' => $tipos[array_rand($tipos)],
                'titulo' => "Comunicado Institucional N° {$i}",
                'contenido' => "Este es un comunicado de prueba generado por el sistema. Acá la administración puede publicar recordatorios, citaciones o cambios de reglamento etc etc.",
                'created_at' => Carbon::now()->subDays($i),  
            ]);
        }

        // 5. Asignar Licencias
        $arbitro1 = User::where('email', 'arbitro1@gmail.com')->first();
        $arbitro2 = User::where('email', 'arbitro2@gmail.com')->first();
        $arbitro3 = User::where('email', 'arbitro3@gmail.com')->first();

        if ($arbitro1) {
            Licencia::firstOrCreate(
                ['user_id' => $arbitro1->id, 'estado' => 'pendiente'],
                [
                    'fecha_desde' => Carbon::now()->format('Y-m-d'),
                    'fecha_hasta' => Carbon::now()->addDays(3)->format('Y-m-d'),
                    'motivo' => 'Reposo médico por cuadro gripal',
                    'estado' => 'pendiente'
                ]
            );
        }

        if ($arbitro2) {
            Licencia::firstOrCreate(
                ['user_id' => $arbitro2->id, 'estado' => 'aprobado'],
                [
                    'fecha_desde' => Carbon::now()->subDays(1)->format('Y-m-d'),
                    'fecha_hasta' => Carbon::now()->addDays(5)->format('Y-m-d'),
                    'motivo' => 'Viaje personal programado',
                    'estado' => 'aprobado'
                ]
            );
        }

        if ($arbitro3) {
            Licencia::firstOrCreate(
                ['user_id' => $arbitro3->id, 'estado' => 'rechazado'],
                [
                    'fecha_desde' => Carbon::now()->subDays(10)->format('Y-m-d'),
                    'fecha_hasta' => Carbon::now()->subDays(8)->format('Y-m-d'),
                    'motivo' => 'Cumpleaños familiar',
                    'estado' => 'rechazado'
                ]
            );
        }

        // =========================================================
        // NUEVO: SISTEMA DE TARIFAS Y PARTIDOS PARA LIQUIDACIONES
        // =========================================================

        // 6. Crear Tarifas / Aranceles
        $tarifas = [
            ['cat' => 'PRIMERA A', 'disc' => 'FUTBOL 11', 'prin' => 24000, 'asis' => 10000],
            ['cat' => 'PRIMERA B', 'disc' => 'FUTBOL 11', 'prin' => 20000, 'asis' => 9000],
            ['cat' => 'SUB 15',    'disc' => 'FUTBOL 11', 'prin' => 18000, 'asis' => 8000],
            ['cat' => 'VETERANO A','disc' => 'FUTSAL',    'prin' => 15000, 'asis' => 7000],
        ];

        foreach ($tarifas as $t) {
            Tarifa::firstOrCreate(
                ['categoria' => $t['cat'], 'disciplina' => $t['disc']],
                ['monto_principal' => $t['prin'], 'monto_asistente' => $t['asis']]
            );
        }

        // 7. Crear Partidos en el MES ANTERIOR (Para que se cobren ahora)
        $mesAnterior = Carbon::now()->subMonth();

        $partido1 = Partido::create([
            'fecha' => $mesAnterior->copy()->setDay(5)->format('Y-m-d'),
            'hora_inicio' => '15:00:00',
            'equipo_local' => 'JUVENTUD',
            'equipo_visitante' => 'ESTUDIANTES',
            'categoria' => 'PRIMERA A',
            'cancha' => 'Juan Gilberto Funes',
            'disciplina' => 'FUTBOL 11',
            'estado' => 'publicado'
        ]);

        $partido2 = Partido::create([
            'fecha' => $mesAnterior->copy()->setDay(12)->format('Y-m-d'),
            'hora_inicio' => '10:00:00',
            'equipo_local' => 'ASEBA',
            'equipo_visitante' => 'CAI',
            'categoria' => 'SUB 15',
            'cancha' => 'Cancha ASEBA',
            'disciplina' => 'FUTBOL 11',
            'estado' => 'publicado'
        ]);

        $partido3 = Partido::create([
            'fecha' => $mesAnterior->copy()->setDay(20)->format('Y-m-d'),
            'hora_inicio' => '21:00:00',
            'equipo_local' => 'LOS AMIGOS',
            'equipo_visitante' => 'SPARTA',
            'categoria' => 'VETERANO A',
            'cancha' => 'Suela 13',
            'disciplina' => 'FUTSAL',
            'estado' => 'publicado'
        ]);

        $partido4 = Partido::create([
            'fecha' => $mesAnterior->copy()->setDay(26)->format('Y-m-d'),
            'hora_inicio' => '16:00:00',
            'equipo_local' => 'DEFENSORES',
            'equipo_visitante' => 'EL LINCE',
            'categoria' => 'PRIMERA B',
            'cancha' => 'El Lince',
            'disciplina' => 'FUTBOL 11',
            'estado' => 'publicado'
        ]);

        // 8. Designar a los Árbitros en los partidos y marcarlos como "confirmado"
        
        // Partido 1 (Primera A) - Arbitro 1 es Principal ($24.000), Arbitro 2 es Asistente ($10.000)
        Designacion::create(['partido_id' => $partido1->id, 'user_id' => $arbitro1->id, 'funcion' => 'ARBITRO PRINCIPAL', 'estado_confirmacion' => 'confirmado']);
        Designacion::create(['partido_id' => $partido1->id, 'user_id' => $arbitro2->id, 'funcion' => 'ASISTENTE 1', 'estado_confirmacion' => 'confirmado']);

        // Partido 2 (Sub 15) - Arbitro 3 es Principal ($18.000), Arbitro 1 es Asistente ($8.000)
        Designacion::create(['partido_id' => $partido2->id, 'user_id' => $arbitro3->id, 'funcion' => 'ARBITRO PRINCIPAL', 'estado_confirmacion' => 'confirmado']);
        Designacion::create(['partido_id' => $partido2->id, 'user_id' => $arbitro1->id, 'funcion' => 'ASISTENTE 1', 'estado_confirmacion' => 'confirmado']);

        // Partido 3 (Futsal) - Arbitro 1 es Principal ($15.000)
        Designacion::create(['partido_id' => $partido3->id, 'user_id' => $arbitro1->id, 'funcion' => 'ARBITRO PRINCIPAL', 'estado_confirmacion' => 'confirmado']);

        // Partido 4 (Primera B) - Arbitro 2 es Principal ($20.000), Arbitro 3 es Asistente ($9.000)
        Designacion::create(['partido_id' => $partido4->id, 'user_id' => $arbitro2->id, 'funcion' => 'ARBITRO PRINCIPAL', 'estado_confirmacion' => 'confirmado']);
        Designacion::create(['partido_id' => $partido4->id, 'user_id' => $arbitro3->id, 'funcion' => 'ASISTENTE 1', 'estado_confirmacion' => 'confirmado']);

    }
}