<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //Registro de usuarios
     
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:usuarios,correo',
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    Password::min(8)->mixedCase()->numbers(),
                ],
            ]);

            $user = User::create([
                'nombre' => $validated['name'],
                'correo' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'message' => '¡Usuario creado con éxito! Por favor inicia sesión.',
            ], 201)->header('Access-Control-Allow-Origin', '*');

        } catch (ValidationException $e) {
            if (isset($e->errors()['email'])) {
                return response()->json([
                    'message' => 'El correo electrónico ya se encuentra registrado. Por favor use otro.'
                ], 422)->header('Access-Control-Allow-Origin', '*');
            }
            throw $e;
        }
    }

    //Inicio de sesión de usuarios 
     
    public function login(Request $request)
    {
        try {
            Log::info('Intento de inicio de sesión recibido.');

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('correo', $request->email)->first();

            if (!$user) {
                Log::warning('LOGIN FALLIDO: El usuario no existe en la base de datos.');
                return response()->json([
                    'message' => 'Credenciales incorrectas (Usuario no existe).'
                ], 401)->header('Access-Control-Allow-Origin', '*');
            }

            if (!Hash::check($request->password, $user->password)) {
                Log::warning('LOGIN FALLIDO: Contraseña incorrecta para el usuario ID: ' . $user->id);
                return response()->json([
                    'message' => 'Credenciales incorrectas (Contraseña inválida).'
                ], 401)->header('Access-Control-Allow-Origin', '*');
            }

            Log::info('LOGIN EXITOSO para el usuario ID: ' . $user->id);

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'name' => $user->nombre,
                    'email' => $user->correo,
                ]
            ], 200)->header('Access-Control-Allow-Origin', '*');

        } catch (\Exception $e) {
            Log::error('ERROR CRÍTICO EN LOGIN:', [
                'error' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine()
            ]);

            return response()->json([
                'message' => 'Error interno del servidor. Por favor intente más tarde.'
            ], 500)->header('Access-Control-Allow-Origin', '*');
        }
    }
}