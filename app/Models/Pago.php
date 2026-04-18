<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'mes', 'anio', 'monto', 'estado', 
        'metodo_pago', 'mp_preference_id', 'mp_payment_id', 'fecha_pago'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}