<?php

namespace App\Domains\AuthenticationSessions\Controllers;

use App\Domains\AuthenticationSessions\Models\User;
use App\Domains\AuthenticationSessions\Models\ActiveSession;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|same:password',
            'role' => 'required|string|exists:roles,name',
            // Campos del vendor
            'dni' => 'nullable|string|max:8|unique:users,dni',
            'fullname' => 'nullable|string|max:255',
            'avatar' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'dni' => $request->dni,
                'fullname' => $request->fullname ?? $request->name,
                'avatar' => $request->avatar,
                'phone' => $request->phone,
            ]);

            // Asignar el rol al usuario
            $user->assignRole($request->role);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'fullname' => $user->fullname,
                        'email' => $user->email,
                        'dni' => $user->dni,
                        'phone' => $user->phone,
                        'avatar' => $user->avatar,
                        'roles' => $user->getRoleNames(),
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                    ],
                    'token' => $token
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|string|exists:roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // Verificar que el usuario tenga el rol especificado
        if (!$user->hasRole($request->role)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para acceder con el rol especificado'
            ], 403);
        }

        // Crear token con Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'fullname' => $user->fullname,
                    'email' => $user->email,
                    'dni' => $user->dni,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ],
                'token' => $token
            ]
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ], 200);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'fullname' => $user->fullname,
                    'email' => $user->email,
                    'dni' => $user->dni,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ]
            ]
        ], 200);
    }

    /**
     * Update authenticated user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
            'dni' => 'sometimes|string|max:8|unique:users,dni,' . $user->id,
            'fullname' => 'sometimes|string|max:255',
            'avatar' => 'sometimes|string|max:500',
            'phone' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Preparar datos para actualizar
            $dataToUpdate = $request->only([
                'name',
                'email',
                'dni',
                'fullname',
                'avatar',
                'phone'
            ]);

            // Si se proporciona password, encriptarlo
            if ($request->has('password')) {
                $dataToUpdate['password'] = Hash::make($request->password);
            }

            // Actualizar usuario
            $user->update($dataToUpdate);

            // Refrescar usuario para obtener datos actualizados
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'fullname' => $user->fullname,
                        'email' => $user->email,
                        'dni' => $user->dni,
                        'phone' => $user->phone,
                        'avatar' => $user->avatar,
                        'roles' => $user->getRoleNames(),
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Generar token de reseteo
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => 'Se ha enviado un enlace de recuperación a tu correo electrónico'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar el enlace de recuperación. Intenta nuevamente.'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el enlace de recuperación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    // Revocar todos los tokens existentes del usuario
                    $user->tokens()->delete();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contraseña actualizada exitosamente. Puedes iniciar sesión con tu nueva contraseña.'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Token inválido o expirado. Solicita un nuevo enlace de recuperación.'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al resetear la contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}