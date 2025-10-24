# CRUD de Usuarios - Documentación de Endpoints para Postman

## Base URL
```
http://127.0.0.1:8000/api/admin
```

---

## 1. LISTAR USUARIOS (GET)

### Endpoint
```
GET /admin/users
```

### Parámetros Query (Opcionales)
- `role`: Filtrar por rol (admin, instructor, student, lms, seg, infra, web, data)
- `status`: Filtrar por estado (active, inactive, banned)
- `search`: Buscar por nombre, apellido o email
- `page`: Número de página (default: 1)
- `limit`: Registros por página (default: 20)

### Ejemplos de URLs
```
GET http://127.0.0.1:8000/api/admin/users
GET http://127.0.0.1:8000/api/admin/users?role=student
GET http://127.0.0.1:8000/api/admin/users?status=active
GET http://127.0.0.1:8000/api/admin/users?search=Carlos
GET http://127.0.0.1:8000/api/admin/users?page=2&limit=10
```

### Respuesta Exitosa (200)
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "first_name": "Carlos",
            "last_name": "Rodríguez",
            "full_name": "Carlos Rodríguez",
            "dni": "12345678",
            "document": "DOC12345678",
            "email": "carlos.rodriguez@academia.edu",
            "email_verified_at": null,
            "phone_number": "+51987654321",
            "address": "Av. Principal 123, Lima",
            "birth_date": "1985-03-15",
            "role": ["admin"],
            "gender": "male",
            "country": "Peru",
            "country_location": "Lima",
            "timezone": "America/Lima",
            "profile_photo": null,
            "status": "active",
            "synchronized": true,
            "last_access_ip": null,
            "last_access": null,
            "last_connection": null,
            "created_at": "2025-10-24T16:27:38.237970Z",
            "updated_at": "2025-10-24T16:27:38.237970Z"
        }
    ]
}
```

---

## 2. OBTENER UN USUARIO (GET)

### Endpoint
```
GET /admin/users/{user_id}
```

### Ejemplo
```
GET http://127.0.0.1:8000/api/admin/users/1
```

### Respuesta Exitosa (200)
```json
{
    "success": true,
    "data": {
        "id": 1,
        "first_name": "Carlos",
        "last_name": "Rodríguez",
        "email": "carlos.rodriguez@academia.edu",
        "phone_number": "+51987654321",
        "address": "Av. Principal 123, Lima",
        "birth_date": "1985-03-15",
        "gender": "male",
        "country": "Peru",
        "role": ["admin"],
        "status": "active",
        "profile_photo": null,
        "last_access": null,
        "last_access_ip": null,
        "created_at": "2025-10-24T16:27:38.237970Z",
        "updated_at": "2025-10-24T16:27:38.237970Z"
    }
}
```

### Respuesta de Error (404)
```json
{
    "success": false,
    "error": {
        "code": "USER_NOT_FOUND",
        "message": "Usuario no encontrado"
    }
}
```

---

## 3. CREAR USUARIO (POST)

### Endpoint
```
POST /admin/users
```

### Headers
```
Content-Type: application/json
```

### Body (JSON)
```json
{
    "first_name": "María",
    "last_name": "González",
    "full_name": "María González",
    "dni": "98765432",
    "document": "DOC98765432",
    "email": "maria.gonzalez@academia.edu",
    "password": "password123",
    "phone_number": "+51987654342",
    "address": "Av. Los Álamos 456, Lima",
    "birth_date": "1995-08-20",
    "gender": "female",
    "country": "Peru",
    "country_location": "Lima",
    "timezone": "America/Lima",
    "profile_photo": null,
    "role": "instructor",
    "status": "active",
    "synchronized": true
}
```

### Campos Obligatorios
- `first_name` (string, max: 100)
- `last_name` (string, max: 100)
- `email` (email único)
- `password` (string, min: 6)
- `role` (enum: admin, instructor, student, lms, seg, infra, web, data)

### Campos Opcionales
- `full_name` (string, max: 100) - Se auto-genera si no se envía
- `dni` (string, max: 20, único)
- `document` (string, max: 20, único)
- `phone_number` (string, max: 20)
- `address` (string)
- `birth_date` (date: YYYY-MM-DD)
- `gender` (enum: male, female, other)
- `country` (string, max: 100)
- `country_location` (string, max: 100)
- `timezone` (string, max: 50) - Default: "America/Lima"
- `profile_photo` (string, max: 500)
- `status` (enum: active, inactive, banned) - Default: "active"
- `synchronized` (boolean) - Default: true

### Respuesta Exitosa (201)
```json
{
    "success": true,
    "message": "Usuario creado exitosamente",
    "data": {
        "id": 22,
        "email": "maria.gonzalez@academia.edu"
    }
}
```

### Respuesta de Error de Validación (422)
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Error de validación en los datos enviados",
        "details": {
            "email": [
                "El campo email ya ha sido registrado."
            ]
        }
    }
}
```

---

## 4. ACTUALIZAR USUARIO (PUT)

### Endpoint
```
PUT /admin/users/{user_id}
```

### Headers
```
Content-Type: application/json
```

### Body (JSON) - Ejemplo Completo
```json
{
    "first_name": "María Fernanda",
    "last_name": "González Pérez",
    "full_name": "María Fernanda González Pérez",
    "dni": "98765432",
    "document": "DOC98765432",
    "email": "maria.gonzalez@academia.edu",
    "password": "newpassword123",
    "phone_number": "+51987654999",
    "address": "Av. Nueva Dirección 789, Lima",
    "birth_date": "1995-08-20",
    "gender": "female",
    "country": "Peru",
    "country_location": "Lima",
    "timezone": "America/Lima",
    "profile_photo": "https://example.com/photo.jpg",
    "role": "admin",
    "status": "active",
    "synchronized": true
}
```

### Body (JSON) - Ejemplo Parcial
```json
{
    "phone_number": "+51999888777",
    "address": "Nueva dirección actualizada",
    "status": "inactive"
}
```

### Notas Importantes
- **Todos los campos son opcionales** (se pueden actualizar solo los campos necesarios)
- Si se actualiza `first_name` o `last_name`, se regenera automáticamente `full_name` (a menos que se envíe explícitamente)
- El campo `password` se encripta automáticamente si se envía
- El campo `role` se convierte a array internamente
- Los campos `dni`, `document` y `email` deben ser únicos (excepto para el usuario actual)

### Respuesta Exitosa (200)
```json
{
    "success": true,
    "message": "Usuario actualizado exitosamente"
}
```

### Respuesta de Error (404)
```json
{
    "success": false,
    "error": {
        "code": "USER_NOT_FOUND",
        "message": "Usuario no encontrado"
    }
}
```

---

## 5. ELIMINAR USUARIO (DELETE)

### Endpoint
```
DELETE /admin/users/{user_id}
```

### Ejemplo
```
DELETE http://127.0.0.1:8000/api/admin/users/22
```

### Respuesta Exitosa (200)
```json
{
    "success": true,
    "message": "Usuario y datos relacionados eliminados exitosamente"
}
```

### Respuesta de Error (404)
```json
{
    "success": false,
    "error": {
        "code": "USER_NOT_FOUND",
        "message": "Usuario no encontrado"
    }
}
```

### Notas Importantes
- Esta operación es **irreversible**
- Elimina también:
  - Empleado asociado (si existe)
  - Sesiones activas del usuario
- Usa transacciones para garantizar la integridad de los datos

---

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Operación exitosa |
| 201 | Created - Usuario creado exitosamente |
| 404 | Not Found - Usuario no encontrado |
| 422 | Unprocessable Entity - Error de validación |
| 500 | Internal Server Error - Error interno del servidor |

---

## Tipos de Roles Disponibles

- `admin` - Administrador del sistema
- `instructor` - Profesor/Instructor
- `student` - Estudiante
- `lms` - Gestión de LMS
- `seg` - Seguridad
- `infra` - Infraestructura
- `web` - Desarrollo Web
- `data` - Gestión de Datos

---

## Tipos de Estado (Status)

- `active` - Usuario activo
- `inactive` - Usuario inactivo
- `banned` - Usuario bloqueado/baneado

---

## Géneros Disponibles

- `male` - Masculino
- `female` - Femenino
- `other` - Otro
