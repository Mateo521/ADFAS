<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pago;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Ajuste;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);


        $ajustes = Ajuste::first();

        $arbitros = User::where('rol', 'arbitro')
            ->orderBy('apellido', 'asc')
            ->get()
            ->map(function ($user) use ($mes, $anio) {
                $pago = Pago::where('user_id', $user->id)
                    ->where('mes', $mes)
                    ->where('anio', $anio)
                    ->where('estado', 'pagado')
                    ->first();

                $user->pago_info = $pago;
                $user->esta_al_dia = !is_null($pago);
                return $user;
            });

        return Inertia::render('Admin/Pagos/Index', [
            'arbitros' => $arbitros,
            'filtros' => ['mes' => (int) $mes, 'anio' => (int) $anio],
            'ajustes' => $ajustes
        ]);
    }


    public function guardarAjustes(Request $request)
    {
        $request->validate([
            'cuota_monto' => 'required|numeric|min:0',
            'cuota_dia_vencimiento' => 'required|integer|between:1,28'
        ]);

        Ajuste::first()->update($request->all());

        return back()->with('success', 'Configuración de cuota actualizada.');
    }

    public function registrarManual(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'mes' => 'required|integer',
            'anio' => 'required|integer',
            'monto' => 'required|numeric'
        ]);


        Pago::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'mes' => $request->mes,
                'anio' => $request->anio,
            ],
            [
                'monto' => $request->monto,
                'estado' => 'pagado',
                'metodo_pago' => 'efectivo / manual',
                'fecha_pago' => now()
            ]
        );

        return back()->with('success', 'Pago registrado correctamente.');
    }
}