# Módulo de Seguridad - Documentación para Frontend

## 📋 Índice

1. [Descripción General](#descripción-general)
2. [Autenticación](#autenticación)
3. [Roles y Permisos](#roles-y-permisos)
4. [Endpoints Disponibles](#endpoints-disponibles)
5. [Gestión de Sesiones](#gestión-de-sesiones)
6. [Eventos de Seguridad](#eventos-de-seguridad)
7. [Dashboard de Seguridad](#dashboard-de-seguridad)
8. [Ejemplos de Integración](#ejemplos-de-integración)
9. [Manejo de Errores](#manejo-de-errores)
10. [Flujos de Usuario](#flujos-de-usuario)

---

## 📖 Descripción General

El módulo de seguridad permite a los usuarios:
- **Usuarios normales**: Ver y gestionar sus propias sesiones activas
- **Usuarios admin**: Ver y gestionar sesiones de TODOS los usuarios del sistema

### Conceptos Clave

- **Sesión = Token Sanctum**: En esta API, cada sesión representa un token de autenticación activo
- **Sesión Activa**: Token usado en los últimos 30 minutos
- **Sesión Sospechosa**: Usuario con múltiples IPs activas simultáneamente

---

## 🔐 Autenticación

### Base URL
```
http://localhost:8000/api
```

### Login
```http
POST /login
Content-Type: application/json

{
    "email": "usuario@incadev.com",
    "password": "password",
    "role": "admin"
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "message": "Login exitoso",
    "data": {
        "user": {
            "id": 1,
            "name": "Admin",
            "email": "admin@incadev.com",
            "roles": ["admin"],
            "permissions": ["sessions.view-any", "sessions.terminate-any", ...]
        },
        "token": "1|abc123xyz..."
    }
}
```

**Guardar el token**:
```javascript
// Guardar en localStorage/sessionStorage
localStorage.setItem('auth_token', response.data.token);
localStorage.setItem('user', JSON.stringify(response.data.user));
```

### Autenticación en Requests

Todas las peticiones al módulo de seguridad requieren el header:
```http
Authorization: Bearer {token}
```

---

## 👥 Roles y Permisos

### Roles con Acceso al Módulo

| Rol | Acceso |
|-----|--------|
| `admin` | ✅ Acceso completo (ver y gestionar sesiones de TODOS) |
| `security` | ✅ Acceso completo (rol especializado en seguridad) |
| `super_admin` | ✅ Acceso completo |
| Otros roles | ❌ Sin acceso al módulo de seguridad |

### Verificar Permisos del Usuario

```javascript
const user = JSON.parse(localStorage.getItem('user'));

// Verificar si tiene rol admin
const isAdmin = user.roles.includes('admin') ||
                user.roles.includes('security') ||
                user.roles.includes('super_admin');

// Verificar permisos específicos
const canViewAllSessions = user.permissions.includes('sessions.view-any');
const canTerminateAnySessions = user.permissions.includes('sessions.terminate-any');
```

### Permisos Principales

| Permiso | Descripción |
|---------|-------------|
| `sessions.view` | Ver propias sesiones |
| `sessions.view-any` | Ver sesiones de todos los usuarios |
| `sessions.terminate` | Terminar propias sesiones |
| `sessions.terminate-any` | Terminar sesiones de cualquier usuario |
| `security-events.view` | Ver propios eventos de seguridad |
| `security-events.view-any` | Ver eventos de todos los usuarios |

---

## 🔌 Endpoints Disponibles

### 1. Dashboard de Seguridad

#### Ver Dashboard
```http
GET /security/dashboard
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "sessions_summary": {
            "total_active": 5,
            "unique_ips": 3,
            "has_suspicious": true,
            "suspicious_count": 2
        },
        "recent_events": [...],
        "critical_events": [...],
        "statistics": {
            "total_logins": 150,
            "failed_attempts": 5,
            "anomalies_detected": 2
        }
    }
}
```

---

## 🖥️ Gestión de Sesiones

### 1.1 Ver Mis Sesiones Activas

```http
GET /security/sessions
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
            "device": "Chrome on Windows",
            "last_activity_human": "Hace 2 minutos",
            "is_active": true,
            "is_current": false,
            "created_at": "2025-11-15T17:18:59Z",
            "last_used_at": "2025-11-15T17:20:00Z"
        },
        {
            "id": 7,
            "ip_address": "192.168.1.100",
            "device": "Safari on iPhone",
            "last_activity_human": "Hace 1 hora",
            "is_active": false,
            "is_current": false,
            "created_at": "2025-11-15T16:00:00Z",
            "last_used_at": "2025-11-15T16:30:00Z"
        }
    ],
    "user_id": 1
}
```

**Renderizar en UI:**
```javascript
// Ejemplo React
{sessions.map(session => (
    <div key={session.id} className={session.is_active ? 'active' : 'inactive'}>
        <div className="device">{session.device}</div>
        <div className="ip">{session.ip_address}</div>
        <div className="time">{session.last_activity_human}</div>
        {!session.is_current && (
            <button onClick={() => terminateSession(session.id)}>
                Cerrar Sesión
            </button>
        )}
    </div>
))}
```

---

### 1.2 Ver TODAS las Sesiones (Solo Admin)

```http
GET /security/sessions/all
Authorization: Bearer {token}
```

**Requiere**: Permiso `sessions.view-any`

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
            "sessions": [
                {
                    "id": 5,
                    "ip_address": "127.0.0.1",
                    "device": "Chrome on Windows",
                    "last_activity_human": "Hace 2 minutos",
                    "is_active": true,
                    "is_current": false,
                    "created_at": "2025-11-15T17:18:59Z",
                    "last_used_at": "2025-11-15T17:20:00Z"
                }
            ]
        },
        {
            "user_id": 10,
            "user_name": "Martin",
            "user_email": "martin@incadev.com",
            "total_sessions": 1,
            "unique_ips": 1,
            "sessions": [...]
        }
    ],
    "total_users": 2,
    "total_sessions": 3
}
```

**UI Sugerida:**
- Lista de usuarios con sesiones activas
- Contador de sesiones por usuario
- Indicador de IPs únicas (alerta si > 1)
- Botón para ver detalles de cada usuario

---

### 1.3 Ver Sesiones de Usuario Específico (Solo Admin)

```http
GET /security/sessions?user_id=10
Authorization: Bearer {token}
```

**Requiere**: Permiso `sessions.view-any`

**Respuesta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 15,
            "ip_address": "192.168.1.50",
            "device": "Chrome on Android",
            "last_activity_human": "Hace 5 minutos",
            "is_active": true,
            "is_current": false,
            "created_at": "2025-11-15T16:00:00Z",
            "last_used_at": "2025-11-15T17:15:00Z"
        }
    ],
    "user_id": 10
}
```

---

### 1.4 Ver Sesiones Sospechosas

```http
GET /security/sessions/suspicious
Authorization: Bearer {token}
```

**Usuario Normal** (ve solo sus sesiones sospechosas):
```json
{
    "success": true,
    "data": [
        {
            "id": 5,
            "ip_address": "127.0.0.1",
            "device": "Chrome on Windows",
            "..."
        },
        {
            "id": 7,
            "ip_address": "192.168.1.100",
            "device": "Safari on iPhone",
            "..."
        }
    ],
    "has_suspicious": true
}
```

**Admin** (ve sesiones sospechosas de TODOS):
```json
{
    "success": true,
    "data": [
        {
            "user_id": 5,
            "user_name": "Juan",
            "user_email": "juan@incadev.com",
            "sessions": [...]
        }
    ],
    "total_users_with_suspicious": 1
}
```

**UI Sugerida:**
- Badge/Alert rojo para sesiones sospechosas
- Mensaje: "Detectamos accesos desde múltiples ubicaciones"
- Botón "Cerrar todas las sesiones"

---

### 1.5 Terminar Sesión Específica

```http
DELETE /security/sessions/{sessionId}
Authorization: Bearer {token}
```

**Ejemplo:**
```http
DELETE /security/sessions/15
Authorization: Bearer {token}
```

**Respuesta Exitosa:**
```json
{
    "success": true,
    "message": "Sesión terminada exitosamente"
}
```

**Código Frontend:**
```javascript
async function terminateSession(sessionId) {
    try {
        const response = await fetch(`${BASE_URL}/security/sessions/${sessionId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            // Actualizar lista de sesiones
            refreshSessions();
            showNotification('Sesión cerrada exitosamente', 'success');
        }
    } catch (error) {
        showNotification('Error al cerrar sesión', 'error');
    }
}
```

---

### 1.6 Cerrar Todas las Sesiones

#### Para Usuario Normal (cierra sus propias sesiones excepto la actual)

```http
POST /security/sessions/terminate-all
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Se terminaron 3 sesiones",
    "count": 3
}
```

#### Para Admin (cerrar sesiones de otro usuario)

```http
POST /security/sessions/terminate-all?user_id=10
Authorization: Bearer {token}
```

**Requiere**: Permiso `sessions.terminate-any`

**Respuesta:**
```json
{
    "success": true,
    "message": "Se terminaron 2 sesiones",
    "count": 2
}
```

**⚠️ IMPORTANTE**: Esto expulsa completamente al usuario del sistema.

**Código Frontend con Confirmación:**
```javascript
async function terminateAllSessions(userId = null) {
    const confirmMessage = userId
        ? '¿Estás seguro de cerrar TODAS las sesiones de este usuario?'
        : '¿Cerrar todas tus sesiones? (excepto la actual)';

    if (!confirm(confirmMessage)) return;

    const url = userId
        ? `${BASE_URL}/security/sessions/terminate-all?user_id=${userId}`
        : `${BASE_URL}/security/sessions/terminate-all`;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification(`Se cerraron ${data.count} sesiones`, 'success');
            refreshSessions();
        }
    } catch (error) {
        showNotification('Error al cerrar sesiones', 'error');
    }
}
```

---

## 📝 Eventos de Seguridad

### 2.1 Ver Mis Eventos

```http
GET /security/events?per_page=20
Authorization: Bearer {token}
```

**Parámetros Query:**
- `per_page`: Eventos por página (default: 15)
- `page`: Número de página (default: 1)

**Respuesta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "event_type": "login_success",
            "severity": "info",
            "ip_address": "127.0.0.1",
            "user_agent": "Mozilla/5.0...",
            "metadata": {
                "device": "Chrome on Windows"
            },
            "created_at": "2025-11-15T17:20:00Z"
        },
        {
            "id": 2,
            "event_type": "login_failed",
            "severity": "warning",
            "ip_address": "192.168.1.100",
            "metadata": {
                "reason": "invalid_credentials"
            },
            "created_at": "2025-11-15T16:00:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 45
    }
}
```

### Tipos de Eventos

| Tipo | Severidad | Descripción |
|------|-----------|-------------|
| `login_success` | info | Login exitoso |
| `login_failed` | warning | Login fallido |
| `logout` | info | Logout |
| `token_created` | info | Token creado |
| `token_revoked` | info | Token revocado |
| `session_terminated` | info | Sesión terminada |
| `password_reset_requested` | info | Solicitud de reset de contraseña |
| `password_changed` | warning | Contraseña cambiada |
| `suspicious_activity` | critical | Actividad sospechosa detectada |
| `anomaly_detected` | critical | Anomalía detectada |

### Severidades

| Severidad | Color UI | Descripción |
|-----------|----------|-------------|
| `info` | Azul | Evento normal |
| `warning` | Amarillo | Requiere atención |
| `critical` | Rojo | Requiere acción inmediata |

---

### 2.2 Ver Eventos Recientes del Sistema (Solo Admin)

```http
GET /security/events/recent?hours=24
Authorization: Bearer {token}
```

**Requiere**: Permiso `security-events.view-any`

**Parámetros:**
- `hours`: Horas hacia atrás (default: 24)

---

### 2.3 Ver Eventos Críticos (Solo Admin)

```http
GET /security/events/critical?days=7
Authorization: Bearer {token}
```

**Requiere**: Permiso `security-events.view-any`

**Parámetros:**
- `days`: Días hacia atrás (default: 7)

**Respuesta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 15,
            "event_type": "anomaly_detected",
            "severity": "critical",
            "user_id": 10,
            "ip_address": "203.0.113.0",
            "metadata": {
                "type": "multiple_ips",
                "ips": ["127.0.0.1", "192.168.1.100", "203.0.113.0"]
            },
            "created_at": "2025-11-15T14:00:00Z"
        }
    ]
}
```

---

### 2.4 Estadísticas de Eventos

```http
GET /security/events/statistics?days=30
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "total_events": 1250,
        "by_type": {
            "login_success": 850,
            "login_failed": 45,
            "anomaly_detected": 2
        },
        "by_severity": {
            "info": 1200,
            "warning": 48,
            "critical": 2
        },
        "events_per_day": [
            {"date": "2025-11-15", "count": 50},
            {"date": "2025-11-14", "count": 48}
        ]
    }
}
```

---

## 📊 Dashboard de Seguridad

### Vista Completa

```http
GET /security/dashboard
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "sessions_summary": {
            "total_active": 5,
            "unique_ips": 3,
            "has_suspicious": true,
            "suspicious_count": 2
        },
        "recent_events": [
            {
                "id": 1,
                "event_type": "login_success",
                "created_at": "2025-11-15T17:20:00Z"
            }
        ],
        "critical_alerts": [
            {
                "id": 15,
                "event_type": "anomaly_detected",
                "user_id": 10,
                "created_at": "2025-11-15T14:00:00Z"
            }
        ],
        "statistics": {
            "total_logins_today": 150,
            "failed_attempts_today": 5,
            "anomalies_detected": 2
        }
    }
}
```

---

## 💡 Ejemplos de Integración

### Componente React - Lista de Sesiones

```jsx
import React, { useState, useEffect } from 'react';

const SessionsList = () => {
    const [sessions, setSessions] = useState([]);
    const [loading, setLoading] = useState(true);
    const token = localStorage.getItem('auth_token');

    useEffect(() => {
        fetchSessions();
    }, []);

    const fetchSessions = async () => {
        try {
            const response = await fetch('http://localhost:8000/api/security/sessions', {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();

            if (data.success) {
                setSessions(data.data);
            }
        } catch (error) {
            console.error('Error fetching sessions:', error);
        } finally {
            setLoading(false);
        }
    };

    const terminateSession = async (sessionId) => {
        if (!confirm('¿Cerrar esta sesión?')) return;

        try {
            const response = await fetch(`http://localhost:8000/api/security/sessions/${sessionId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();

            if (data.success) {
                alert('Sesión cerrada exitosamente');
                fetchSessions(); // Refresh
            }
        } catch (error) {
            alert('Error al cerrar sesión');
        }
    };

    if (loading) return <div>Cargando...</div>;

    return (
        <div className="sessions-list">
            <h2>Sesiones Activas ({sessions.length})</h2>
            {sessions.map(session => (
                <div key={session.id} className={`session-card ${session.is_active ? 'active' : 'inactive'}`}>
                    <div className="session-info">
                        <strong>{session.device}</strong>
                        <p>IP: {session.ip_address}</p>
                        <small>{session.last_activity_human}</small>
                    </div>
                    <button
                        onClick={() => terminateSession(session.id)}
                        className="btn-danger"
                    >
                        Cerrar Sesión
                    </button>
                </div>
            ))}
        </div>
    );
};

export default SessionsList;
```

---

### Servicio API (JavaScript/TypeScript)

```typescript
// api/security.service.ts
import axios from 'axios';

const BASE_URL = 'http://localhost:8000/api';

class SecurityService {
    private getHeaders() {
        const token = localStorage.getItem('auth_token');
        return {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        };
    }

    // Sesiones
    async getMySessions() {
        const response = await axios.get(`${BASE_URL}/security/sessions`, {
            headers: this.getHeaders()
        });
        return response.data;
    }

    async getAllSessions() {
        const response = await axios.get(`${BASE_URL}/security/sessions/all`, {
            headers: this.getHeaders()
        });
        return response.data;
    }

    async getUserSessions(userId: number) {
        const response = await axios.get(`${BASE_URL}/security/sessions?user_id=${userId}`, {
            headers: this.getHeaders()
        });
        return response.data;
    }

    async getSuspiciousSessions() {
        const response = await axios.get(`${BASE_URL}/security/sessions/suspicious`, {
            headers: this.getHeaders()
        });
        return response.data;
    }

    async terminateSession(sessionId: number) {
        const response = await axios.delete(`${BASE_URL}/security/sessions/${sessionId}`, {
            headers: this.getHeaders()
        });
        return response.data;
    }

    async terminateAllSessions(userId?: number) {
        const url = userId
            ? `${BASE_URL}/security/sessions/terminate-all?user_id=${userId}`
            : `${BASE_URL}/security/sessions/terminate-all`;

        const response = await axios.post(url, {}, {
            headers: this.getHeaders()
        });
        return response.data;
    }

    // Eventos
    async getMyEvents(perPage = 20, page = 1) {
        const response = await axios.get(`${BASE_URL}/security/events`, {
            headers: this.getHeaders(),
            params: { per_page: perPage, page }
        });
        return response.data;
    }

    async getCriticalEvents(days = 7) {
        const response = await axios.get(`${BASE_URL}/security/events/critical`, {
            headers: this.getHeaders(),
            params: { days }
        });
        return response.data;
    }

    // Dashboard
    async getDashboard() {
        const response = await axios.get(`${BASE_URL}/security/dashboard`, {
            headers: this.getHeaders()
        });
        return response.data;
    }
}

export default new SecurityService();
```

---

## ⚠️ Manejo de Errores

### Códigos de Estado HTTP

| Código | Significado | Acción Frontend |
|--------|-------------|-----------------|
| 200 | Éxito | Mostrar datos |
| 401 | No autenticado | Redirigir a login |
| 403 | Sin permisos | Mostrar mensaje "No autorizado" |
| 404 | No encontrado | Mostrar "Recurso no encontrado" |
| 422 | Error de validación | Mostrar errores de campo |
| 429 | Demasiados intentos | Mostrar "Intenta más tarde" |
| 500 | Error del servidor | Mostrar "Error inesperado" |

### Ejemplo de Manejo

```javascript
async function fetchSessions() {
    try {
        const response = await fetch(`${BASE_URL}/security/sessions`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        // Verificar estado HTTP
        if (response.status === 401) {
            // Token expirado o inválido
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
            return;
        }

        if (response.status === 403) {
            // Sin permisos
            showNotification('No tienes permisos para acceder a esta sección', 'error');
            return;
        }

        const data = await response.json();

        if (!data.success) {
            showNotification(data.message || 'Error desconocido', 'error');
            return;
        }

        // Éxito
        setSessions(data.data);

    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión con el servidor', 'error');
    }
}
```

---

## 🔄 Flujos de Usuario

### Flujo 1: Usuario Normal - Ver Mis Sesiones

```
1. Usuario hace login → Recibe token
2. Navega a "Mis Sesiones"
3. Frontend: GET /security/sessions
4. Mostrar lista de sesiones con:
   - Dispositivo y SO
   - IP
   - Última actividad
   - Estado (activa/inactiva)
   - Botón "Cerrar Sesión"
5. Usuario hace clic en "Cerrar Sesión"
6. Frontend: DELETE /security/sessions/{id}
7. Actualizar lista
```

### Flujo 2: Usuario Normal - Detecta Sesión Sospechosa

```
1. Frontend: GET /security/sessions/suspicious
2. Si has_suspicious = true:
   - Mostrar alerta roja
   - "Detectamos accesos desde múltiples ubicaciones"
   - Listar IPs y dispositivos
   - Botón "Cerrar todas las sesiones"
3. Usuario hace clic en "Cerrar todas"
4. Frontend: POST /security/sessions/terminate-all
5. Mostrar confirmación
6. Actualizar lista
```

### Flujo 3: Admin - Gestionar Sesiones de Usuario

```
1. Admin navega a "Gestión de Sesiones"
2. Frontend: GET /security/sessions/all
3. Mostrar tabla con:
   - Lista de usuarios
   - Sesiones activas por usuario
   - IPs únicas (alerta si > 1)
4. Admin hace clic en usuario
5. Frontend: GET /security/sessions?user_id=X
6. Mostrar detalles de sesiones del usuario
7. Admin puede:
   - Cerrar sesión específica: DELETE /security/sessions/{id}
   - Cerrar todas: POST /security/sessions/terminate-all?user_id=X
```

### Flujo 4: Admin - Dashboard de Seguridad

```
1. Admin navega a "Dashboard de Seguridad"
2. Frontend: GET /security/dashboard
3. Mostrar:
   - Resumen de sesiones activas
   - Gráfico de eventos (últimos 7 días)
   - Alertas críticas
   - Usuarios con sesiones sospechosas
4. Admin hace clic en alerta crítica
5. Frontend: GET /security/events/critical
6. Mostrar detalles del evento
7. Admin puede tomar acción (cerrar sesiones, etc.)
```

---

## 📋 Checklist de Implementación Frontend

### Fase 1: Autenticación
- [ ] Implementar login y guardar token
- [ ] Crear interceptor para agregar Authorization header
- [ ] Manejar token expirado (401) y redirigir a login
- [ ] Guardar información del usuario y roles

### Fase 2: Vista de Usuario Normal
- [ ] Página "Mis Sesiones"
  - [ ] Listar sesiones activas
  - [ ] Mostrar detalles (IP, dispositivo, última actividad)
  - [ ] Botón "Cerrar Sesión" por sesión
  - [ ] Botón "Cerrar Todas Mis Sesiones"
- [ ] Alerta de sesiones sospechosas
  - [ ] Badge/indicador si hay sesiones sospechosas
  - [ ] Modal con detalles
  - [ ] Acción rápida "Cerrar Todas"

### Fase 3: Vista Admin (Solo si el usuario tiene permiso)
- [ ] Verificar permisos antes de mostrar opciones admin
- [ ] Página "Gestión de Sesiones"
  - [ ] Tabla de usuarios con sesiones activas
  - [ ] Filtros y búsqueda
  - [ ] Vista detallada por usuario
  - [ ] Acciones: Cerrar sesión específica, Cerrar todas
- [ ] Página "Eventos de Seguridad"
  - [ ] Tabla de eventos con paginación
  - [ ] Filtros (tipo, severidad, fecha)
  - [ ] Detalles de evento
- [ ] Dashboard de Seguridad
  - [ ] Resumen con métricas
  - [ ] Gráficos de eventos
  - [ ] Alertas críticas
  - [ ] Accesos rápidos

### Fase 4: Notificaciones
- [ ] Notificaciones de éxito/error
- [ ] Confirmaciones antes de acciones destructivas
- [ ] Actualización automática de listas después de acciones

---

## 🎨 Sugerencias de UI/UX

### Sesiones Activas
```
┌─────────────────────────────────────────┐
│ 🖥️ Chrome on Windows                    │
│ IP: 127.0.0.1                          │
│ 🕐 Hace 2 minutos                       │
│ [Cerrar Sesión]                        │
└─────────────────────────────────────────┘
```

### Alerta de Sesión Sospechosa
```
┌────────────────────────────────────────────┐
│ ⚠️ ALERTA DE SEGURIDAD                    │
│                                            │
│ Detectamos accesos desde múltiples        │
│ ubicaciones:                              │
│ • 127.0.0.1 (Windows)                     │
│ • 192.168.1.100 (iPhone)                  │
│                                            │
│ [Ver Detalles] [Cerrar Todas las Sesiones]│
└────────────────────────────────────────────┘
```

### Dashboard Admin
```
┌─────────────────────┬──────────────────┐
│ Sesiones Activas: 5 │ Usuarios: 3      │
├─────────────────────┼──────────────────┤
│ Eventos Hoy: 150    │ Alertas: 2       │
└─────────────────────┴──────────────────┘

Usuarios con Sesiones Activas
┌────────────────────────────────────┐
│ admin@incadev.com                  │
│ 2 sesiones · 1 IP única            │
│ [Ver Detalles]                     │
├────────────────────────────────────┤
│ martin@incadev.com         ⚠️      │
│ 3 sesiones · 2 IPs únicas          │
│ [Ver Detalles] [Cerrar Sesiones]  │
└────────────────────────────────────┘
```

---

## 📞 Soporte

Para dudas o problemas con la integración, contactar al equipo de backend.

**Repositorio Backend**: `TechProc-Backend`
**Colección Postman**: Importar `POSTMAN_SECURITY_MODULE.json`

---

**Versión**: 1.0
**Fecha**: 2025-11-15
**Autor**: Equipo Backend TechProc
