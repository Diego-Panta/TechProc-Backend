<?php

namespace App\Domains\AuthenticationSessions\Controllers;

use App\Models\User;
use App\Domains\AuthenticationSessions\Models\ActiveSession;
use App\Domains\AuthenticationSessions\Notifications\VerifyEmailNotification;
use App\Domains\Security\Services\SecurityEventService;
use App\Domains\Security\Services\AnomalyDetectionService;
use App\Domains\Security\Services\SessionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected SecurityEventService $securityEventService;
    protected AnomalyDetectionService $anomalyDetectionService;
    protected SessionService $sessionService;

    public function __construct(
        SecurityEventService $securityEventService,
        AnomalyDetectionService $anomalyDetectionService,
        SessionService $sessionService
    ) {
        $this->securityEventService = $securityEventService;
        $this->anomalyDetectionService = $anomalyDetectionService;
        $this->sessionService = $sessionService;
    }
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
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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
            $avatarPath = null;

            // Manejar avatar si se proporciona
            if ($request->hasFile('avatar')) {
                $avatarFile = $request->file('avatar');
                $avatarPath = $avatarFile->store('avatars', 'public');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'dni' => $request->dni,
                'fullname' => $request->fullname ?? $request->name,
                'avatar' => $avatarPath,
                'phone' => $request->phone,
            ]);

            // Asignar el rol al usuario
            $user->assignRole($request->role);

            // Crear token con metadata
            $tokenResult = $user->createToken('auth_token', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            $token = $tokenResult->plainTextToken;

            // Generar URL completa del avatar
            $avatarUrl = $user->avatar ? Storage::disk('public')->url($user->avatar) : null;

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
                        'avatar_url' => $avatarUrl,
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

        // Verificar si el usuario está bloqueado ANTES de verificar credenciales
        $blockInfo = $this->anomalyDetectionService->isEmailBlocked($request->email);
        if ($blockInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta está temporalmente bloqueada',
                'blocked' => true,
                'block_info' => [
                    'reason' => $blockInfo['reason'],
                    'blocked_until' => $blockInfo['blocked_until'],
                    'remaining_time' => $blockInfo['remaining_time'],
                ]
            ], 423); // 423 Locked
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Registrar intento de login fallido
            $this->securityEventService->logLoginFailed(
                $request->email,
                $request->ip(),
                $request->userAgent(),
                'invalid_credentials'
            );

            // Detectar fuerza bruta y bloquear si es necesario
            $blockResult = $this->anomalyDetectionService->detectAndBlockIfNeeded(
                $request->email,
                $request->ip()
            );

            if ($blockResult) {
                // Si se bloqueó al usuario o ya estaba bloqueado
                if ($blockResult['type'] === 'user_blocked' || $blockResult['type'] === 'already_blocked') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tu cuenta ha sido bloqueada temporalmente debido a múltiples intentos fallidos',
                        'blocked' => true,
                        'block_info' => [
                            'blocked_until' => $blockResult['blocked_until'] ?? null,
                            'remaining_time' => $blockResult['remaining_time'] ?? null,
                            'block_duration_minutes' => $blockResult['block_duration_minutes'] ?? null,
                        ]
                    ], 423); // 423 Locked
                }

                // Si es brute force pero el usuario no existe
                return response()->json([
                    'success' => false,
                    'message' => 'Demasiados intentos fallidos. Por favor, intenta más tarde.'
                ], 429);
            }

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

        // Verificar límite de sesiones concurrentes
        $sessionInfo = $this->sessionService->getConcurrentSessionsInfo($user->id);
        if ($sessionInfo['has_reached_limit']) {
            return response()->json([
                'success' => false,
                'message' => 'Has alcanzado el límite máximo de sesiones concurrentes',
                'session_limit_exceeded' => true,
                'session_info' => [
                    'current_sessions' => $sessionInfo['current_sessions'],
                    'max_sessions' => $sessionInfo['max_sessions'],
                ]
            ], 429); // 429 Too Many Requests
        }

        // Si tiene 2FA habilitado, requerir código
        if ($user->two_factor_enabled) {
            return response()->json([
                'success' => false,
                'requires_2fa' => true,
                'message' => 'Se requiere código de autenticación de dos factores',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]
            ], 200);
        }

        // Crear token con Sanctum y guardar metadata (IP y user agent)
        $tokenResult = $user->createToken('auth_token', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        $token = $tokenResult->plainTextToken;

        // Registrar login exitoso
        $this->securityEventService->logLoginSuccess(
            $user->id,
            $request->ip(),
            $request->userAgent()
        );

        // Detectar anomalías (múltiples IPs)
        $this->anomalyDetectionService->runAllDetections(
            $user->id,
            $request->ip(),
            $request->userAgent()
        );

        // Generar URL completa del avatar
        $avatarUrl = $user->avatar ? Storage::disk('public')->url($user->avatar) : null;

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
                    'avatar_url' => $avatarUrl,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ],
                'token' => $token
            ]
        ], 200);
    }

    /**
     * Verificar código 2FA durante el login
     */
    public function verify2FALogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'code' => 'required|string',
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

        // Verificar credenciales
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // Verificar rol
        if (!$user->hasRole($request->role)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para acceder con el rol especificado'
            ], 403);
        }

        // Verificar que 2FA esté habilitado
        if (!$user->two_factor_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'La autenticación de dos factores no está habilitada'
            ], 400);
        }

        // Verificar código 2FA
        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        // Si el código no es válido, intentar con códigos de recuperación
        if (!$valid) {
            $recoveryCodesValid = false;

            if ($user->two_factor_recovery_codes) {
                $recoveryCodes = json_decode($user->two_factor_recovery_codes, true);

                foreach ($recoveryCodes as $index => $hashedCode) {
                    if (Hash::check(strtoupper($request->code), $hashedCode)) {
                        // Código de recuperación válido, eliminarlo
                        unset($recoveryCodes[$index]);
                        $user->update([
                            'two_factor_recovery_codes' => json_encode(array_values($recoveryCodes))
                        ]);
                        $recoveryCodesValid = true;
                        break;
                    }
                }
            }

            if (!$recoveryCodesValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Código de autenticación inválido'
                ], 401);
            }
        }

        // Verificar límite de sesiones concurrentes
        $sessionInfo = $this->sessionService->getConcurrentSessionsInfo($user->id);
        if ($sessionInfo['has_reached_limit']) {
            return response()->json([
                'success' => false,
                'message' => 'Has alcanzado el límite máximo de sesiones concurrentes',
                'session_limit_exceeded' => true,
                'session_info' => [
                    'current_sessions' => $sessionInfo['current_sessions'],
                    'max_sessions' => $sessionInfo['max_sessions'],
                ]
            ], 429);
        }

        // Crear token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Generar URL completa del avatar
        $avatarUrl = $user->avatar ? Storage::disk('public')->url($user->avatar) : null;

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
                    'avatar_url' => $avatarUrl,
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

        // Generar URL completa del avatar
        $avatarUrl = $user->avatar ? Storage::disk('public')->url($user->avatar) : null;

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
                    'avatar_url' => $avatarUrl,
                    'secondary_email' => $user->secondary_email,
                    'secondary_email_verified' => $user->secondary_email_verified_at !== null,
                    'two_factor_enabled' => $user->two_factor_enabled,
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
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB max
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
            // DEBUG: Log para verificar qué está recibiendo
            Log::info('Update Profile Request:', [
                'all_data' => $request->all(),
                'files' => $request->allFiles(),
                'has_avatar' => $request->hasFile('avatar'),
                'avatar_file' => $request->file('avatar'),
                'content_type' => $request->header('Content-Type'),
            ]);

            // Preparar datos para actualizar
            $dataToUpdate = $request->only([
                'name',
                'email',
                'dni',
                'fullname',
                'phone'
            ]);

            // Si se proporciona password, encriptarlo
            if ($request->has('password')) {
                $dataToUpdate['password'] = Hash::make($request->password);
            }

            // Manejar avatar (imagen)
            if ($request->hasFile('avatar')) {
                Log::info('Avatar file detected, processing...');

                // Eliminar avatar anterior si existe
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                    Log::info('Previous avatar deleted: ' . $user->avatar);
                }

                // Guardar nueva imagen
                $avatarFile = $request->file('avatar');
                $avatarPath = $avatarFile->store('avatars', 'public');
                $dataToUpdate['avatar'] = $avatarPath;

                Log::info('New avatar saved: ' . $avatarPath);
            } else {
                Log::warning('No avatar file detected in request');
            }

            // Actualizar usuario
            $user->update($dataToUpdate);

            // Refrescar usuario para obtener datos actualizados
            $user->refresh();

            // Generar URL completa del avatar
            $avatarUrl = $user->avatar ? Storage::disk('public')->url($user->avatar) : null;

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
                        'avatar_url' => $avatarUrl,
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
     * Send password reset link using secondary email
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $email = $request->email;

            // Buscar usuario por secondary_email verificado
            $user = User::where('secondary_email', $email)
                        ->whereNotNull('secondary_email_verified_at')
                        ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe una cuenta con este correo secundario o no ha sido verificado.'
                ], 404);
            }

            // Generar token de reseteo para el usuario encontrado
            $status = Password::sendResetLink(['email' => $user->email]);

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => 'Se ha enviado un enlace de recuperación a tu correo secundario.'
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

    /**
     * Add and send verification code to secondary email
     */
    public function addSecondaryEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'secondary_email' => 'required|email|different:email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verificar que el email secundario no esté siendo usado como email principal
        if (User::where('email', $request->secondary_email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Este email ya está registrado como email principal en el sistema'
            ], 400);
        }

        // Verificar que el email secundario no esté siendo usado por otro usuario
        if (User::where('secondary_email', $request->secondary_email)
                ->where('id', '!=', $user->id)
                ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Este email ya está siendo usado como email secundario por otro usuario'
            ], 400);
        }

        try {
            // Generar código de 6 dígitos
            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Guardar email secundario, código y expiración (15 minutos)
            $user->update([
                'secondary_email' => $request->secondary_email,
                'secondary_email_verified_at' => null, // Resetear verificación
                'secondary_email_verification_code' => $code,
                'secondary_email_code_expires_at' => Carbon::now()->addMinutes(15)
            ]);

            // Enviar código al email secundario usando Notification
            $user->notify(new \App\Domains\AuthenticationSessions\Notifications\VerifySecondaryEmailNotification($code));

            return response()->json([
                'success' => true,
                'message' => 'Email secundario agregado. Se ha enviado un código de verificación.',
                'data' => [
                    'secondary_email' => $user->secondary_email,
                    'verified' => false
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar el email secundario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify secondary email with code
     */
    public function verifySecondaryEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verificar si tiene un recovery email
        if (!$user->secondary_email) {
            return response()->json([
                'success' => false,
                'message' => 'No has agregado un email secundario'
            ], 400);
        }

        // Verificar si el email ya está verificado
        if ($user->secondary_email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'El email secundario ya está verificado'
            ], 400);
        }

        // Verificar si existe un código
        if (!$user->secondary_email_verification_code) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un código de verificación activo. Solicita uno nuevo.'
            ], 400);
        }

        // Verificar si el código ha expirado
        if (Carbon::now()->isAfter($user->secondary_email_code_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'El código de verificación ha expirado. Solicita uno nuevo.'
            ], 400);
        }

        // Verificar si el código es correcto
        if ($user->secondary_email_verification_code !== $request->code) {
            return response()->json([
                'success' => false,
                'message' => 'Código de verificación incorrecto'
            ], 400);
        }

        try {
            // Marcar recovery email como verificado y limpiar código
            $user->update([
                'secondary_email_verified_at' => Carbon::now(),
                'secondary_email_verification_code' => null,
                'secondary_email_code_expires_at' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email secundario verificado exitosamente',
                'data' => [
                    'secondary_email' => $user->secondary_email,
                    'verified' => true,
                    'verified_at' => $user->secondary_email_verified_at
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el email secundario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend secondary email verification code
     */
    public function resendSecondaryEmailCode(Request $request)
    {
        $user = $request->user();

        // Verificar si tiene un recovery email
        if (!$user->secondary_email) {
            return response()->json([
                'success' => false,
                'message' => 'No has agregado un email secundario'
            ], 400);
        }

        // Verificar si el email ya está verificado
        if ($user->secondary_email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'El email secundario ya está verificado'
            ], 400);
        }

        try {
            // Generar nuevo código de 6 dígitos
            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Actualizar código y expiración
            $user->update([
                'secondary_email_verification_code' => $code,
                'secondary_email_code_expires_at' => Carbon::now()->addMinutes(15)
            ]);

            // Enviar notificación al email secundario
            $user->notify(new \App\Domains\AuthenticationSessions\Notifications\VerifySecondaryEmailNotification($code));

            return response()->json([
                'success' => true,
                'message' => 'Nuevo código de verificación enviado a tu email secundario'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reenviar el código de verificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove secondary email
     */
    public function removeSecondaryEmail(Request $request)
    {
        $user = $request->user();

        if (!$user->secondary_email) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes un email secundario configurado'
            ], 400);
        }

        try {
            $user->update([
                'secondary_email' => null,
                'secondary_email_verified_at' => null,
                'secondary_email_verification_code' => null,
                'secondary_email_code_expires_at' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email secundario eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el email secundario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Habilitar 2FA para el usuario
     */
    public function enable2FA(Request $request)
    {
        try {
            $user = $request->user();

            // Si ya está habilitado
            if ($user->two_factor_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'La autenticación de dos factores ya está habilitada'
                ], 400);
            }

            // Generar secreto 2FA
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $secret = $google2fa->generateSecretKey();

            // Guardar el secreto (pero aún no habilitado)
            $user->update([
                'two_factor_secret' => $secret,
            ]);

            // Generar códigos de recuperación
            $recoveryCodes = [];
            for ($i = 0; $i < 8; $i++) {
                $recoveryCodes[] = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            }

            // Guardar códigos de recuperación encriptados
            $user->update([
                'two_factor_recovery_codes' => json_encode(array_map(function($code) {
                    return Hash::make($code);
                }, $recoveryCodes))
            ]);

            // Generar QR code URL
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $secret
            );

            return response()->json([
                'success' => true,
                'message' => 'Escanea el código QR con Google Authenticator y luego verifica el código para habilitar 2FA',
                'data' => [
                    'secret' => $secret,
                    'qr_code_url' => $qrCodeUrl,
                    'recovery_codes' => $recoveryCodes, // Solo se muestran una vez
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al habilitar 2FA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar y confirmar la habilitación de 2FA
     */
    public function verify2FA(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|size:6'
            ]);

            $user = $request->user();

            // Validar que tenga un secreto generado
            if (!$user->two_factor_secret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Primero debes iniciar el proceso de habilitación de 2FA'
                ], 400);
            }

            // Si ya está habilitado
            if ($user->two_factor_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'La autenticación de dos factores ya está habilitada'
                ], 400);
            }

            // Verificar código
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

            if (!$valid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Código de verificación inválido'
                ], 400);
            }

            // Habilitar 2FA
            $user->update([
                'two_factor_enabled' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Autenticación de dos factores habilitada exitosamente',
                'data' => [
                    'two_factor_enabled' => true
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar 2FA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deshabilitar 2FA
     */
    public function disable2FA(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string'
            ]);

            $user = $request->user();

            // Verificar que 2FA esté habilitado
            if (!$user->two_factor_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'La autenticación de dos factores no está habilitada'
                ], 400);
            }

            // Verificar password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contraseña incorrecta'
                ], 400);
            }

            // Deshabilitar 2FA y limpiar datos
            $user->update([
                'two_factor_enabled' => false,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Autenticación de dos factores deshabilitada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al deshabilitar 2FA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener nuevos códigos de recuperación
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string'
            ]);

            $user = $request->user();

            // Verificar que 2FA esté habilitado
            if (!$user->two_factor_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'La autenticación de dos factores no está habilitada'
                ], 400);
            }

            // Verificar password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contraseña incorrecta'
                ], 400);
            }

            // Generar nuevos códigos de recuperación
            $recoveryCodes = [];
            for ($i = 0; $i < 8; $i++) {
                $recoveryCodes[] = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            }

            // Guardar códigos de recuperación encriptados
            $user->update([
                'two_factor_recovery_codes' => json_encode(array_map(function($code) {
                    return Hash::make($code);
                }, $recoveryCodes))
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nuevos códigos de recuperación generados. Guárdalos en un lugar seguro',
                'data' => [
                    'recovery_codes' => $recoveryCodes
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar códigos de recuperación',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}