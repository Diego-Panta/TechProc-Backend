# Módulo de Seguridad - TechProc API (Consolidado)

## 📋 Resumen

El módulo de seguridad ha sido **consolidado** para eliminar redundancia. Ahora usamos **solo `SessionController`** para gestionar sesiones, ya que en Sanctum API:

```
Sesiones = Tokens Sanctum
```

No tiene sentido separar "sesiones" y "tokens" cuando son la misma entidad en la tabla `personal_access_tokens`.

---

## 🔄 Cambios Realizados

### ✅ Eliminado
- ❌ `TokenController` (redundante)
- ❌ Rutas `/api/security/tokens/*` (redundantes)
- ❌ `TokenService` y `TokenRepository` (mantenidos solo si se usan internamente)

### ✅ Consolidado
- ✅ `SessionController` gestiona TODAS las operaciones de sesiones/tokens
- ✅ Rutas simplificadas en `/api/security/sessions/*`
- ✅ Colección Postman actualizada y limpia

---

## 🛠️ Arquitectura del Módulo

```
┌─────────────────────────────────────────────────────────┐
│                  MÓDULO DE SEGURIDAD                    │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  📊 Dashboard                                           │
│  └─ /api/security/dashboard                             │
│                                                         │
│  🔐 Sesiones (Tokens Sanctum)                           │
│  ├─ GET    /api/security/sessions                       │
│  ├─ GET    /api/security/sessions/all                   │
│  ├─ GET    /api/security/sessions/suspicious            │
│  ├─ DELETE /api/security/sessions/{id}                  │
│  └─ POST   /api/security/sessions/terminate-all         │
│                                                         │
│  📝 Eventos de Seguridad                                │
│  ├─ GET    /api/security/events                         │
│  ├─ GET    /api/security/events/all                     │
│  ├─ GET    /api/security/events/recent                  │
│  ├─ GET    /api/security/events/critical                │
│  └─ GET    /api/security/events/statistics              │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 🔑 Endpoints Principales

### 1. **Ver Mis Sesiones**
```http
GET /api/security/sessions
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 5,
            "ip_address": "127.0.0.1",
            "device": "Postman on Windows",
            "last_activity_human": "Hace 2 minutos",
            "is_active": true,
            "is_current": false,
            "created_at": "2025-11-15T17:18:59Z",
            "last_used_at": "2025-11-15T17:20:00Z"
        }
    ],
    "user_id": 1
}
```

---

### 2. **Ver TODAS las Sesiones** (Solo Rol `security`)
```http
GET /api/security/sessions/all
Authorization: Bearer {token_security}
```

**Respuesta:**
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
            "user_name": "Maria Security",
            "user_email": "maria.security@incadev.com",
            "total_sessions": 1,
            "unique_ips": 1,
            "sessions": [...]
        }
    ],
    "total_users": 2,
    "total_sessions": 3
}
```

---

### 3. **Ver Sesiones de Usuario Específico** (Solo Rol `security`)
```http
GET /api/security/sessions?user_id=2
Authorization: Bearer {token_security}
```

**Respuesta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 7,
            "ip_address": "192.168.1.100",
            "device": "Chrome on Android",
            "last_activity_human": "Hace 5 minutos",
            "is_active": true,
            "is_current": false,
            "created_at": "2025-11-15T16:00:00Z",
            "last_used_at": "2025-11-15T17:15:00Z"
        }
    ],
    "user_id": 2
}
```

---

### 4. **Ver Sesiones Sospechosas**
```http
GET /api/security/sessions/suspicious
Authorization: Bearer {token}
```

**Usuario Normal:**
```json
{
    "success": true,
    "data": [
        {
            "id": 5,
            "ip_address": "127.0.0.1",
            "device": "Postman on Windows",
            "..."
        },
        {
            "id": 7,
            "ip_address": "192.168.1.100",
            "device": "Chrome on Android",
            "..."
        }
    ],
    "has_suspicious": true
}
```

**Rol Security (ve TODOS los usuarios con sesiones sospechosas):**
```json
{
    "success": true,
    "data": [
        {
            "user_id": 1,
            "user_name": "Admin",
            "user_email": "admin@incadev.com",
            "sessions": [...]
        }
    ],
    "total_users_with_suspicious": 1
}
```

---

### 5. **Terminar Sesión Específica**
```http
DELETE /api/security/sessions/7
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Sesión terminada exitosamente"
}
```

---

### 6. **Cerrar TODAS las Sesiones de un Usuario** (Rol `security`)
```http
POST /api/security/sessions/terminate-all?user_id=2
Authorization: Bearer {token_security}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Se terminaron 3 sesiones",
    "count": 3
}
```

⚠️ **IMPORTANTE**: Esto expulsa completamente al usuario del sistema.

---

## 🔐 Diferencias por Rol

| Acción | Usuario Normal | Rol `security` |
|--------|----------------|----------------|
| Ver propias sesiones | ✅ | ✅ |
| Ver sesiones de otro usuario | ❌ | ✅ (con `?user_id=X`) |
| Ver TODAS las sesiones | ❌ | ✅ (`/all`) |
| Terminar propia sesión | ✅ | ✅ |
| Terminar sesión de otro | ❌ | ✅ |
| Ver sesiones sospechosas propias | ✅ | ✅ |
| Ver sesiones sospechosas de TODOS | ❌ | ✅ |

---

## 📦 Importar en Postman

1. Importa `POSTMAN_SECURITY_MODULE.json`
2. Haz login con el endpoint "1. Authentication > Login"
3. El token se guarda automáticamente
4. Prueba los endpoints en orden:
   - **Sección 2**: Vista personal (cualquier usuario)
   - **Sección 3**: Vista administrativa (solo rol `security`)

---

## 🗃️ Base de Datos

### Tabla: `personal_access_tokens`
```sql
| Campo          | Descripción                              |
|----------------|------------------------------------------|
| id             | ID del token                             |
| tokenable_id   | ID del usuario propietario               |
| name           | Nombre del token (ej: "auth_token")      |
| token          | Hash del token (hasheado)                |
| abilities      | JSON con metadata: {ip, user_agent}      |
| last_used_at   | Última vez que se usó                    |
| expires_at     | Fecha de expiración (opcional)           |
| created_at     | Fecha de creación                        |
```

**IMPORTANTE**: El campo `abilities` ahora guarda:
```json
{
    "ip": "127.0.0.1",
    "user_agent": "PostmanRuntime/7.32.3"
}
```

---

## ⚙️ Modelo `ActiveToken`

El modelo `ActiveToken` extiende `PersonalAccessToken` de Sanctum y añade:

### Atributos Computados
- `ip_address`: Extrae IP desde `abilities`
- `user_agent`: Extrae user agent desde `abilities`
- `device`: Detecta navegador y SO desde user agent
- `is_active`: Token usado en últimos 30 minutos
- `last_activity_human`: "Hace X minutos/horas"

### Scopes
- `active()`: Tokens activos (últimos 30 min)
- `forUser($userId)`: Tokens de un usuario específico

---

## 🎯 Casos de Uso

### Caso 1: Usuario Sospecha que le Robaron la Cuenta
1. Usuario hace login
2. Ve sus sesiones: `GET /api/security/sessions`
3. Detecta una sesión desde IP desconocida
4. Cierra todas sus sesiones: `POST /api/security/sessions/terminate-all`

### Caso 2: Rol Security Detecta Actividad Sospechosa
1. Security revisa sesiones sospechosas: `GET /api/security/sessions/suspicious`
2. Ve que user_id=5 tiene sesiones desde 2 países
3. Revisa eventos del usuario: `GET /api/security/events?user_id=5`
4. Cierra todas las sesiones del usuario: `POST /api/security/sessions/terminate-all?user_id=5`
5. Contacta al usuario para confirmar

### Caso 3: Auditoría de Sesiones Activas
1. Security ve todas las sesiones: `GET /api/security/sessions/all`
2. Filtra por usuario específico: `GET /api/security/sessions?user_id=X`
3. Revisa eventos críticos: `GET /api/security/events/critical?days=7`

---

## 📝 Permisos Requeridos

### Permisos Básicos (Todos los usuarios)
- `security-dashboard.view`
- `sessions.view`
- `sessions.terminate`
- `security-events.view`

### Permisos Administrativos (Rol `security`)
- `security-dashboard.view-any`
- `sessions.view-any`
- `sessions.terminate-any`
- `security-events.view-any`
- `security-events.export`
- `security-alerts.view`
- `security-alerts.resolve`
- `security-users.view`
- `security-users.block`
- `security-users.unblock`

---

## 🧪 Testing

### Crear Sesiones de Prueba
```php
// Crear token con IP diferente
$user = User::find(1);
$token = $user->createToken('auth_token', [
    'ip' => '192.168.1.100',
    'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)'
]);
```

### Verificar Sesiones Sospechosas
```php
$repo = new SessionRepository();
$suspicious = $repo->getSuspiciousSessions(1);
// Devuelve sesiones si hay múltiples IPs activas
```

---

## ✅ Checklist de Implementación

- [x] Eliminar `TokenController` redundante
- [x] Consolidar rutas en `SessionController`
- [x] Actualizar `ActiveToken` model
- [x] Actualizar `SessionRepository`
- [x] Actualizar `SessionService`
- [x] Crear colección Postman consolidada
- [x] Documentar cambios
- [x] Limpiar cache de rutas

---

## 🚀 Próximos Pasos (Opcional)

- [ ] Agregar notificaciones por email cuando se detectan sesiones sospechosas
- [ ] Implementar bloqueo automático temporal por intentos fallidos
- [ ] Agregar 2FA (autenticación de dos factores)
- [ ] Implementar geolocalización de IPs
- [ ] Dashboard visual con gráficos de sesiones

---

**Autor**: Claude Code
**Fecha**: 2025-11-15
**Versión**: 2.0 (Consolidado)
