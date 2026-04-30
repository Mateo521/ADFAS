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


        User::firstOrCreate(
            ['email' => 'espectador@gmail.com'],
            [
                'name' => 'Usuario',
                'apellido' => 'Espectador',
                'telefono' => '5492664333333',
                'password' => Hash::make('password'),
                'rol' => 'espectador',
                'estado' => 'aprobado'
            ]
        );


        $tarifas = [
            ['cat' => 'PRIMERA A', 'disc' => 'FUTBOL 11', 'prin' => 24000, 'asis' => 10000],
            ['cat' => 'PRIMERA B', 'disc' => 'FUTBOL 11', 'prin' => 20000, 'asis' => 9000],
            ['cat' => 'SUB 15', 'disc' => 'FUTBOL 11', 'prin' => 18000, 'asis' => 8000],
            ['cat' => 'VETERANO A', 'disc' => 'FUTSAL', 'prin' => 15000, 'asis' => 7000],
        ];

        foreach ($tarifas as $t) {
            Tarifa::firstOrCreate(
                ['categoria' => $t['cat'], 'disciplina' => $t['disc']],
                ['monto_principal' => $t['prin'], 'monto_asistente' => $t['asis']]
            );
        }


        $tipos = ['Aviso del Sistema', 'Institucional', 'Información'];
        for ($i = 1; $i <= 3; $i++) {
            Noticia::create([
                'user_id' => $admin->id,
                'tipo' => $tipos[array_rand($tipos)],
                'titulo' => "Comunicado Institucional N° {$i}",
                'contenido' => "Este es un comunicado de prueba generado por el sistema. Acá la administración puede publicar recordatorios, citaciones o cambios de reglamento.",
                'created_at' => Carbon::now()->subDays($i),
            ]);
        }


        $arbitro1 = User::where('email', 'arbitro1@gmail.com')->first();
        $arbitro2 = User::where('email', 'arbitro2@gmail.com')->first();
        $arbitro3 = User::where('email', 'arbitro3@gmail.com')->first();

        if ($arbitro1) {
            Licencia::firstOrCreate(
                ['user_id' => $arbitro1->id, 'estado' => 'pendiente'],
                ['fecha_desde' => Carbon::now()->format('Y-m-d'), 'fecha_hasta' => Carbon::now()->addDays(3)->format('Y-m-d'), 'motivo' => 'Reposo', 'estado' => 'pendiente']
            );
        }

        if ($arbitro2) {
            Licencia::firstOrCreate(
                ['user_id' => $arbitro2->id, 'estado' => 'aprobado'],
                ['fecha_desde' => Carbon::now()->subDays(1)->format('Y-m-d'), 'fecha_hasta' => Carbon::now()->addDays(5)->format('Y-m-d'), 'motivo' => 'Viaje', 'estado' => 'aprobado']
            );
        }


        $mesPasado = Carbon::now()->subMonth();


        $partidoDeuda1 = Partido::create([
            'fecha' => $mesPasado->copy()->setDay(10)->format('Y-m-d'),
            'hora_inicio' => '15:00:00',
            'equipo_local' => 'JUVENTUD',
            'equipo_visitante' => 'ESTUDIANTES',
            'categoria' => 'PRIMERA A',
            'disciplina' => 'FUTBOL 11',
            'cancha' => 'Juan Gilberto Funes',
            'jornada' => 'FECHA 1 DEUDA',
            'estado' => 'publicado'
        ]);


        $partidoDeuda2 = Partido::create([
            'fecha' => $mesPasado->copy()->setDay(18)->format('Y-m-d'),
            'hora_inicio' => '10:00:00',
            'equipo_local' => 'ASEBA',
            'equipo_visitante' => 'CAI',
            'categoria' => 'SUB 15',
            'disciplina' => 'FUTBOL 11',
            'cancha' => 'Cancha ASEBA',
            'jornada' => 'FECHA 2 DEUDA',
            'estado' => 'publicado'
        ]);


        Designacion::create(['partido_id' => $partidoDeuda1->id, 'user_id' => $arbitro1->id, 'funcion' => 'ARBITRO PRINCIPAL', 'estado_confirmacion' => 'confirmado']);
        Designacion::create(['partido_id' => $partidoDeuda1->id, 'user_id' => $arbitro2->id, 'funcion' => 'ASISTENTE 1', 'estado_confirmacion' => 'confirmado']);


        Designacion::create(['partido_id' => $partidoDeuda2->id, 'user_id' => $arbitro3->id, 'funcion' => 'ARBITRO PRINCIPAL', 'estado_confirmacion' => 'confirmado']);
        Designacion::create(['partido_id' => $partidoDeuda2->id, 'user_id' => $arbitro1->id, 'funcion' => 'ASISTENTE 1', 'estado_confirmacion' => 'confirmado']);
    }
}