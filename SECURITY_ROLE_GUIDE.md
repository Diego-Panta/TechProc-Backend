# 🛡️ Rol `security` - Guía de Administración de Seguridad

## 📋 Descripción del Rol

El rol `security` es el **encargado de velar por la seguridad y monitoreo de TODOS los usuarios** del sistema.

### Diferencias con otros roles:

| Característica | Usuario Normal | Rol `admin` | Rol `security` | Rol `super_admin` |
|----------------|----------------|-------------|----------------|-------------------|
| Ver sus propias sesiones | ✅ | ✅ | ✅ | ✅ |
| Ver sesiones de TODOS | ❌ | ✅ (solo lectura) | ✅ (lectura + control) | ✅ |
| Terminar sesiones de otros | ❌ | ❌ | ✅ | ✅ |
| Ver eventos de TODOS | ❌ | ✅ (solo lectura) | ✅ (lectura + análisis) | ✅ |
| Revocar tokens de otros | ❌ | ❌ | ✅ | ✅ |
| Bloquear usuarios | ❌ | ❌ | ✅ | ✅ |

---

## 🔑 Permisos del Rol `security`

### Permisos Básicos (todos los usuarios los tienen)
- `security-dashboard.view` - Ver mi dashboard
- `sessions.view` - Ver mis sesiones
- `sessions.terminate` - Terminar mis sesiones
- `tokens.view` - Ver mis tokens
- `tokens.revoke` - Revocar mis tokens
- `security-events.view` - Ver mis eventos

### Permisos Administrativos (solo `security` y `super_admin`)
- `security-dashboard.view-any` - Ver dashboard de CUALQUIER usuario
- `sessions.view-any` - Ver sesiones de TODOS
- `sessions.terminate-any` - Terminar sesiones de CUALQUIERA
- `tokens.view-any` - Ver tokens de TODOS
- `tokens.revoke-any` - Revocar tokens de CUALQUIERA
- `security-events.view-any` - Ver eventos de TODOS
- `security-events.export` - Exportar reportes
- `security-alerts.view` - Ver alertas del sistema
- `security-alerts.resolve` - Resolver alertas
- `security-users.view` - Ver lista de usuarios con alertas
- `security-users.block` - Bloquear usuarios
- `security-users.unblock` - Desbloquear usuarios

**Total**: 18 permisos (6 básicos + 12 administrativos)

---

## 🚀 Cómo Usar los Endpoints como Rol `security`

### 1. Ver TODAS las sesiones activas del sistema

```http
GET /api/security/sessions/all
Authorization: Bearer {token_del_usuario_security}

Response:
{
    "success": true,
    "data": [
        {
            "user_id": 1,
            "user_name": "Admin",
            "user_email": "admin@incadev.com",
            "total_sessions": 2,
            "unique_ips": 2,
            "sessions": [...]
        },
        {
            "user_id": 5,
            "user_name": "John Doe",
            "user_email": "john@example.com",
            "total_sessions": 1,
            "unique_ips": 1,
            "sessions": [...]
        }
    ],
    "total_users": 2,
    "total_sessions": 3
}
```

### 2. Ver sesiones de un usuario específico

```http
GET /api/security/sessions?user_id=5
Authorization: Bearer {token_del_usuario_security}

Response:
{
    "success": true,
    "data": [
        {
            "id": "abc123",
            "user_id": 5,
            "ip_address": "192.168.1.100",
            "device": "Chrome on Windows",
            "last_activity_human": "Hace 5 minutos",
            "is_current": false
        }
    ],
    "user_id": 5
}
```

### 3. Ver sesiones sospechosas de TODOS los usuarios

```http
GET /api/security/sessions/suspicious
Authorization: Bearer {token_del_usuario_security}

Response:
{
    "success": true,
    "data": [
        {
            "user_id": 5,
            "user_name": "John Doe",
            "user_email": "john@example.com",
            "sessions": [
                {
                    "id": "abc123",
                    "ip_address": "192.168.1.100",
                    "device": "Chrome on Windows"
                },
                {
                    "id": "def456",
                    "ip_address": "200.48.12.50",
                    "device": "Safari on iPhone"
                }
            ]
        }
    ],
    "total_users_with_suspicious": 1
}
```

### 4. Terminar sesión de otro usuario

```http
DELETE /api/security/sessions/{session_id}
Authorization: Bearer {token_del_usuario_security}

Response:
{
    "success": true,
    "message": "Sesión terminada exitosamente"
}
```

### 5. Terminar TODAS las sesiones de un usuario

```http
POST /api/security/sessions/terminate-all?user_id=5
Authorization: Bearer {token_del_usuario_security}

Response:
{
    "success": true,
    "message": "Se terminaron 2 sesiones",
    "count": 2
}
```

### 6. Ver TODOS los tokens del sistema

```http
GET /api/security/tokens/all
Authorization: Bearer {token_del_usuario_security}

Response:
{
    "success": true,
    "data": [
        {
            "user_id": 1,
            "user_name": "Admin",
            "user_email": "admin@incadev.com",
            "total_tokens": 3,
            "tokens": [...]
        }
    ],
    "total_users": 5,
    "total_tokens": 15
}
```

### 7. Ver TODOS los eventos de seguridad

```http
GET /api/security/events/all
Authorization: Bearer {token_del_usuario_security}

Response:
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 5,
            "user_name": "John Doe",
            "event_type": "login_failed",
            "severity": "warning",
            "ip_address": "200.48.12.50",
            "created_at_human": "Hace 10 minutos"
        }
    ],
    "total": 150
}
```

---

## 🎯 Casos de Uso Comunes

### Caso 1: Detectar usuario con actividad sospechosa

**Problema**: Múltiples logins fallidos desde diferentes IPs

**Solución**:
```bash
# 1. Ver sesiones sospechosas
GET /api/security/sessions/suspicious

# 2. Ver eventos críticos del usuario
GET /api/security/events?user_id=5

# 3. Si es necesario, terminar todas sus sesiones
POST /api/security/sessions/terminate-all?user_id=5
```

### Caso 2: Auditoría de seguridad

**Problema**: Revisar actividad de todos los usuarios

**Solución**:
```bash
# 1. Ver todas las sesiones activas
GET /api/security/sessions/all

# 2. Ver eventos críticos de los últimos 7 días
GET /api/security/events/critical?days=7

# 3. Ver estadísticas generales
GET /api/security/events/statistics?days=30
```

### Caso 3: Incidente de seguridad

**Problema**: Cuenta comprometida, cerrar todo

**Solución**:
```bash
# 1. Verificar sesiones del usuario
GET /api/security/sessions?user_id=5

# 2. Terminar TODAS las sesiones
POST /api/security/sessions/terminate-all?user_id=5

# 3. Revocar TODOS los tokens
POST /api/security/tokens/revoke-all?user_id=5

# 4. Ver eventos para análisis
GET /api/security/events?user_id=5
```

---

## 🔒 Control de Acceso

### Usuario Normal
```javascript
// Solo puede ver SU PROPIA información
GET /api/security/sessions              // ✅ Sus sesiones
GET /api/security/sessions?user_id=5    // ❌ 403 Forbidden
GET /api/security/sessions/all          // ❌ 403 Forbidden
```

### Rol `security`
```javascript
// Puede ver información de TODOS
GET /api/security/sessions              // ✅ Sus sesiones
GET /api/security/sessions?user_id=5    // ✅ Sesiones del usuario 5
GET /api/security/sessions/all          // ✅ TODAS las sesiones
DELETE /api/security/sessions/{id}      // ✅ Puede terminar CUALQUIER sesión
POST /api/security/tokens/revoke-all?user_id=5  // ✅ Puede revocar tokens de otros
```

---

## 📝 Registro de Acciones

**Todas las acciones del rol `security` se registran en eventos**:

```json
{
    "event_type": "session_terminated",
    "user_id": 10,  // Usuario security que ejecutó la acción
    "metadata": {
        "action": "terminate_all",
        "target_user_id": 5,  // Usuario afectado
        "count": 2
    }
}
```

Esto crea una **auditoría completa** de todas las acciones administrativas.

---

## 🚨 Endpoints Exclusivos del Rol `security`

| Endpoint | Descripción | Método |
|----------|-------------|--------|
| `/api/security/sessions/all` | Ver TODAS las sesiones | GET |
| `/api/security/sessions?user_id=X` | Ver sesiones de usuario X | GET |
| `/api/security/sessions/suspicious` | Sesiones sospechosas de TODOS | GET |
| `DELETE /api/security/sessions/{id}` | Terminar sesión de otro | DELETE |
| `POST /api/security/sessions/terminate-all?user_id=X` | Cerrar sesiones de otro | POST |
| `/api/security/tokens/all` | Ver TODOS los tokens | GET |
| `/api/security/tokens?user_id=X` | Ver tokens de usuario X | GET |
| `POST /api/security/tokens/revoke-all?user_id=X` | Revocar tokens de otro | POST |
| `/api/security/events/all` | Ver TODOS los eventos | GET |
| `/api/security/events?user_id=X` | Ver eventos de usuario X | GET |

---

## ✅ Resumen

El rol `security`:
- ✅ Tiene acceso **global** a información de seguridad de **todos los usuarios**
- ✅ Puede **terminar sesiones** de cualquier usuario
- ✅ Puede **revocar tokens** de cualquier usuario
- ✅ Puede **ver eventos** de todos los usuarios
- ✅ Todas sus acciones quedan **auditadas**
- ✅ Es el **responsable del monitoreo** y **seguridad** del sistema

---

## 🎉 ¡Listo para usar!

Ahora el módulo de seguridad está adaptado para el rol `security` con acceso administrativo completo.
