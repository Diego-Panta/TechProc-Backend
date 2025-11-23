# API de Administración - Documentación Frontend

## Información General

**Base URL:** `http://localhost:8000/api`

**Autenticación:** Todos los endpoints requieren autenticación con Sanctum.
```
Header: Authorization: Bearer {token}
```

**Content-Type:** `application/json`

---

## Índice

1. [Dashboard de Administración](#1-dashboard-de-administración)
2. [Gestión de Usuarios](#2-gestión-de-usuarios)
3. [Gestión de Roles](#3-gestión-de-roles)
4. [Gestión de Permisos](#4-gestión-de-permisos)

---

## 1. Dashboard de Administración

### GET `/admin/dashboard`

Obtiene estadísticas generales para el panel de administración.

#### Request
```http
GET /api/admin/dashboard
Authorization: Bearer {token}
```

#### Response (200 OK)
```json
{
  "success": true,
  "message": "Dashboard de administración",
  "data": {
    "users": {
      "total": 150,
      "with_roles": 145,
      "without_roles": 5,
      "with_2fa": 50,
      "without_2fa": 100,
      "verified_emails": 140,
      "unverified_emails": 10,
      "new_last_30_days": 25,
      "by_role": [
        { "role": "student", "count": 80 },
        { "role": "teacher", "count": 30 },
        { "role": "admin", "count": 5 }
      ]
    },
    "roles": {
      "total": 18,
      "with_users": 15,
      "without_users": 3,
      "with_permissions": 18,
      "top_roles": [
        {
          "id": 1,
          "name": "admin",
          "users_count": 5,
          "permissions_count": 50
        }
      ]
    },
    "permissions": {
      "total": 200,
      "in_use": 180,
      "unused": 20,
      "by_category": {
        "users": 10,
        "roles": 5,
        "tickets": 8
      }
    },
    "activity": {
      "active_tokens": 45,
      "expired_tokens": 10,
      "users_with_sessions": 30,
      "tokens_created_today": 15,
      "tokens_used_last_7_days": 120,
      "active_last_24_hours": 25
    },
    "recent_actions": {
      "recent_events": [
        {
          "id": 1,
          "event_type": "login_success",
          "severity": "info",
          "user": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
          },
          "ip_address": "192.168.1.1",
          "metadata": {},
          "created_at": "2025-11-23T10:30:00.000000Z"
        }
      ],
      "event_stats_7_days": {
        "login_success": 200,
        "login_failed": 15,
        "token_created": 50
      },
      "critical_events_count": 3
    }
  }
}
```

---

## 2. Gestión de Usuarios

### GET `/users` - Listar Usuarios

Lista usuarios con paginación, búsqueda y filtros.

#### Parámetros de Query

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `per_page` | integer | 15 | Número de items por página |
| `page` | integer | 1 | Página actual |
| `search` | string | - | Buscar por nombre, email, DNI o nombre completo |
| `role` | string | - | Filtrar por nombre de rol exacto |
| `has_roles` | boolean | - | `true`: usuarios con roles, `false`: sin roles |
| `has_2fa` | boolean | - | `true`: con 2FA habilitado, `false`: sin 2FA |
| `email_verified` | boolean | - | `true`: email verificado, `false`: no verificado |
| `created_from` | date | - | Fecha inicio (formato: YYYY-MM-DD) |
| `created_to` | date | - | Fecha fin (formato: YYYY-MM-DD) |
| `sort_by` | string | created_at | Campos: `name`, `email`, `created_at`, `id`, `fullname` |
| `sort_order` | string | desc | `asc` o `desc` |
| `with_roles` | boolean | true | Incluir roles en la respuesta |
| `with_permissions` | boolean | false | Incluir permisos directos en la respuesta |

#### Ejemplos de Request

**Básico con paginación:**
```http
GET /api/users?per_page=10&page=1
```

**Buscar usuarios:**
```http
GET /api/users?search=juan
```

**Filtrar por rol:**
```http
GET /api/users?role=admin
```

**Usuarios sin 2FA y sin verificar email:**
```http
GET /api/users?has_2fa=false&email_verified=false
```

**Usuarios creados en un rango de fechas:**
```http
GET /api/users?created_from=2025-01-01&created_to=2025-11-30
```

**Ordenar por nombre ascendente:**
```http
GET /api/users?sort_by=name&sort_order=asc
```

**Combinación de filtros:**
```http
GET /api/users?search=admin&role=admin&has_2fa=true&sort_by=created_at&sort_order=desc&per_page=20
```

#### Response (200 OK)
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Admin User",
        "email": "admin@incadev.com",
        "dni": "12345678",
        "fullname": "Admin User Complete",
        "avatar": null,
        "phone": "999888777",
        "email_verified_at": "2025-11-23T00:00:00.000000Z",
        "two_factor_enabled": true,
        "created_at": "2025-11-23T00:00:00.000000Z",
        "updated_at": "2025-11-23T00:00:00.000000Z",
        "roles": [
          { "id": 1, "name": "admin", "guard_name": "web" }
        ],
        "permissions": []
      }
    ],
    "first_page_url": "http://localhost:8000/api/users?page=1",
    "from": 1,
    "last_page": 10,
    "last_page_url": "http://localhost:8000/api/users?page=10",
    "links": [...],
    "next_page_url": "http://localhost:8000/api/users?page=2",
    "path": "http://localhost:8000/api/users",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 150
  },
  "filters": {
    "search": null,
    "role": null,
    "has_roles": null,
    "has_2fa": null,
    "email_verified": null,
    "created_from": null,
    "created_to": null,
    "sort_by": "created_at",
    "sort_order": "desc"
  }
}
```

---

### GET `/users/{id}` - Obtener Usuario

Obtiene los detalles de un usuario específico.

#### Parámetros de Query

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `with_permissions` | boolean | true | Incluir permisos directos |

#### Request
```http
GET /api/users/1?with_permissions=true
```

#### Response (200 OK)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@incadev.com",
    "dni": "12345678",
    "fullname": "Admin User Complete",
    "avatar": null,
    "phone": "999888777",
    "email_verified_at": "2025-11-23T00:00:00.000000Z",
    "two_factor_enabled": true,
    "created_at": "2025-11-23T00:00:00.000000Z",
    "updated_at": "2025-11-23T00:00:00.000000Z",
    "roles": [
      { "id": 1, "name": "admin", "guard_name": "web" }
    ],
    "permissions": [
      { "id": 1, "name": "users.view", "guard_name": "web" }
    ]
  }
}
```

---

### POST `/users` - Crear Usuario

Crea un nuevo usuario.

#### Request Body

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `name` | string | Sí | Nombre del usuario (max: 255) |
| `email` | string | Sí | Email único |
| `password` | string | Sí | Contraseña (min: 8 caracteres) |
| `dni` | string | No | DNI único (max: 8) |
| `fullname` | string | No | Nombre completo (max: 255) |
| `avatar` | string | No | URL del avatar (max: 500) |
| `phone` | string | No | Teléfono (max: 20) |
| `roles` | array | No | Array de nombres de roles |

#### Request
```http
POST /api/users
Content-Type: application/json

{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "password123",
  "dni": "87654321",
  "fullname": "Juan Carlos Pérez García",
  "phone": "999111222",
  "roles": ["student", "teacher"]
}
```

#### Response (201 Created)
```json
{
  "success": true,
  "message": "Usuario creado exitosamente",
  "data": {
    "id": 2,
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "dni": "87654321",
    "fullname": "Juan Carlos Pérez García",
    "phone": "999111222",
    "roles": [
      { "id": 17, "name": "student" },
      { "id": 18, "name": "teacher" }
    ],
    "permissions": []
  }
}
```

---

### PUT `/users/{id}` - Actualizar Usuario

Actualiza un usuario existente.

#### Request Body

Todos los campos son opcionales. Solo se actualizan los campos enviados.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `name` | string | Nombre del usuario |
| `email` | string | Email único |
| `password` | string | Nueva contraseña |
| `dni` | string | DNI único |
| `fullname` | string | Nombre completo |
| `avatar` | string | URL del avatar |
| `phone` | string | Teléfono |
| `roles` | array | Array de nombres de roles (reemplaza todos los roles) |

#### Request
```http
PUT /api/users/2
Content-Type: application/json

{
  "name": "Juan Pérez Actualizado",
  "phone": "999333444",
  "roles": ["admin"]
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "message": "Usuario actualizado exitosamente",
  "data": { ... }
}
```

---

### DELETE `/users/{id}` - Eliminar Usuario

Elimina un usuario.

#### Request
```http
DELETE /api/users/2
```

#### Response (200 OK)
```json
{
  "success": true,
  "message": "Usuario eliminado exitosamente"
}
```

---

### POST `/users/{id}/roles` - Asignar Roles

Asigna roles a un usuario (reemplaza los roles existentes).

#### Request
```http
POST /api/users/2/roles
Content-Type: application/json

{
  "roles": ["admin", "teacher"]
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "message": "Roles asignados exitosamente",
  "data": { ... }
}
```

---

### POST `/users/{id}/permissions` - Asignar Permisos Directos

Asigna permisos directamente a un usuario (sin pasar por rol).

#### Request
```http
POST /api/users/2/permissions
Content-Type: application/json

{
  "permissions": ["users.view", "users.create"]
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "message": "Permisos asignados exitosamente",
  "data": { ... }
}
```

---

## 3. Gestión de Roles

### GET `/roles` - Listar Roles

Lista roles con paginación, búsqueda y filtros.

#### Parámetros de Query

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `per_page` | integer | 15 | Número de items por página |
| `page` | integer | 1 | Página actual |
| `search` | string | - | Buscar por nombre del rol |
| `has_users` | boolean | - | `true`: roles con usuarios, `false`: sin usuarios |
| `has_permissions` | boolean | - | `true`: roles con permisos, `false`: sin permisos |
| `sort_by` | string | name | Campos: `name`, `created_at`, `id`, `users_count` |
| `sort_order` | string | asc | `asc` o `desc` |
| `with_permissions` | boolean | false | Incluir permisos en la respuesta |
| `with_users_count` | boolean | true | Incluir conteo de usuarios |

#### Ejemplos de Request

**Listar todos los roles con paginación:**
```http
GET /api/roles?per_page=10
```

**Buscar roles:**
```http
GET /api/roles?search=admin
```

**Roles con usuarios asignados:**
```http
GET /api/roles?has_users=true
```

**Roles ordenados por cantidad de usuarios:**
```http
GET /api/roles?sort_by=users_count&sort_order=desc
```

**Roles con sus permisos:**
```http
GET /api/roles?with_permissions=true
```

#### Response (200 OK)
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "admin",
        "guard_name": "web",
        "created_at": "2025-11-23T00:00:00.000000Z",
        "updated_at": "2025-11-23T00:00:00.000000Z",
        "users_count": 5,
        "permissions": [
          { "id": 1, "name": "users.view" },
          { "id": 2, "name": "users.create" }
        ]
      }
    ],
    "total": 18
  },
  "filters": {
    "search": null,
    "has_users": null,
    "has_permissions": null,
    "sort_by": "name",
    "sort_order": "asc"
  }
}
```

---

### GET `/roles/{id}` - Obtener Rol

Obtiene los detalles de un rol específico.

#### Parámetros de Query

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `with_users` | boolean | false | Incluir lista de usuarios (max 50) |

#### Request
```http
GET /api/roles/1?with_users=true
```

#### Response (200 OK)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "admin",
    "guard_name": "web",
    "users_count": 5,
    "permissions": [
      { "id": 1, "name": "users.view" },
      { "id": 2, "name": "users.create" }
    ],
    "users": [
      { "id": 1, "name": "Admin User", "email": "admin@incadev.com" }
    ]
  }
}
```

---

### POST `/roles` - Crear Rol

Crea un nuevo rol.

#### Request Body

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `name` | string | Sí | Nombre único del rol |
| `permissions` | array | No | Array de nombres de permisos |

#### Request
```http
POST /api/roles
Content-Type: application/json

{
  "name": "moderator",
  "permissions": ["users.view", "tickets.view", "tickets.update"]
}
```

#### Response (201 Created)
```json
{
  "success": true,
  "message": "Rol creado exitosamente",
  "data": {
    "id": 19,
    "name": "moderator",
    "permissions": [...]
  }
}
```

---

### PUT `/roles/{id}` - Actualizar Rol

Actualiza un rol existente.

#### Request
```http
PUT /api/roles/19
Content-Type: application/json

{
  "name": "super_moderator",
  "permissions": ["users.view", "users.update", "tickets.view", "tickets.update"]
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "message": "Rol actualizado exitosamente",
  "data": { ... }
}
```

---

### DELETE `/roles/{id}` - Eliminar Rol

Elimina un rol. **No se puede eliminar si tiene usuarios asignados.**

#### Request
```http
DELETE /api/roles/19
```

#### Response (200 OK)
```json
{
  "success": true,
  "message": "Rol eliminado exitosamente"
}
```

#### Response (409 Conflict) - Si tiene usuarios
```json
{
  "success": false,
  "message": "No se puede eliminar el rol 'moderator' porque tiene 5 usuario(s) asignado(s)"
}
```

---

### POST `/roles/{id}/permissions` - Asignar Permisos a Rol

Asigna permisos a un rol (reemplaza los permisos existentes).

#### Request
```http
POST /api/roles/1/permissions
Content-Type: application/json

{
  "permissions": ["users.view", "users.create", "users.update", "users.delete"]
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "message": "Permisos asignados al rol exitosamente",
  "data": { ... }
}
```

---

## 4. Gestión de Permisos

### GET `/permissions` - Listar Permisos

Lista permisos con paginación, búsqueda y filtros.

#### Parámetros de Query

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `per_page` | integer | 15 | Items por página. Usar `-1` para obtener todos |
| `page` | integer | 1 | Página actual |
| `search` | string | - | Buscar por nombre del permiso |
| `category` | string | - | Filtrar por categoría (prefijo: `users`, `roles`, `tickets`, etc.) |
| `in_use` | boolean | - | `true`: permisos asignados a roles, `false`: no asignados |
| `sort_by` | string | name | Campos: `name`, `created_at`, `id` |
| `sort_order` | string | asc | `asc` o `desc` |
| `grouped` | boolean | false | Agrupar permisos por categoría |

#### Ejemplos de Request

**Listar todos los permisos (sin paginación):**
```http
GET /api/permissions?per_page=-1
```

**Buscar permisos:**
```http
GET /api/permissions?search=view
```

**Filtrar por categoría:**
```http
GET /api/permissions?category=users
```

**Permisos no utilizados:**
```http
GET /api/permissions?in_use=false
```

**Permisos agrupados por categoría (ideal para UI de asignación):**
```http
GET /api/permissions?grouped=true
```

#### Response (200 OK) - Normal
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      { "id": 1, "name": "users.view", "guard_name": "web" },
      { "id": 2, "name": "users.create", "guard_name": "web" },
      { "id": 3, "name": "users.update", "guard_name": "web" }
    ],
    "total": 200
  },
  "available_categories": [
    "appointments", "attendance", "audits", "certificates",
    "chatbot", "courses", "enrollments", "grades",
    "hardware", "permissions", "roles", "security",
    "software", "surveys", "tickets", "users"
  ],
  "filters": {
    "search": null,
    "category": null,
    "in_use": null,
    "sort_by": "name",
    "sort_order": "asc"
  }
}
```

#### Response (200 OK) - Agrupado (`grouped=true`)
```json
{
  "success": true,
  "data": [
    {
      "category": "users",
      "count": 6,
      "permissions": [
        { "id": 1, "name": "users.view", "guard_name": "web" },
        { "id": 2, "name": "users.view-any", "guard_name": "web" },
        { "id": 3, "name": "users.create", "guard_name": "web" },
        { "id": 4, "name": "users.update", "guard_name": "web" },
        { "id": 5, "name": "users.delete", "guard_name": "web" },
        { "id": 6, "name": "users.assign-roles", "guard_name": "web" }
      ]
    },
    {
      "category": "roles",
      "count": 5,
      "permissions": [...]
    }
  ],
  "total": 200,
  "filters": {
    "search": null,
    "category": null,
    "in_use": null,
    "grouped": true
  }
}
```

---

### GET `/permissions/{id}` - Obtener Permiso

Obtiene los detalles de un permiso específico.

#### Parámetros de Query

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `with_roles` | boolean | false | Incluir roles que tienen este permiso |

#### Request
```http
GET /api/permissions/1?with_roles=true
```

#### Response (200 OK)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "users.view",
    "guard_name": "web",
    "roles": [
      { "id": 1, "name": "admin" },
      { "id": 2, "name": "support" }
    ]
  }
}
```

---

### POST `/permissions` - Crear Permiso

Crea un nuevo permiso.

#### Request
```http
POST /api/permissions
Content-Type: application/json

{
  "name": "reports.export"
}
```

#### Response (201 Created)
```json
{
  "success": true,
  "message": "Permiso creado exitosamente",
  "data": {
    "id": 201,
    "name": "reports.export",
    "guard_name": "web"
  }
}
```

---

### PUT `/permissions/{id}` - Actualizar Permiso

Actualiza un permiso existente.

#### Request
```http
PUT /api/permissions/201
Content-Type: application/json

{
  "name": "reports.export-pdf"
}
```

#### Response (200 OK)
```json
{
  "success": true,
  "message": "Permiso actualizado exitosamente",
  "data": { ... }
}
```

---

### DELETE `/permissions/{id}` - Eliminar Permiso

Elimina un permiso. **No se puede eliminar si está asignado a roles.**

#### Request
```http
DELETE /api/permissions/201
```

#### Response (200 OK)
```json
{
  "success": true,
  "message": "Permiso eliminado exitosamente"
}
```

#### Response (409 Conflict) - Si está en uso
```json
{
  "success": false,
  "message": "No se puede eliminar el permiso 'users.view' porque está asignado a 5 rol(es)"
}
```

---

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Operación exitosa |
| 201 | Created - Recurso creado exitosamente |
| 401 | Unauthorized - Token no válido o no proporcionado |
| 403 | Forbidden - Sin permisos para realizar la acción |
| 404 | Not Found - Recurso no encontrado |
| 409 | Conflict - Conflicto (ej: eliminar rol con usuarios) |
| 422 | Unprocessable Entity - Error de validación |
| 500 | Internal Server Error - Error del servidor |

---

## Estructura de Errores

### Error de Validación (422)
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "email": ["El campo email ya ha sido tomado."],
    "password": ["El campo password debe tener al menos 8 caracteres."]
  }
}
```

### Error de Autorización (403)
```json
{
  "message": "This action is unauthorized."
}
```

### Recurso No Encontrado (404)
```json
{
  "success": false,
  "message": "Usuario no encontrado"
}
```

---

## Ejemplos de Implementación en JavaScript

### Fetch API

```javascript
// Configuración base
const API_URL = 'http://localhost:8000/api';
const token = localStorage.getItem('token');

const headers = {
  'Content-Type': 'application/json',
  'Authorization': `Bearer ${token}`
};

// Obtener dashboard
async function getDashboard() {
  const response = await fetch(`${API_URL}/admin/dashboard`, { headers });
  return response.json();
}

// Listar usuarios con filtros
async function getUsers(params = {}) {
  const queryString = new URLSearchParams(params).toString();
  const response = await fetch(`${API_URL}/users?${queryString}`, { headers });
  return response.json();
}

// Crear usuario
async function createUser(userData) {
  const response = await fetch(`${API_URL}/users`, {
    method: 'POST',
    headers,
    body: JSON.stringify(userData)
  });
  return response.json();
}

// Ejemplos de uso
getUsers({ search: 'admin', role: 'admin', per_page: 10 });
getUsers({ has_2fa: false, email_verified: false });
getUsers({ created_from: '2025-01-01', created_to: '2025-12-31' });
```

### Axios

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json'
  }
});

// Interceptor para agregar token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Funciones
export const adminAPI = {
  // Dashboard
  getDashboard: () => api.get('/admin/dashboard'),

  // Users
  getUsers: (params) => api.get('/users', { params }),
  getUser: (id) => api.get(`/users/${id}`),
  createUser: (data) => api.post('/users', data),
  updateUser: (id, data) => api.put(`/users/${id}`, data),
  deleteUser: (id) => api.delete(`/users/${id}`),
  assignRoles: (id, roles) => api.post(`/users/${id}/roles`, { roles }),

  // Roles
  getRoles: (params) => api.get('/roles', { params }),
  getRole: (id, params) => api.get(`/roles/${id}`, { params }),
  createRole: (data) => api.post('/roles', data),
  updateRole: (id, data) => api.put(`/roles/${id}`, data),
  deleteRole: (id) => api.delete(`/roles/${id}`),
  assignPermissionsToRole: (id, permissions) =>
    api.post(`/roles/${id}/permissions`, { permissions }),

  // Permissions
  getPermissions: (params) => api.get('/permissions', { params }),
  getPermission: (id, params) => api.get(`/permissions/${id}`, { params }),
  createPermission: (data) => api.post('/permissions', data),
  updatePermission: (id, data) => api.put(`/permissions/${id}`, data),
  deletePermission: (id) => api.delete(`/permissions/${id}`)
};
```

---

## Notas Importantes

1. **Autenticación**: Todos los endpoints requieren un token válido de Sanctum.

2. **Permisos Requeridos**:
   - Dashboard: `users.view`
   - Usuarios: `users.view`, `users.create`, `users.update`, `users.delete`, `users.assign-roles`
   - Roles: `roles.view`, `roles.create`, `roles.update`, `roles.delete`
   - Permisos: `permissions.view`, `permissions.create`, `permissions.update`, `permissions.delete`

3. **Paginación**: La respuesta paginada incluye metadata de Laravel (total, per_page, current_page, etc.)

4. **Filtros Booleanos**: Los valores `true`, `false`, `1`, `0`, `"true"`, `"false"` son aceptados.

5. **Sincronización de Roles/Permisos**: Los métodos `assignRoles` y `assignPermissions` **reemplazan** todos los roles/permisos existentes con los nuevos.
