<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designacion extends Model
{
    use HasFactory;

    protected $table = 'designaciones';

    protected $fillable = [
        'partido_id', 'user_id', 'funcion', 'estado_confirmacion'
    ];

    // una designación pertenece a un partido
    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }

    // una designación pertenece a un árbitro 
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}