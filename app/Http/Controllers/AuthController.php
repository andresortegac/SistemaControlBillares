<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Mostrar formulario de login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Procesar login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended('dashboard')
                ->with('success', 'Bienvenido ' . Auth::user()->name);
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')
            ->with('success', 'Sesión cerrada exitosamente');
    }

    // Mostrar formulario de registro (solo admin)
    public function showRegister()
    {
        return view('auth.register');
    }

    // Procesar registro
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'rol' => 'required|in:admin,gerente,cajero',
            'telefono' => 'nullable|string|max:20'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol' => $validated['rol'],
            'telefono' => $validated['telefono'] ?? null
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente');
    }

    // Cambiar contraseña
    public function cambiarPassword(Request $request)
    {
        $validated = $request->validate([
            'password_actual' => 'required',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['password_actual'], $user->password)) {
            return back()->withErrors([
                'password_actual' => 'La contraseña actual es incorrecta.'
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Contraseña actualizada exitosamente');
    }
}
