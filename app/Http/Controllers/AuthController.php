<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showRegistro()
    {
        return view('auth.registro');
    }

    public function registro(Request $request)
    {
        $rules = [
            'nombre'   => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email'    => 'required|email|max:100|unique:usuarios,email',
            'password' => 'required|string|min:8|confirmed',
        ];

        if (Auth::check() && Auth::user()->esAdmin()) {
            $rules['rol'] = ['required', Rule::in(['cliente', 'admin', 'almacen'])];
        }

        $request->validate($rules, [
            'nombre.required'    => 'El nombre es obligatorio.',
            'apellido.required'  => 'El apellido es obligatorio.',
            'email.required'     => 'El correo es obligatorio.',
            'email.unique'       => 'Este correo ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'rol.required'       => 'El rol es obligatorio.',
            'rol.in'             => 'El rol seleccionado no es válido.',
        ]);

        $rol = (Auth::check() && Auth::user()->esAdmin())
            ? $request->rol
            : 'cliente';

        $usuario = Usuario::create([
            'nombre'          => $request->nombre,
            'apellido'        => $request->apellido,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'rol'             => $rol,
            'fecha_registro'  => now(),
        ]);

        if (Auth::check() && Auth::user()->esAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('success', "Usuario {$usuario->nombre} creado con rol '{$rol}'.");
        }

        Auth::login($usuario);

        return redirect()->route('home')->with('success', '¡Bienvenido, ' . $usuario->nombre . '!');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'Ingresa un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $credenciales = $request->only('email', 'password');
        $remember     = $request->boolean('remember');

        if (!Auth::attempt($credenciales, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Correo o contraseña incorrectos.']);
        }

        $request->session()->regenerate();

        return $this->redirigirSegunRol(Auth::user());
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }

    private function redirigirSegunRol($usuario)
    {
        return match ($usuario->rol) {
            'admin'   => redirect()->route('admin.dashboard'),
            'almacen' => redirect()->route('almacen.dashboard'),
            default   => redirect()->route('home'),
        };
    }
}
