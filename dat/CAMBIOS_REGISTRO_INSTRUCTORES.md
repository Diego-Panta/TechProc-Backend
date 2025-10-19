# Cambios en el Registro de Instructores

## Resumen de Cambios

Se ha modificado el endpoint de registro de instructores para que cree automáticamente el usuario asociado. Ahora ya no es necesario crear el usuario previamente.

### Cambios Principales:

1. **Eliminado**: Campo `user_id` del request
2. **Agregado**: Campos de datos básicos del usuario (`first_name`, `last_name`, `email`, `password`, etc.)
3. **Automatizado**: Creación del usuario con los datos proporcionados

---

## Endpoint Actualizado

### POST `/api/lms/instructors`

**Antes** (versión antigua):
```json
{
  "user_id": 12,
  "bio": "Ingeniero de Software con especialización en desarrollo web",
  "expertise_area": "JavaScript, React, Node.js",
  "status": "active"
}
```

**Ahora** (versión nueva):
```json
{
  "first_name": "Juan",
  "last_name": "Pérez",
  "email": "jperez@example.com",
  "password": "securePass123",
  "phone_number": "+51 999888777",
  "document_number": "12345678",
  "bio": "Ingeniero de Software con especialización en desarrollo web",
  "expertise_area": "JavaScript, React, Node.js",
  "status": "active"
}
```

---

## Validaciones del Request

| Campo | Tipo | Requerido | Validaciones | Descripción |
|-------|------|-----------|--------------|-------------|
| **Datos del Usuario** |
| `first_name` | string | ✅ Sí | max:100 | Nombre del instructor |
| `last_name` | string | ✅ Sí | max:100 | Apellido del instructor |
| `email` | string | ✅ Sí | email, max:255, unique:users,email | Correo electrónico (debe ser único) |
| `password` | string | ✅ Sí | min:6, max:255 | Contraseña para login del instructor |
| `phone_number` | string | ❌ No | max:20 | Número de teléfono |
| `document_number` | string | ❌ No | max:20 | Número de documento (DNI, CE, etc.) |
| **Datos del Instructor** |
| `bio` | string | ❌ No | - | Biografía o descripción del instructor |
| `expertise_area` | string | ✅ Sí | max:500 | Área de expertise o especialización |
| `status` | string | ✅ Sí | in:active,inactive | Estado del instructor |

---

## Proceso de Creación

Cuando se envía una petición POST a `/api/lms/instructors`, el sistema realiza lo siguiente en una transacción de base de datos:

### 1. Crea el Usuario (tabla `users`)
```sql
INSERT INTO users (
    first_name,
    last_name,
    full_name,
    email,
    password,
    phone_number,
    document,
    dni,
    role,
    status,
    timezone
) VALUES (
    'Juan',
    'Pérez',
    'Juan Pérez',
    'jperez@example.com',
    '[hash bcrypt del password]',
    '+51 999888777',
    '12345678',
    '12345678',
    '["instructor"]',
    'active',
    'America/Lima'
);
```

### 2. Crea el Instructor (tabla `instructors`)
```sql
INSERT INTO instructors (
    user_id,
    bio,
    expertise_area,
    status,
    instructor_id
) VALUES (
    [ID del usuario recién creado],
    'Ingeniero de Software con especialización en desarrollo web',
    'JavaScript, React, Node.js',
    'active',
    [auto-generado igual al ID del instructor]
);
```

---

## Respuesta del Endpoint

### Respuesta Exitosa (201 Created)

```json
{
    "success": true,
    "message": "Instructor y usuario creados exitosamente",
    "data": {
        "id": 45,
        "instructor_id": 45,
        "user_id": 89,
        "email": "jperez@example.com",
        "full_name": "Juan Pérez",
        "status": "active"
    }
}
```

**Descripción de campos:**
- `id`: ID del registro en la tabla `instructors`
- `instructor_id`: Identificador del instructor (igual al `id`)
- `user_id`: ID del usuario creado en la tabla `users`
- `email`: Email del instructor
- `full_name`: Nombre completo del instructor
- `status`: Estado actual del instructor

### Respuesta de Error (422 Unprocessable Entity)

```json
{
    "message": "The email has already been taken. (and 1 more error)",
    "errors": {
        "email": [
            "Este correo electrónico ya está registrado"
        ],
        "password": [
            "La contraseña debe tener al menos 6 caracteres"
        ]
    }
}
```

---

## Ejemplos de Uso con cURL

### Ejemplo 1: Crear Instructor Completo

```bash
curl -X POST http://127.0.0.1:8000/api/lms/instructors \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "jperez@example.com",
    "password": "securePass123",
    "phone_number": "+51 999888777",
    "document_number": "12345678",
    "bio": "Ingeniero de Software con más de 10 años de experiencia en desarrollo web",
    "expertise_area": "JavaScript, React, Node.js, TypeScript",
    "status": "active"
  }'
```

### Ejemplo 2: Crear Instructor con Datos Mínimos

```bash
curl -X POST http://127.0.0.1:8000/api/lms/instructors \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "first_name": "María",
    "last_name": "González",
    "email": "mgonzalez@example.com",
    "password": "myPassword456",
    "expertise_area": "Python, Django, Machine Learning",
    "status": "active"
  }'
```

### Ejemplo 3: Login con las Credenciales del Instructor Creado

Después de crear el instructor, puede hacer login usando el endpoint de autenticación:

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "jperez@example.com",
    "password": "securePass123"
  }'
```

**Respuesta esperada:**
```json
{
    "success": true,
    "user": {
        "id": 89,
        "first_name": "Juan",
        "last_name": "Pérez",
        "email": "jperez@example.com",
        "role": ["instructor"],
        "status": "active"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refreshToken": "def50200..."
}
```

---

## Archivos Modificados

### 1. `app/Domains/Lms/Http/Requests/CreateInstructorRequest.php`

**Cambios:**
- ❌ Eliminada validación de `user_id`
- ✅ Agregadas validaciones para datos de usuario:
  - `first_name` (required, max:100)
  - `last_name` (required, max:100)
  - `email` (required, email, unique:users,email)
  - `password` (required, min:6, max:255)
  - `phone_number` (nullable, max:20)
  - `document_number` (nullable, max:20)
- ✅ Actualizados mensajes de validación

### 2. `app/Domains/Lms/Services/InstructorService.php`

**Cambios:**
- ✅ Agregadas importaciones: `User`, `Hash`, `DB`
- ✅ Modificado método `createInstructor()`:
  - Envuelto en transacción de base de datos (`DB::transaction`)
  - Crea primero el usuario con `User::create()`
  - Hashea el password con `Hash::make()`
  - Asigna rol `['instructor']` automáticamente
  - Crea el instructor con el `user_id` generado
  - Retorna el instructor con relación `user`

### 3. `app/Domains/Lms/Http/Controllers/InstructorController.php`

**Cambios:**
- ✅ Actualizada documentación del método `store()`
- ✅ Modificado mensaje de respuesta: "Instructor y usuario creados exitosamente"
- ✅ Agregados campos en la respuesta:
  - `user_id`
  - `email`
  - `full_name`
  - `status`

---

## Beneficios de la Nueva Implementación

1. **Simplicidad**: Un solo endpoint para crear instructor y usuario
2. **Atomicidad**: Uso de transacciones garantiza que ambos registros se crean o ninguno
3. **Seguridad**: La contraseña se hashea automáticamente con Bcrypt
4. **Consistencia**: El rol "instructor" se asigna automáticamente
5. **Validación Mejorada**: El email debe ser único en la tabla users
6. **Login Inmediato**: El instructor puede hacer login inmediatamente después de ser creado
7. **Datos Completos**: Se requieren datos básicos del usuario para crear un perfil completo

---

## Casos de Error Comunes

### Error 1: Email Duplicado
```json
{
    "errors": {
        "email": ["Este correo electrónico ya está registrado"]
    }
}
```
**Solución**: Usar un email diferente que no esté en uso

### Error 2: Contraseña Muy Corta
```json
{
    "errors": {
        "password": ["La contraseña debe tener al menos 6 caracteres"]
    }
}
```
**Solución**: Usar una contraseña de mínimo 6 caracteres

### Error 3: Campos Requeridos Faltantes
```json
{
    "errors": {
        "first_name": ["El nombre es obligatorio"],
        "last_name": ["El apellido es obligatorio"],
        "expertise_area": ["El área de expertise es obligatoria"]
    }
}
```
**Solución**: Asegurarse de enviar todos los campos requeridos

---

## Notas Técnicas

### Transacciones de Base de Datos
Se utiliza `DB::transaction()` para garantizar que:
- Si falla la creación del usuario, no se crea el instructor
- Si falla la creación del instructor, se hace rollback del usuario
- Ambos registros se crean atómicamente o ninguno

### Seguridad de Contraseñas
- Las contraseñas se hashean usando `Hash::make()` con Bcrypt
- La configuración usa 12 rondas de hash (ver `BCRYPT_ROUNDS=12` en `.env`)
- El hash nunca se retorna en las respuestas de la API

### Rol de Usuario
- El usuario se crea automáticamente con rol `["instructor"]`
- El campo `role` es un array JSON en la base de datos
- Esto permite que en el futuro un usuario tenga múltiples roles

### Zona Horaria
- Por defecto se usa `America/Lima` como timezone
- Esto se puede modificar en futuras versiones si es necesario

### Campos Opcionales
- `bio`: Puede ser null
- `phone_number`: Puede ser null
- `document_number`: Puede ser null

### Campos Auto-Generados
- `instructor_id`: Se asigna automáticamente igual al `id` del instructor
- `full_name`: Se genera concatenando `first_name` + `last_name`
- `created_at`: Se asigna automáticamente con el timestamp actual

---

## Testing Manual

Para probar la nueva funcionalidad:

1. **Preparar el ambiente**:
   ```bash
   php artisan serve
   ```

2. **Obtener un token de autenticación** (si el endpoint está protegido):
   ```bash
   # Login como admin u otro usuario autorizado
   curl -X POST http://127.0.0.1:8000/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email": "admin@example.com", "password": "admin123"}'
   ```

3. **Crear un instructor**:
   ```bash
   curl -X POST http://127.0.0.1:8000/api/lms/instructors \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -d '{
       "first_name": "Test",
       "last_name": "Instructor",
       "email": "test.instructor@example.com",
       "password": "test123456",
       "phone_number": "+51 999999999",
       "document_number": "87654321",
       "bio": "Instructor de prueba con experiencia en tecnologías web",
       "expertise_area": "HTML, CSS, JavaScript",
       "status": "active"
     }'
   ```

4. **Verificar que el instructor puede hacer login**:
   ```bash
   curl -X POST http://127.0.0.1:8000/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{
       "email": "test.instructor@example.com",
       "password": "test123456"
     }'
   ```

5. **Verificar en la base de datos** (opcional):
   ```sql
   -- Verificar que el usuario fue creado con rol instructor
   SELECT * FROM users WHERE email = 'test.instructor@example.com';

   -- Verificar que el instructor fue creado
   SELECT * FROM instructors WHERE user_id IN (
       SELECT id FROM users WHERE email = 'test.instructor@example.com'
   );

   -- Verificar la relación entre ambos
   SELECT i.*, u.email, u.role, u.full_name
   FROM instructors i
   JOIN users u ON i.user_id = u.id
   WHERE u.email = 'test.instructor@example.com';
   ```

---

## Comparación: Antes vs Ahora

### Flujo Anterior (2 pasos)

**Paso 1**: Crear usuario
```bash
POST /api/admin/users
{
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "jperez@example.com",
    "password": "pass123",
    "role": ["instructor"]
}
# Respuesta: { "id": 12 }
```

**Paso 2**: Crear instructor
```bash
POST /api/lms/instructors
{
    "user_id": 12,
    "bio": "Experto en desarrollo web",
    "expertise_area": "JavaScript, React",
    "status": "active"
}
```

### Flujo Nuevo (1 paso)

**Paso único**: Crear instructor (crea usuario automáticamente)
```bash
POST /api/lms/instructors
{
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "jperez@example.com",
    "password": "pass123",
    "bio": "Experto en desarrollo web",
    "expertise_area": "JavaScript, React",
    "status": "active"
}
# Respuesta incluye tanto instructor_id como user_id
```

**Ventajas del nuevo flujo:**
- ✅ Menos pasos (1 en vez de 2)
- ✅ Atómico (rollback automático si falla)
- ✅ Menos propenso a errores
- ✅ Más simple de implementar en el frontend

---

## Migración de Datos Existentes

Si tienes instructores creados con el sistema antiguo, no necesitas hacer nada. Los cambios son compatibles hacia atrás y solo afectan la creación de nuevos instructores.

---

## Casos de Uso Comunes

### Caso 1: Instructor Universitario
```json
{
  "first_name": "Dr. Carlos",
  "last_name": "Rodríguez",
  "email": "crodriguez@universidad.edu.pe",
  "password": "university2024",
  "phone_number": "+51 987654321",
  "document_number": "43218765",
  "bio": "Doctor en Ciencias de la Computación, especializado en Inteligencia Artificial y Machine Learning",
  "expertise_area": "Python, TensorFlow, PyTorch, Deep Learning, Computer Vision",
  "status": "active"
}
```

### Caso 2: Instructor Técnico
```json
{
  "first_name": "Ana",
  "last_name": "Martínez",
  "email": "amartinez@techschool.com",
  "password": "tech2024pass",
  "phone_number": "+51 912345678",
  "bio": "Desarrolladora Full Stack con 8 años de experiencia",
  "expertise_area": "JavaScript, TypeScript, React, Node.js, MongoDB",
  "status": "active"
}
```

### Caso 3: Instructor de Tiempo Parcial
```json
{
  "first_name": "Luis",
  "last_name": "Torres",
  "email": "ltorres@freelance.com",
  "password": "freelance2024",
  "expertise_area": "Diseño UX/UI, Figma, Adobe XD",
  "status": "active"
}
```

---

## Soporte

Si encuentras algún problema o tienes dudas sobre la nueva funcionalidad, por favor:

1. Revisa la documentación completa en `/dat/DOCUMENTACION_BACKEND_API.md`
2. Verifica los logs de Laravel: `php artisan pail`
3. Revisa los mensajes de validación en la respuesta de error
4. Consulta la documentación de cambios en estudiantes: `/dat/CAMBIOS_REGISTRO_ESTUDIANTES.md`

---

**Fecha de actualización**: 2025-10-19
**Versión**: 1.0
**Autor**: Claude Code
