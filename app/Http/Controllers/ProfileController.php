<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(\App\Models\User::class)->ignore($request->user()->id)
            ],
            'telefono' => ['nullable', 'string', 'max:20'],
            'foto_perfil' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = $request->user();


        $user->fill($request->only(['name', 'apellido', 'email', 'telefono']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }


        if ($request->hasFile('foto_perfil')) {


            if ($user->foto_perfil) {
                Storage::disk('public')->delete($user->foto_perfil);
            }


            $user->foto_perfil = $request->file('foto_perfil')->store('perfiles', 'public');
        }


        $user->save();

        return redirect()->route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
