# Documentación del Endpoint de Login

## POST /api/auth/login

Este endpoint autentica un usuario y retorna sus datos, incluyendo la información del empleado asociado si existe.

### URL
```
POST http://127.0.0.1:8000/api/auth/login
```

### Headers
```
Content-Type: application/json
```

### Body (JSON)

```json
{
  "email": "usuario@ejemplo.com",
  "password": "contraseña_segura"
}
```

### Parámetros

| Campo | Tipo | Descripción | Validación |
|-------|------|-------------|------------|
| `email` | string | Email del usuario | Requerido, formato email válido |
| `password` | string | Contraseña del usuario | Requerido, mínimo 6 caracteres |

### Respuesta Exitosa (200)

#### Usuario CON empleado asociado:

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "first_name": "Juan",
      "last_name": "Pérez",
      "email": "juan.perez@incadev.com",
      "role": ["admin"],
      "profile_photo": "https://...",
      "status": "active"
    },
    "session": {
      "session_id": 123,
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "expires_at": "2025-10-18T14:30:00.000000Z"
    },
    "employee": {
      "id": 8,
      "employee_id": 12345,
      "hire_date": "2025-01-15",
      "position": {
        "id": 3,
        "position_name": "Desarrollador Senior",
        "department_id": 2
      },
      "department": {
        "id": 2,
        "department_name": "Tecnología"
      },
      "employment_status": "Active",
      "schedule": "Lunes a Viernes, 9:00 AM - 6:00 PM",
      "speciality": "Backend Development",
      "salary": "5000.00"
    }
  }
}
```

#### Usuario SIN empleado asociado:

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 2,
      "first_name": "María",
      "last_name": "González",
      "email": "maria.gonzalez@incadev.com",
      "role": ["student"],
      "profile_photo": null,
      "status": "active"
    },
    "session": {
      "session_id": 124,
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "expires_at": "2025-10-18T14:30:00.000000Z"
    }
  }
}
```

**Nota:** El campo `employee` solo aparece si el usuario tiene un registro de empleado asociado en la base de datos.

### Respuestas de Error

#### Error de Validación (422)
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Error de validación en los datos enviados",
    "details": {
      "email": ["El campo email es obligatorio"],
      "password": ["El campo password debe tener al menos 6 caracteres"]
    }
  }
}
```

#### Credenciales Incorrectas (401)
```json
{
  "success": false,
  "error": {
    "code": "AUTHENTICATION_FAILED",
    "message": "Email o contraseña incorrectos"
  }
}
```

#### Usuario Inactivo (403)
```json
{
  "success": false,
  "error": {
    "code": "USER_INACTIVE",
    "message": "Usuario inactivo. Contacte al administrador."
  }
}
```

### Ejemplo con cURL

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan.perez@incadev.com",
    "password": "password123"
  }'
```

### Flujo de Trabajo

```
1. Cliente envía credenciales (email + password)
   ↓
2. Validar formato de los datos
   ↓
3. Buscar usuario por email
   ↓
4. Verificar contraseña con hash
   ↓
5. Verificar que el usuario esté activo
   ↓
6. Generar token JWT
   ↓
7. Crear sesión activa en base de datos
   ↓
8. Actualizar último acceso del usuario
   ↓
9. Cargar datos del empleado (con position y department)
   ↓
10. Construir respuesta (incluye employee si existe)
   ↓
11. Retornar respuesta JSON
```

### Datos Retornados

#### Objeto `user`:
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | integer | ID único del usuario |
| `first_name` | string | Nombre del usuario |
| `last_name` | string | Apellido del usuario |
| `email` | string | Email del usuario |
| `role` | array | Roles del usuario (admin, lms, seg, etc.) |
| `profile_photo` | string\|null | URL de la foto de perfil |
| `status` | string | Estado del usuario (active, inactive) |

#### Objeto `session`:
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `session_id` | integer | ID de la sesión creada |
| `token` | string | Token JWT para autenticación |
| `expires_at` | string | Fecha/hora de expiración del token (ISO 8601) |

#### Objeto `employee` (opcional):
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | integer | ID del registro de empleado |
| `employee_id` | integer | ID de empleado empresarial |
| `hire_date` | date | Fecha de contratación |
| `position` | object\|null | Información del cargo |
| `department` | object\|null | Información del departamento |
| `employment_status` | string | Estado laboral (Active, Inactive, Terminated) |
| `schedule` | string\|null | Horario de trabajo |
| `speciality` | string\|null | Especialidad del empleado |
| `salary` | decimal\|null | Salario |

### Notas Importantes

1. **Eager Loading:** Los datos del empleado se cargan eficientemente usando `load(['employee.position', 'employee.department'])` para evitar el problema N+1.

2. **Retrocompatibilidad:** Si el usuario NO tiene empleado asociado, el campo `employee` simplemente no aparece en la respuesta, manteniendo compatibilidad con usuarios que no son empleados.

3. **Seguridad:**
   - Las contraseñas se verifican usando `Hash::check()`
   - Los tokens JWT expiran después de 2 horas
   - Se registra la IP y user agent de cada sesión

4. **Sesión Activa:** Cada login crea un nuevo registro en `active_sessions` que puede ser usado para:
   - Rastrear sesiones activas del usuario
   - Implementar logout desde todos los dispositivos
   - Auditoría de accesos

### Relaciones de Base de Datos

```
users (1) ──┬── (1) employees
            │
            └── (N) active_sessions

employees (N) ──── (1) positions
employees (N) ──── (1) departments
```

### Código Relacionado

- **Controlador:** `app/Domains/AuthenticationSessions/Controllers/AuthController.php:24-134`
- **Modelo User:** `app/Domains/AuthenticationSessions/Models/User.php`
- **Modelo Employee:** `app/Domains/Administrator/Models/Employee.php`
- **Ruta:** Definida en las rutas de autenticación

### Testing

Para probar este endpoint necesitas:

1. Un usuario registrado en la base de datos
2. Opcionalmente, un registro de empleado asociado al usuario
3. El usuario debe tener `status = 'active'`

```bash
# Crear un usuario de prueba (si no existe)
# Luego hacer login
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"password123"}'
```
