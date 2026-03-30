<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Licencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fecha_desde',
        'fecha_hasta',
        'motivo',
        'certificado_path',
        'estado'
    ];

     
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}