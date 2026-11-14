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

    //Inicio de sesión de usuarios (Con control de intentos fallidos - HU-05)
     
    public function login(Request $request)
    {
        try {
            Log::info('Intento de inicio de sesión recibido.');

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

           $user = User::whereRaw('LOWER(correo) = ?', [strtolower($request->email)])->first();

            // Si el usuario existe, verificamos si está bloqueado temporalmente (CA-05.2 y CA-05.3)
            if ($user && $user->bloqueado_hasta) {
                if (\Illuminate\Support\Carbon::now()->isBefore($user->bloqueado_hasta)) {
                    $minutosRestantes = \Illuminate\Support\Carbon::now()->diffInMinutes($user->bloqueado_hasta) + 1;
                    Log::warning('LOGIN BLOQUEADO: Intento de acceso en cuenta bloqueada para el usuario ID: ' . $user->id);
                    return response()->json([
                        'message' => "Demasiados intentos fallidos. Su cuenta está temporalmente bloqueada. Intente de nuevo en {$minutosRestantes} minutos."
                    ], 429)->header('Access-Control-Allow-Origin', '*');
                } else {
                    // Si ya pasó el período de bloqueo, reiniciamos el contador (CA-05.4)
                    $user->intentos_fallidos = 0;
                    $user->bloqueado_hasta = null;
                    $user->save();
                }
            }

            if (!$user) {
                Log::warning('LOGIN FALLIDO: El usuario no existe en la base de datos.');
                return response()->json([
                    'message' => 'Credenciales incorrectas.'
                ], 401)->header('Access-Control-Allow-Origin', '*');
            }

            // Validar contraseña
            if (!Hash::check($request->password, $user->password)) {
                // Incrementar contador de intentos fallidos (CA-05.1)
                $user->intentos_fallidos += 1;

                // Si llega a 5 intentos fallidos, bloquear por 15 minutos (CA-05.1 y CA-05.2)
                if ($user->intentos_fallidos >= 5) {
                    $user->bloqueado_hasta = \Illuminate\Support\Carbon::now()->addMinutes(15);
                    $user->save();
                    Log::warning('CUENTA BLOQUEADA por 15 minutos debido a 5 intentos fallidos. Usuario ID: ' . $user->id);

                    return response()->json([
                        'message' => 'Demasiados intentos fallidos. La cuenta ha sido bloqueada temporalmente por 15 minutos.'
                    ], 429)->header('Access-Control-Allow-Origin', '*');
                }

                $user->save();

                Log::warning('LOGIN FALLIDO: Contraseña incorrecta para el usuario ID: ' . $user->id . '. Intentos fallidos: ' . $user->intentos_fallidos);
                
                $intentosRestantes = 5 - $user->intentos_fallidos;
                return response()->json([
                    'message' => "Credenciales incorrectas. Te quedan {$intentosRestantes} intentos antes del bloqueo."
                ], 401)->header('Access-Control-Allow-Origin', '*');
            }

            // INICIO DE SESIÓN EXITOSO: Reiniciar contador y bloqueo (CA-05.5)
            $user->intentos_fallidos = 0;
            $user->bloqueado_hasta = null;
            $user->save();

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

    // Recuperacion y restablecimiento de contraseña Sp3
   
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::whereRaw('LOWER(correo) = ?', [strtolower($request->email)])->first();

        if ($user) {
            $token = \Illuminate\Support\Str::random(64);

            \App\Models\PasswordReset::updateOrInsert(
                ['email' => $user->correo],
                [
                    'token' => Hash::make($token),
                    'created_at' => \Illuminate\Support\Carbon::now()
                ]
            );

            $resetLink = "{$request->getSchemeAndHttpHost()}/reset-password.php?token={$token}&email=" . urlencode($user->correo);
            
            try {
                // ENVÍO REAL POR SMTP (Utiliza la configuración del archivo .env y Gmail)
                \Illuminate\Support\Facades\Mail::raw(
                    "Hola,\n\nHas solicitado restablecer tu contraseña para TaskFlow Pro (HU-03).\n\n" .
                    "Haz clic en el siguiente enlace para continuar con el proceso:\n{$resetLink}\n\n" .
                    "Si no solicitaste este cambio, puedes ignorar este mensaje.", 
                    function ($message) use ($user) {
                        $message->to($user->correo)
                                ->subject('Restablecer Contraseña - TaskFlow Pro (HU-03)');
                    }
                );

                Log::info("CORREO SMTP ENVIADO (HU-03) exitosamente a: {$user->correo}");

            } catch (\Exception $e) {
                Log::error("ERROR DE SMTP AL ENVIAR CORREO (HU-03): " . $e->getMessage());
            }
        }

        // Mensaje genérico de confirmación por seguridad cumpliendo CA-03.2
        return response()->json([
            'message' => 'Si el correo electrónico está registrado en nuestro sistema, recibirás un enlace de recuperación en breve.'
        ], 200)->header('Access-Control-Allow-Origin', '*');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => [
                'required',
                'string',
                'confirmed',
                \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers(),
            ],
        ]);

        $resetRecord = \App\Models\PasswordReset::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'message' => 'El enlace de recuperación es inválido o ya fue utilizado.'
            ], 400)->header('Access-Control-Allow-Origin', '*');
        }

        //  Expiración después de 30 minutos cumpliendo CA-03.4 
        if (\Illuminate\Support\Carbon::parse($resetRecord->created_at)->addMinutes(30)->isPast()) {
            \App\Models\PasswordReset::where('email', $resetRecord->email)->delete();
            return response()->json([
                'message' => 'El enlace de recuperación ha expirado (límite de 30 minutos).'
            ], 400)->header('Access-Control-Allow-Origin', '*');
        }

        $user = User::where('correo', $resetRecord->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado.'
            ], 404)->header('Access-Control-Allow-Origin', '*');
        }

        //Actualizar contraseña con hash seguro e invalidar sesiones anteriores cumpliendo  CA-03.5 y CA-03.7
        $user->password = Hash::make($request->password);
        $user->save();
        $user->tokens()->delete();

        // CA-03.6: Invalidar token (un solo uso)
        \App\Models\PasswordReset::where('email', $resetRecord->email)->delete();

        return response()->json([
            'message' => '¡Contraseña restablecida con éxito! Ya puedes iniciar sesión.'
        ], 200)->header('Access-Control-Allow-Origin', '*');
    }
   
    // Cierre de sesion Sp4
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
            Log::info("CIERRE DE SESIÓN EXITOSO para el usuario: " . $request->user()->correo);
        }

        return response()->json([
            'message' => 'Sesión cerrada exitosamente en el servidor.'
        ], 200)->header('Access-Control-Allow-Origin', '*');
    }
    
}