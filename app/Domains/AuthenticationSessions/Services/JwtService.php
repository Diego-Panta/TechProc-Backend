<?php

namespace App\Domains\AuthenticationSessions\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class JwtService
{
    private $secretKey;
    private $algorithm = 'HS256';

    public function __construct()
    {
        // Usar APP_KEY de Laravel como secreto
        $this->secretKey = config('app.key');
        
        if (empty($this->secretKey)) {
            throw new \Exception('APP_KEY no está configurada en .env');
        }
    }

    /**
     * Generar token JWT para un usuario
     */
    public function generateToken(User $user)
    {
        $payload = [
            'iss' => config('app.url', 'http://localhost'), // Issuer
            'iat' => time(), // Issued at
            'exp' => time() + (2 * 60 * 60), // Expiration (2 horas)
            'nbf' => time(), // Not before
            'jti' => uniqid(), // JWT ID
            'sub' => $user->id, // Subject (user ID)
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role
            ]
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Validar y decodificar token JWT
     */
    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtener ID de usuario desde el token
     */
    public function getUserIdFromToken($token)
    {
        $data = $this->validateToken($token);
        return $data['sub'] ?? null;
    }

    /**
     * Obtener usuario desde el token
     */
    public function getUserFromToken($token)
    {
        $userId = $this->getUserIdFromToken($token);
        
        if (!$userId) {
            return null;
        }

        return User::find($userId);
    }

    /**
     * Verificar si el token es válido
     */
    public function isValid($token)
    {
        return $this->validateToken($token) !== null;
    }
}