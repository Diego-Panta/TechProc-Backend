<?php

namespace App\Domains\Security\Enums;

enum SecurityEventType: string
{
    case LOGIN_SUCCESS = 'login_success';
    case LOGIN_FAILED = 'login_failed';
    case LOGOUT = 'logout';
    case TOKEN_CREATED = 'token_created';
    case TOKEN_REVOKED = 'token_revoked';
    case SESSION_TERMINATED = 'session_terminated';
    case PASSWORD_RESET_REQUESTED = 'password_reset_requested';
    case PASSWORD_CHANGED = 'password_changed';
    case TWO_FACTOR_ENABLED = '2fa_enabled';
    case TWO_FACTOR_DISABLED = '2fa_disabled';
    case RECOVERY_EMAIL_ADDED = 'recovery_email_added';
    case RECOVERY_EMAIL_VERIFIED = 'recovery_email_verified';
    case SUSPICIOUS_ACTIVITY = 'suspicious_activity';
    case ANOMALY_DETECTED = 'anomaly_detected';

    /**
     * Get all values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match($this) {
            self::LOGIN_SUCCESS => 'Inicio de sesión exitoso',
            self::LOGIN_FAILED => 'Intento de inicio de sesión fallido',
            self::LOGOUT => 'Cierre de sesión',
            self::TOKEN_CREATED => 'Token creado',
            self::TOKEN_REVOKED => 'Token revocado',
            self::SESSION_TERMINATED => 'Sesión terminada',
            self::PASSWORD_RESET_REQUESTED => 'Solicitud de restablecimiento de contraseña',
            self::PASSWORD_CHANGED => 'Contraseña cambiada',
            self::TWO_FACTOR_ENABLED => 'Autenticación de dos factores habilitada',
            self::TWO_FACTOR_DISABLED => 'Autenticación de dos factores deshabilitada',
            self::RECOVERY_EMAIL_ADDED => 'Email de recuperación agregado',
            self::RECOVERY_EMAIL_VERIFIED => 'Email de recuperación verificado',
            self::SUSPICIOUS_ACTIVITY => 'Actividad sospechosa detectada',
            self::ANOMALY_DETECTED => 'Anomalía detectada',
        };
    }
}
