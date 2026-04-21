<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class EspectadorController extends Controller
{
    public function index()
    {

        $espectadores = User::where('rol', 'espectador')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Espectadores/Index', [
            'espectadores' => $espectadores
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'apellido' => 'Espectador',
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => 'espectador',
            'estado' => 'aprobado',
        ]);

        return back();
    }

    public function destroy(User $user)
    {

        if ($user->rol === 'espectador') {
            $user->delete();
        }
        return back();
    }
}