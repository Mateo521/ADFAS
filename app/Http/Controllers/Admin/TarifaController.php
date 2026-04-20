<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tarifa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TarifaController extends Controller
{
    public function index()
    {
        $tarifas = Tarifa::orderBy('disciplina', 'asc')
            ->orderBy('categoria', 'asc')
            ->get();

        return Inertia::render('Admin/Tarifas/Index', [
            'tarifas' => $tarifas
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'categoria' => 'required|string|max:255',
            'disciplina' => 'required|string|max:255',
            'monto_principal' => 'required|numeric|min:0',
            'monto_asistente' => 'required|numeric|min:0',
        ]);


        Tarifa::create([
            'categoria' => strtoupper(trim($request->categoria)),
            'disciplina' => strtoupper(trim($request->disciplina)),
            'monto_principal' => $request->monto_principal,
            'monto_asistente' => $request->monto_asistente,
        ]);

        return back();
    }

    public function update(Request $request, Tarifa $tarifa)
    {
        $request->validate([
            'monto_principal' => 'required|numeric|min:0',
            'monto_asistente' => 'required|numeric|min:0',
        ]);

        $tarifa->update([
            'monto_principal' => $request->monto_principal,
            'monto_asistente' => $request->monto_asistente,
        ]);

        return back();
    }

    public function destroy(Tarifa $tarifa)
    {
        $tarifa->delete();
        return back();
    }
}