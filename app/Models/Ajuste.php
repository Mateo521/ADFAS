<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ajuste extends Model
{
    protected $fillable = ['cuota_monto', 'cuota_dia_vencimiento'];
}
