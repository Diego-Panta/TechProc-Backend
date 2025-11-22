# Módulo de Seguridad - Documentación API para Frontend

## Índice
1. [Información General](#información-general)
2. [Autenticación](#autenticación)
3. [Flujo de Bloqueo Automático](#flujo-de-bloqueo-automático)
4. [Endpoints de Bloqueos](#endpoints-de-bloqueos)
5. [Endpoints de Configuración](#endpoints-de-configuración)
6. [Endpoints de Sesiones](#endpoints-de-sesiones)
7. [Endpoints de Eventos](#endpoints-de-eventos)
8. [Dashboard de Seguridad](#dashboard-de-seguridad)
9. [Códigos de Estado HTTP](#códigos-de-estado-http)
10. [Manejo de Errores](#manejo-de-errores)

---

## Información General

### Base URL
```
http://localhost:8000/api
```

### Headers Requeridos
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

### Roles con Acceso
- `admin` - Acceso completo
- `security` - Acceso completo

### Usuarios de Prueba
| Email | Rol | Contraseña |
|-------|-----|------------|
| `admin@incadev.com` | admin | password |
| `maria.security@incadev.com` | security | password |

---

## Autenticación

### Login
```http
POST /api/auth/login
```

**Request:**
```json
{
    "email": "maria.security@incadev.com",
    "password": "password",
    "role": "security"
}
```

**Response Exitosa (200):**
```json
{
    "success": true,
    "message": "Login exitoso",
    "data": {
        "user": {
            "id": 14,
            "name": "Maria Rojas",
            "fullname": "MARIA ROJAS CAMPOS",
            "email": "maria.security@incadev.com",
            "roles": ["security"],
            "permissions": ["user-blocks.view-any", "user-blocks.create", "..."]
        },
        "token": "1|abc123..."
    }
}
```

**Response Usuario Bloqueado (423):**
```json
{
    "success": false,
    "message": "Tu cuenta está temporalmente bloqueada",
    "blocked": true,
    "block_info": {
        "reason": "Bloqueado automáticamente por 5 intentos fallidos de login en 10 minutos",
        "blocked_until": "2025-11-22T15:30:00+00:00",
        "remaining_time": "25 minutos"
    }
}
```

**Response Límite de Sesiones Concurrentes (429):**
```json
{
    "success": false,
    "message": "Has alcanzado el límite máximo de sesiones concurrentes",
    "session_limit_exceeded": true,
    "session_info": {
        "current_sessions": 5,
        "max_sessions": 5
    }
}
```

> **Nota:** El límite de sesiones concurrentes es configurable desde `security/settings`. Por defecto es 5 sesiones activas simultáneas. Una sesión se considera "activa" si ha sido usada en los últimos 30 minutos.

---

## Flujo de Bloqueo Automático

### Diagrama de Flujo
```
┌─────────────────┐
│  Usuario envía  │
│     login       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐     Sí     ┌─────────────────┐
│ ¿Usuario está   │───────────▶│  Retornar 423   │
│   bloqueado?    │            │    (Locked)     │
└────────┬────────┘            └─────────────────┘
         │ No
         ▼
┌─────────────────┐     No     ┌─────────────────┐
│ ¿Credenciales   │───────────▶│ Registrar       │
│   correctas?    │            │ intento fallido │
└────────┬────────┘            └────────┬────────┘
         │ Sí                           │
         ▼                              ▼
┌─────────────────┐            ┌─────────────────┐
│  Login exitoso  │            │ ¿Supera máximo  │
│  Retornar 200   │            │   de intentos?  │
└─────────────────┘            └────────┬────────┘
                                        │ Sí
                                        ▼
                               ┌─────────────────┐
                               │ BLOQUEAR USUARIO│
                               │  Retornar 423   │
                               └─────────────────┘
```

### Configuración por Defecto
| Parámetro | Valor | Descripción |
|-----------|-------|-------------|
| `max_failed_login_attempts` | 5 | Intentos antes de bloquear |
| `failed_login_window_minutes` | 10 | Ventana de tiempo para contar intentos |
| `block_duration_minutes` | 30 | Duración del bloqueo automático |

---

## Endpoints de Bloqueos

### 1. Listar Usuarios Bloqueados
```http
GET /api/security/blocks?per_page=15
```

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 5,
            "user": {
                "id": 5,
                "name": "Liliana Guerra",
                "email": "liliana@incadev.com"
            },
            "blocked_by": 14,
            "blocked_by_user": {
                "id": 14,
                "name": "Maria Rojas"
            },
            "reason": "Comportamiento sospechoso detectado",
            "block_type": "manual",
            "block_type_label": "Manual",
            "ip_address": "192.168.1.100",
            "blocked_at": "2025-11-22T10:00:00+00:00",
            "blocked_at_human": "hace 2 horas",
            "blocked_until": "2025-11-22T11:00:00+00:00",
            "blocked_until_human": "en 58 minutos",
            "is_active": true,
            "is_currently_blocked": true,
            "remaining_time": "58 minutos",
            "unblocked_at": null,
            "unblocked_by": null,
            "metadata": {
                "duration_minutes": 60,
                "permanent": false
            },
            "created_at": "2025-11-22T10:00:00+00:00"
        }
    ],
    "meta": {
        "total": 1,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

---

### 2. Historial de Bloqueos
```http
GET /api/security/blocks/history?per_page=20
```

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 2,
            "user_id": 6,
            "user": {
                "id": 6,
                "name": "Pedro Bravo",
                "email": "pedro@incadev.com"
            },
            "reason": "Bloqueado automáticamente por 5 intentos fallidos",
            "block_type": "automatic",
            "block_type_label": "Automático",
            "blocked_at": "2025-11-21T15:00:00+00:00",
            "blocked_until": "2025-11-21T15:30:00+00:00",
            "is_active": false,
            "is_currently_blocked": false,
            "remaining_time": null,
            "unblocked_at": "2025-11-21T15:30:00+00:00",
            "unblocked_by": null,
            "metadata": {
                "failed_attempts": 5,
                "block_duration_minutes": 30
            }
        }
    ],
    "meta": {
        "total": 10,
        "per_page": 20,
        "current_page": 1,
        "last_page": 1
    }
}
```

---

### 3. Estadísticas de Bloqueos
```http
GET /api/security/blocks/statistics?days=30
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "total_blocks": 15,
        "automatic_blocks": 12,
        "manual_blocks": 3,
        "currently_blocked": 2,
        "unblocked": 13
    }
}
```

---

### 4. Historial de Bloqueos de un Usuario
```http
GET /api/security/blocks/user/{userId}
```

**Ejemplo:** `GET /api/security/blocks/user/5`

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 5,
            "reason": "Múltiples intentos fallidos",
            "block_type": "automatic",
            "blocked_at": "2025-11-22T10:00:00+00:00",
            "is_active": true,
            "is_currently_blocked": true,
            "remaining_time": "15 minutos"
        }
    ]
}
```

---

### 5. Verificar si Usuario está Bloqueado
```http
GET /api/security/blocks/check/{userId}
```

**Ejemplo:** `GET /api/security/blocks/check/5`

**Response - Usuario Bloqueado (200):**
```json
{
    "success": true,
    "blocked": true,
    "data": {
        "id": 1,
        "user_id": 5,
        "reason": "Comportamiento sospechoso",
        "block_type": "manual",
        "blocked_at": "2025-11-22T10:00:00+00:00",
        "blocked_until": "2025-11-22T11:00:00+00:00",
        "remaining_time": "45 minutos"
    }
}
```

**Response - Usuario NO Bloqueado (200):**
```json
{
    "success": true,
    "blocked": false,
    "message": "El usuario no está bloqueado"
}
```

---

### 6. Bloquear Usuario Manualmente

```http
POST /api/security/blocks
```

**Request - Bloqueo Temporal:**
```json
{
    "user_id": 5,
    "reason": "Comportamiento sospechoso detectado",
    "duration_minutes": 60
}
```

**Request - Bloqueo Permanente:**
```json
{
    "user_id": 5,
    "reason": "Violación grave de términos de servicio"
}
```

**Response Exitosa (201):**
```json
{
    "success": true,
    "message": "Usuario bloqueado exitosamente",
    "data": {
        "id": 3,
        "user_id": 5,
        "user": {
            "id": 5,
            "name": "Liliana Guerra",
            "email": "liliana@incadev.com"
        },
        "blocked_by": 14,
        "blocked_by_user": {
            "id": 14,
            "name": "Maria Rojas"
        },
        "reason": "Comportamiento sospechoso detectado",
        "block_type": "manual",
        "block_type_label": "Manual",
        "blocked_at": "2025-11-22T12:00:00+00:00",
        "blocked_until": "2025-11-22T13:00:00+00:00",
        "is_active": true,
        "is_currently_blocked": true,
        "remaining_time": "1 hora"
    }
}
```

**Response - Usuario Ya Bloqueado (400):**
```json
{
    "success": false,
    "message": "El usuario ya está bloqueado"
}
```

**Response - Auto-bloqueo (400):**
```json
{
    "success": false,
    "message": "No puedes bloquearte a ti mismo"
}
```

---

### 7. Desbloquear Usuario por User ID
```http
DELETE /api/security/blocks/user/{userId}
```

**Ejemplo:** `DELETE /api/security/blocks/user/5`

**Response Exitosa (200):**
```json
{
    "success": true,
    "message": "Usuario desbloqueado exitosamente"
}
```

**Response - No Bloqueado (400):**
```json
{
    "success": false,
    "message": "El usuario no está bloqueado"
}
```

---

### 8. Desbloquear por ID de Bloqueo
```http
DELETE /api/security/blocks/{blockId}
```

**Ejemplo:** `DELETE /api/security/blocks/3`

**Response Exitosa (200):**
```json
{
    "success": true,
    "message": "Usuario desbloqueado exitosamente"
}
```

---

## Endpoints de Configuración

### 1. Obtener Todas las Configuraciones
```http
GET /api/security/settings
```

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "key": "max_failed_login_attempts",
            "value": 5,
            "type": "integer",
            "description": "Número máximo de intentos fallidos de login antes de bloquear al usuario",
            "group": "login",
            "updated_at": "2025-11-22T10:00:00+00:00"
        },
        {
            "id": 2,
            "key": "failed_login_window_minutes",
            "value": 10,
            "type": "integer",
            "description": "Ventana de tiempo (en minutos) para contar los intentos fallidos de login",
            "group": "login",
            "updated_at": "2025-11-22T10:00:00+00:00"
        },
        {
            "id": 3,
            "key": "block_duration_minutes",
            "value": 30,
            "type": "integer",
            "description": "Duración del bloqueo automático (en minutos)",
            "group": "blocking",
            "updated_at": "2025-11-22T10:00:00+00:00"
        }
    ]
}
```

---

### 2. Obtener Configuraciones Agrupadas
```http
GET /api/security/settings/grouped
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "login": {
            "max_failed_login_attempts": {
                "value": 5,
                "type": "integer",
                "description": "Número máximo de intentos fallidos de login antes de bloquear al usuario"
            },
            "failed_login_window_minutes": {
                "value": 10,
                "type": "integer",
                "description": "Ventana de tiempo (en minutos) para contar los intentos fallidos de login"
            }
        },
        "blocking": {
            "block_duration_minutes": {
                "value": 30,
                "type": "integer",
                "description": "Duración del bloqueo automático (en minutos)"
            }
        },
        "sessions": {
            "session_timeout_minutes": {
                "value": 30,
                "type": "integer",
                "description": "Tiempo de inactividad antes de considerar una sesión como inactiva"
            },
            "max_concurrent_sessions": {
                "value": 5,
                "type": "integer",
                "description": "Número máximo de sesiones concurrentes permitidas por usuario"
            }
        },
        "anomaly_detection": {
            "detect_multiple_ips": {
                "value": true,
                "type": "boolean",
                "description": "Habilitar detección de logins desde múltiples IPs"
            },
            "multiple_ip_window_minutes": {
                "value": 30,
                "type": "integer",
                "description": "Ventana de tiempo para detectar logins desde múltiples IPs"
            }
        }
    }
}
```

---

### 3. Obtener Configuraciones de Login
```http
GET /api/security/settings/login
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "max_failed_login_attempts": 5,
        "block_duration_minutes": 30,
        "failed_login_window_minutes": 10
    }
}
```

---

### 4. Actualizar Una Configuración
```http
PUT /api/security/settings/{key}
```

**Ejemplo:** `PUT /api/security/settings/max_failed_login_attempts`

**Request:**
```json
{
    "value": 10
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Configuración actualizada",
    "data": {
        "key": "max_failed_login_attempts",
        "value": 10
    }
}
```

---

### 5. Actualizar Múltiples Configuraciones
```http
PUT /api/security/settings
```

**Request:**
```json
{
    "settings": {
        "max_failed_login_attempts": 5,
        "block_duration_minutes": 60,
        "failed_login_window_minutes": 15
    }
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Configuraciones actualizadas",
    "data": {
        "max_failed_login_attempts": 5,
        "block_duration_minutes": 60,
        "failed_login_window_minutes": 15
    }
}
```

---

### 6. Limpiar Cache de Configuraciones
```http
POST /api/security/settings/clear-cache
```

**Response (200):**
```json
{
    "success": true,
    "message": "Cache de configuraciones limpiado"
}
```

---

## Endpoints de Sesiones

### 1. Mis Sesiones
```http
GET /api/security/sessions
```

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 15,
            "user_id": 14,
            "ip_address": "192.168.1.100",
            "device": "Chrome on Windows",
            "user_agent": "Mozilla/5.0...",
            "last_activity": 1732280400,
            "last_activity_human": "Hace 2 minutos",
            "is_active": true,
            "is_current": true
        },
        {
            "id": 12,
            "user_id": 14,
            "ip_address": "192.168.1.101",
            "device": "Firefox on MacOS",
            "last_activity_human": "Hace 1 hora",
            "is_active": false,
            "is_current": false
        }
    ]
}
```

---

### 2. Sesiones de un Usuario (Admin)
```http
GET /api/security/sessions?user_id={userId}
```

---

### 3. Todas las Sesiones Activas (Admin)
```http
GET /api/security/sessions/all
```

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "user_id": 1,
            "user_name": "Admin",
            "user_email": "admin@incadev.com",
            "total_sessions": 2,
            "unique_ips": 1,
            "sessions": [...]
        },
        {
            "user_id": 14,
            "user_name": "Maria Rojas",
            "user_email": "maria.security@incadev.com",
            "total_sessions": 1,
            "unique_ips": 1,
            "sessions": [...]
        }
    ]
}
```

---

### 4. Sesiones Sospechosas
```http
GET /api/security/sessions/suspicious
```

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 15,
            "user_id": 5,
            "ip_address": "192.168.1.100",
            "device": "Chrome on Windows",
            "is_suspicious": true
        },
        {
            "id": 16,
            "user_id": 5,
            "ip_address": "10.0.0.50",
            "device": "Safari on iOS",
            "is_suspicious": true
        }
    ],
    "message": "Se detectaron múltiples IPs activas para el mismo usuario"
}
```

---

### 5. Terminar Sesión Específica
```http
DELETE /api/security/sessions/{sessionId}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Sesión terminada exitosamente"
}
```

---

### 6. Terminar Todas Mis Sesiones
```http
POST /api/security/sessions/terminate-all
```

**Response (200):**
```json
{
    "success": true,
    "message": "3 sesiones terminadas exitosamente",
    "data": {
        "terminated_count": 3
    }
}
```

---

### 7. Terminar Sesiones de Usuario (Admin)
```http
POST /api/security/sessions/terminate-all?user_id={userId}
```

---

## Endpoints de Eventos

### 1. Mis Eventos
```http
GET /api/security/events?per_page=15
```

**Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 100,
            "user_id": 14,
            "event_type": "login_success",
            "event_label": "Inicio de sesión exitoso",
            "severity": "info",
            "severity_label": "Informativo",
            "severity_color": "blue",
            "ip_address": "192.168.1.100",
            "user_agent": "Mozilla/5.0...",
            "metadata": {
                "timestamp": "2025-11-22T10:00:00+00:00"
            },
            "created_at": "2025-11-22T10:00:00+00:00",
            "created_at_human": "hace 2 horas"
        },
        {
            "id": 99,
            "user_id": 14,
            "event_type": "suspicious_activity",
            "event_label": "Actividad sospechosa detectada",
            "severity": "critical",
            "severity_label": "Crítico",
            "severity_color": "red",
            "metadata": {
                "action": "user_blocked",
                "block_type": "automatic",
                "failed_attempts": 5
            },
            "created_at": "2025-11-22T09:30:00+00:00",
            "created_at_human": "hace 2 horas"
        }
    ],
    "meta": {
        "total": 50,
        "per_page": 15,
        "current_page": 1,
        "last_page": 4
    }
}
```

---

### 2. Eventos Recientes
```http
GET /api/security/events/recent?days=7
```

---

### 3. Eventos Críticos
```http
GET /api/security/events/critical?days=30
```

---

### 4. Estadísticas de Eventos
```http
GET /api/security/events/statistics?days=30
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "total": 150,
        "by_type": {
            "login_success": 80,
            "login_failed": 45,
            "logout": 15,
            "suspicious_activity": 10
        },
        "by_severity": {
            "info": 95,
            "warning": 45,
            "critical": 10
        },
        "critical_count": 10,
        "warning_count": 45,
        "info_count": 95
    }
}
```

---

## Dashboard de Seguridad

### Obtener Dashboard Completo
```http
GET /api/security/dashboard
```

**Response (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 14,
            "name": "Maria Rojas",
            "email": "maria.security@incadev.com",
            "two_factor_enabled": false,
            "has_recovery_email": true
        },
        "sessions_summary": {
            "total_active": 2,
            "unique_ips": 1,
            "has_suspicious": false,
            "suspicious_count": 0
        },
        "tokens_summary": {
            "total_active": 2,
            "total_inactive": 0,
            "total_expiring_soon": 0,
            "last_used": "2025-11-22T10:00:00+00:00"
        },
        "events_summary": {
            "total_last_30_days": 45,
            "critical_count": 2,
            "warning_count": 10,
            "info_count": 33
        },
        "alerts": [
            {
                "type": "critical_events",
                "message": "2 eventos críticos en los últimos 30 días",
                "count": 2
            }
        ]
    }
}
```

---

## Códigos de Estado HTTP

| Código | Significado | Uso |
|--------|-------------|-----|
| `200` | OK | Operación exitosa |
| `201` | Created | Recurso creado (bloqueo) |
| `400` | Bad Request | Error de validación o lógica |
| `401` | Unauthorized | No autenticado |
| `403` | Forbidden | Sin permisos |
| `404` | Not Found | Recurso no encontrado |
| `422` | Unprocessable Entity | Error de validación |
| `423` | Locked | **Usuario bloqueado** |
| `429` | Too Many Requests | **Límite de sesiones concurrentes** o Rate limit |
| `500` | Internal Server Error | Error del servidor |

---

## Manejo de Errores

### Error de Validación (422)
```json
{
    "success": false,
    "message": "Error de validación",
    "errors": {
        "user_id": ["El campo user_id es requerido"],
        "reason": ["El campo reason es requerido"]
    }
}
```

### Sin Permisos (403)
```json
{
    "success": false,
    "message": "No tienes permiso para ver los usuarios bloqueados"
}
```

### Usuario Bloqueado en Login (423)
```json
{
    "success": false,
    "message": "Tu cuenta está temporalmente bloqueada",
    "blocked": true,
    "block_info": {
        "reason": "Bloqueado automáticamente por 5 intentos fallidos de login en 10 minutos",
        "blocked_until": "2025-11-22T15:30:00+00:00",
        "remaining_time": "25 minutos"
    }
}
```

### Bloqueo Creado Tras Intentos Fallidos (423)
```json
{
    "success": false,
    "message": "Tu cuenta ha sido bloqueada temporalmente debido a múltiples intentos fallidos",
    "blocked": true,
    "block_info": {
        "blocked_until": "2025-11-22T15:30:00+00:00",
        "remaining_time": "30 minutos",
        "block_duration_minutes": 30
    }
}
```

### Límite de Sesiones Concurrentes (429)
```json
{
    "success": false,
    "message": "Has alcanzado el límite máximo de sesiones concurrentes",
    "session_limit_exceeded": true,
    "session_info": {
        "current_sessions": 5,
        "max_sessions": 5
    }
}
```

> **Acción recomendada:** El frontend debe mostrar al usuario sus sesiones activas y ofrecer la opción de cerrar otras sesiones antes de continuar con el login.

---

## Tipos de Eventos de Seguridad

| Tipo | Descripción | Severidad |
|------|-------------|-----------|
| `login_success` | Inicio de sesión exitoso | info |
| `login_failed` | Intento de inicio de sesión fallido | warning |
| `logout` | Cierre de sesión | info |
| `token_created` | Token creado | info |
| `token_revoked` | Token revocado | info |
| `session_terminated` | Sesión terminada | info |
| `password_reset_requested` | Solicitud de restablecimiento | warning |
| `password_changed` | Contraseña cambiada | info |
| `2fa_enabled` | 2FA habilitado | info |
| `2fa_disabled` | 2FA deshabilitado | warning |
| `recovery_email_added` | Email de recuperación agregado | info |
| `recovery_email_verified` | Email de recuperación verificado | info |
| `suspicious_activity` | Actividad sospechosa (bloqueos) | critical |
| `anomaly_detected` | Anomalía detectada | critical |

---

## Tipos de Bloqueo

| Tipo | Descripción |
|------|-------------|
| `automatic` | Bloqueo automático por intentos fallidos |
| `manual` | Bloqueo manual por administrador |

---

## Recomendaciones de Implementación Frontend

### 1. Pantalla de Login
- Mostrar mensaje claro cuando el usuario está bloqueado (código 423)
- Mostrar tiempo restante de bloqueo
- No revelar si el usuario existe o no (mismo mensaje para credenciales incorrectas)

### 2. Panel de Administración de Seguridad
- Dashboard con estadísticas generales
- Lista de usuarios bloqueados con opción de desbloquear
- Historial de bloqueos con filtros
- Configuración de parámetros de seguridad

### 3. Gestión de Sesiones
- Mostrar todas las sesiones activas del usuario
- Permitir cerrar sesiones individuales o todas
- Resaltar sesiones sospechosas (múltiples IPs)

### 4. Eventos de Seguridad
- Timeline de eventos con filtros por tipo y severidad
- Destacar eventos críticos
- Exportación de reportes (futuro)

---

## Ejemplo de Flujo Completo

### Flujo: Bloquear Usuario Manualmente

1. **Admin ve lista de usuarios** (otro módulo)
2. **Admin decide bloquear usuario sospechoso**
   ```http
   POST /api/security/blocks
   {
       "user_id": 5,
       "reason": "Actividad sospechosa reportada",
       "duration_minutes": 120
   }
   ```
3. **Sistema confirma bloqueo** (201)
4. **Usuario bloqueado intenta login** → Recibe 423
5. **Admin revisa bloqueos activos**
   ```http
   GET /api/security/blocks
   ```
6. **Admin decide desbloquear**
   ```http
   DELETE /api/security/blocks/user/5
   ```
7. **Usuario puede hacer login nuevamente**

---

*Documento generado el 22 de Noviembre de 2025*
