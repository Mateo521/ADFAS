<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    use HasFactory;

    protected $fillable = [
        'categoria', 'equipo_local', 'equipo_visitante', 
        'cancha', 'fecha', 'hora_inicio', 'hora_fin', 'estado'
    ];

    // Un partido tiene muchas designaciones 
    public function designaciones()
    {
        return $this->hasMany(Designacion::class);
    }
}