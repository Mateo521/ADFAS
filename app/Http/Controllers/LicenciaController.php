<?php

namespace App\Http\Controllers;

use App\Models\Licencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LicenciaController extends Controller
{
   
    public function store(Request $request)
    {
        $request->validate([
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
            'motivo' => 'required|string|max:255',
            'certificado' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', 
        ]);

        $path = null;
        if ($request->hasFile('certificado')) {
            $path = $request->file('certificado')->store('certificados', 'public');
        }

        Licencia::create([
            'user_id' => Auth::id(),
            'fecha_desde' => $request->fecha_desde,
            'fecha_hasta' => $request->fecha_hasta,
            'motivo' => $request->motivo,
            'certificado_path' => $path,
            'estado' => 'pendiente'
        ]);

        return back()->with('success', 'Tu certificado fue enviado y está pendiente de revisión por la Liga.');
    }

   
    public function updateEstado(Request $request, Licencia $licencia)
    {
        $request->validate([
            'estado' => 'required|in:aprobado,rechazado'
        ]);

        $licencia->update([
            'estado' => $request->estado
        ]);

        $mensaje = $request->estado === 'aprobado' ? 'Certificado aprobado. El árbitro no recibirá designaciones en esas fechas.' : 'Certificado rechazado.';
        
        return back()->with('success', $mensaje);
    }
}