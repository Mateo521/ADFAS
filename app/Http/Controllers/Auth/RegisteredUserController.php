<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
   public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'foto_perfil' => 'nullable|image|max:2048',  
        ]);

       
        $rutaFoto = null;
        if ($request->hasFile('foto_perfil')) {
            // guarda la imagen en la carpeta storage/app/public/perfiles
            $rutaFoto = $request->file('foto_perfil')->store('perfiles', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'apellido' => $request->apellido,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'foto_perfil' => $rutaFoto,
            'rol' => 'arbitro',  
        ]);

        event(new Registered($user));

       

        return redirect()->route('login')->with('status', 'Registro correcto. Tu cuenta está pendiente de aprobación por la administración.');
    }
}
