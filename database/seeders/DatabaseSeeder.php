<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Partido;
use App\Models\Designacion;
use App\Models\Noticia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');  

     
        $admin = User::firstOrCreate(
            ['email' => 'admin@adfas.com'],
            [
                'name' => 'Mateo',
                'apellido' => 'Admin',
                'telefono' => '5492664000000',
                'password' => Hash::make('admin1234'),
                'rol' => 'admin'
            ]
        );

        
        $arbitrosReales = ['Noguera', 'Lucero', 'Chavez', 'Gallardo', 'Parodi', 'Escudero', 'Morales', 'Peralta', 'Vargas', 'Quiroga'];
        $usuariosArbitros = [];
        
        foreach ($arbitrosReales as $apellido) {
            $usuariosArbitros[] = User::firstOrCreate(
                ['email' => strtolower($apellido) . '@adfas.com'],
                [
                    'name' => $faker->firstName, 
                    'apellido' => $apellido,
                    'telefono' => '5492664' . $faker->randomNumber(6, true),
                    'password' => Hash::make('password'),
                    'rol' => 'arbitro'
                ]
            );
        }

      
        for ($i = 0; $i < 130; $i++) {
            $usuariosArbitros[] = User::create([
                'name' => $faker->firstName,
                'apellido' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'telefono' => '5492664' . $faker->randomNumber(6, true),
                'password' => Hash::make('password'),
                'rol' => 'arbitro'
            ]);
        }

       
        $categorias = ['PRIMERA A', 'PRIMERA B', 'RESERVA', 'SUB 17', 'FEMENINO'];
        $canchas = ['EL VOLCAN', 'SAN IGNACIO', 'CAMPUS ULP', 'PANORAMICO', 'ASEBA', 'ESTANCIA GRANDE'];
        $estadosConfirmacion = ['pendiente', 'confirmado', 'confirmado', 'confirmado', 'rechazado'];  

        for ($i = 0; $i < 50; $i++) {
       
            $diasOffset = $i < 30 ? rand(-30, -1) : rand(1, 14); 
            $fechaPartido = Carbon::now()->addDays($diasOffset)->toDateString();
            
            $partido = Partido::create([
                'categoria' => $categorias[array_rand($categorias)],
                'equipo_local' => strtoupper($faker->word) . ' FC',
                'equipo_visitante' => strtoupper($faker->word) . ' CLUB',
                'cancha' => $canchas[array_rand($canchas)],
                'fecha' => $fechaPartido,
                'hora_inicio' => $faker->time('H:00:00'),
                'estado' => 'publicado'  
            ]);

            
            $terna = collect($usuariosArbitros)->random(3);

          
            Designacion::create([
                'partido_id' => $partido->id,
                'user_id' => $terna[0]->id,
                'funcion' => 'ARBITRO PRINCIPAL',
                'estado_confirmacion' => $estadosConfirmacion[array_rand($estadosConfirmacion)]
            ]);

           
            Designacion::create([
                'partido_id' => $partido->id,
                'user_id' => $terna[1]->id,
                'funcion' => 'ASISTENTE 1',
                'estado_confirmacion' => $estadosConfirmacion[array_rand($estadosConfirmacion)]
            ]);

            
            Designacion::create([
                'partido_id' => $partido->id,
                'user_id' => $terna[2]->id,
                'funcion' => 'ASISTENTE 2',
                'estado_confirmacion' => $estadosConfirmacion[array_rand($estadosConfirmacion)]
            ]);
        }

       
        $partidoNoguera = Partido::create([
            'categoria' => 'PRIMERA A', 'equipo_local' => 'JUVENTUD', 'equipo_visitante' => 'ESTUDIANTES',
            'cancha' => 'EL BAJO', 'fecha' => Carbon::now()->addDays(2)->toDateString(), 'hora_inicio' => '15:00:00', 'estado' => 'publicado'
        ]);
        Designacion::create([
            'partido_id' => $partidoNoguera->id,
            'user_id' => User::where('email', 'noguera@adfas.com')->first()->id,
            'funcion' => 'ARBITRO PRINCIPAL',
            'estado_confirmacion' => 'pendiente'
        ]);

       
        $tipos = ['Información', 'Citación', 'Urgente', 'Actualización de Reglas'];
        for ($i = 1; $i <= 15; $i++) {
            Noticia::create([
                'user_id' => $admin->id,
                'tipo' => $tipos[array_rand($tipos)],
                'titulo' => "Comunicado Oficial N° $i: Actualización del Protocolo",
                'contenido' => "Este es el contenido detallado de la noticia número $i. pruebas y mas pruebas.",
                'created_at' => Carbon::now()->subHours($i * 5),  
            ]);
        }
    }
}