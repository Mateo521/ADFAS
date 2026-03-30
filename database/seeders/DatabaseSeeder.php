<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Noticia;
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

    
    }
}