<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Noticia extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo', 'titulo', 'contenido', 'imagen_ruta', 'archivo_ruta', 'user_id'
    ];

    //  Una noticia pertenece a un usuario  
    public function autor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}