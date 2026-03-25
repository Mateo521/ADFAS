<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Noticia;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NoticiaController extends Controller
{

public function index()
    {
 
        $noticias = Noticia::with('autor:id,name,apellido')
                           ->latest()
                           ->get();

        return Inertia::render('Noticias/Index', [
            'noticias' => $noticias
        ]);
    }
  
    public function store(Request $request)
    {
     
        if (Auth::user()->rol !== 'admin') {
            abort(403, 'No tienes permiso para publicar noticias.');
        }

        $request->validate([
            'tipo' => 'required|string',
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'imagen' => 'nullable|image|max:2048', // Max 2MB
            'archivo' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120', // Max 5MB
        ]);

        $rutaImagen = null;
        $rutaArchivo = null;
 
        if ($request->hasFile('imagen')) {
            $rutaImagen = $request->file('imagen')->store('noticias/imagenes', 'public');
        }
 
        if ($request->hasFile('archivo')) {
            $rutaArchivo = $request->file('archivo')->store('noticias/archivos', 'public');
        }

        Noticia::create([
            'tipo' => $request->tipo,
            'titulo' => $request->titulo,
            'contenido' => $request->contenido,
            'imagen_ruta' => $rutaImagen,
            'archivo_ruta' => $rutaArchivo,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', '¡Noticia publicada exitosamente!');
    }

    public function create()
    {
        if (Auth::user()->rol !== 'admin') {
            abort(403, 'Acceso denegado');
        }
        return Inertia::render('Admin/CrearNoticia');
    }
    
    public function show(Noticia $noticia)
    {
      
        $noticia->load('autor:id,name,apellido');
        
        return Inertia::render('Noticias/Show', [
            'noticia' => $noticia
        ]);
    }
}