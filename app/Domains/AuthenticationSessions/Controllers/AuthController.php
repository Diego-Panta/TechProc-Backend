<?php

namespace App\Domains\AuthenticationSessions\Controllers;

use App\Domains\AuthenticationSessions\Models\User;
use App\Domains\AuthenticationSessions\Models\ActiveSession;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
}