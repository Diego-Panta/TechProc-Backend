# Cambios en el Registro de Estudiantes

## Resumen de Cambios

Se ha modificado el endpoint de registro de estudiantes para que cree automáticamente el usuario asociado. Ahora ya no es necesario crear el usuario previamente.

### Cambios Principales:

1. **Eliminado**: Campo `user_id` del request
2. **Agregado**: Campo `password` al request
3. **Automatizado**: Creación del usuario con los datos del estudiante

---

## Endpoint Actualizado

### POST `/api/lms/students`

**Antes** (versión antigua):
```json
{
    "user_id": 11,
    "company_id": 2,
    "document_number": "15241524",
    "first_name": "Hugo",
    "last_name": "Caselli",
    "email": "hcaselli@uns.edu.pe",
    "phone": "+51 949000949",
    "status": "active"
}
```

**Ahora** (versión nueva):
```json
{
    "password": "miPassword123",
    "company_id": 2,
    "document_number": "15241524",
    "first_name": "Hugo",
    "last_name": "Caselli",
    "email": "hcaselli@uns.edu.pe",
    "phone": "+51 949000949",
    "status": "active"
}
```

---

## Validaciones del Request

| Campo | Tipo | Requerido | Validaciones | Descripción |
|-------|------|-----------|--------------|-------------|
| `password` | string | ✅ Sí | min:6, max:255 | Contraseña para login del estudiante |
| `company_id` | integer | ❌ No | exists:companies,id | ID de la empresa asociada |
| `document_number` | string | ✅ Sí | max:20, unique:students | Número de documento (DNI, CE, etc.) |
| `first_name` | string | ✅ Sí | max:255 | Nombre del estudiante |
| `last_name` | string | ✅ Sí | max:255 | Apellido del estudiante |
| `email` | string | ✅ Sí | email, max:255, unique:students,email, unique:users,email | Correo electrónico (debe ser único en ambas tablas) |
| `phone` | string | ❌ No | max:20 | Número de teléfono |
| `status` | string | ✅ Sí | in:active,inactive | Estado del estudiante |

---

## Proceso de Creación

Cuando se envía una petición POST a `/api/lms/students`, el sistema realiza lo siguiente en una transacción de base de datos:

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
    'Hugo',
    'Caselli',
    'Hugo Caselli',
    'hcaselli@uns.edu.pe',
    '[hash bcrypt del password]',
    '+51 949000949',
    '15241524',
    '15241524',
    '["student"]',
    'active',
    'America/Lima'
);
```

### 2. Crea el Estudiante (tabla `students`)
```sql
INSERT INTO students (
    user_id,
    company_id,
    document_number,
    first_name,
    last_name,
    email,
    phone,
    status,
    student_id
) VALUES (
    [ID del usuario recién creado],
    2,
    '15241524',
    'Hugo',
    'Caselli',
    'hcaselli@uns.edu.pe',
    '+51 949000949',
    'active',
    [auto-generado igual al ID del estudiante]
);
```

---

## Respuesta del Endpoint

### Respuesta Exitosa (201 Created)

```json
{
    "success": true,
    "message": "Estudiante y usuario creados exitosamente",
    "data": {
        "id": 123,
        "student_id": 123,
        "user_id": 456,
        "email": "hcaselli@uns.edu.pe",
        "status": "active"
    }
}
```

**Descripción de campos:**
- `id`: ID del registro en la tabla `students`
- `student_id`: Identificador del estudiante (igual al `id`)
- `user_id`: ID del usuario creado en la tabla `users`
- `email`: Email del estudiante
- `status`: Estado actual del estudiante

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

### Ejemplo 1: Crear Estudiante Exitosamente

```bash
curl -X POST http://127.0.0.1:8000/api/lms/students \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "password": "securePass123",
    "company_id": 2,
    "document_number": "15241524",
    "first_name": "Hugo",
    "last_name": "Caselli",
    "email": "hcaselli@uns.edu.pe",
    "phone": "+51 949000949",
    "status": "active"
  }'
```

### Ejemplo 2: Crear Estudiante sin Empresa (company_id opcional)

```bash
curl -X POST http://127.0.0.1:8000/api/lms/students \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "password": "myPassword456",
    "document_number": "87654321",
    "first_name": "María",
    "last_name": "González",
    "email": "mgonzalez@example.com",
    "phone": "+51 987654321",
    "status": "active"
  }'
```

### Ejemplo 3: Login con las Credenciales del Estudiante Creado

Después de crear el estudiante, puede hacer login usando el endpoint de autenticación:

```bash
curl -X POST http://127.0.0.1:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "hcaselli@uns.edu.pe",
    "password": "securePass123"
  }'
```

**Respuesta esperada:**
```json
{
    "success": true,
    "user": {
        "id": 456,
        "first_name": "Hugo",
        "last_name": "Caselli",
        "email": "hcaselli@uns.edu.pe",
        "role": ["student"],
        "status": "active"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refreshToken": "def50200..."
}
```

---

## Archivos Modificados

### 1. `app/Domains/Lms/Http/Requests/CreateStudentRequest.php`

**Cambios:**
- ❌ Eliminada validación de `user_id`
- ✅ Agregada validación de `password` (required, min:6, max:255)
- ✅ Agregada validación `unique:users,email` al campo `email`
- ✅ Actualizados mensajes de validación

### 2. `app/Domains/Lms/Services/StudentService.php`

**Cambios:**
- ✅ Agregadas importaciones: `User`, `Hash`, `DB`
- ✅ Modificado método `createStudent()`:
  - Envuelto en transacción de base de datos (`DB::transaction`)
  - Crea primero el usuario con `User::create()`
  - Hashea el password con `Hash::make()`
  - Asigna rol `['student']` automáticamente
  - Crea el estudiante con el `user_id` generado
  - Retorna el estudiante con relaciones `company` y `user`

### 3. `app/Domains/Lms/Http/Controllers/StudentController.php`

**Cambios:**
- ✅ Actualizada documentación del método `store()`
- ✅ Modificado mensaje de respuesta: "Estudiante y usuario creados exitosamente"
- ✅ Agregado campo `user_id` en la respuesta
- ✅ Agregado campo `status` en la respuesta

---

## Beneficios de la Nueva Implementación

1. **Simplicidad**: Un solo endpoint para crear estudiante y usuario
2. **Atomicidad**: Uso de transacciones garantiza que ambos registros se crean o ninguno
3. **Seguridad**: La contraseña se hashea automáticamente con Bcrypt
4. **Consistencia**: El rol "student" se asigna automáticamente
5. **Validación Mejorada**: El email debe ser único en ambas tablas (users y students)
6. **Login Inmediato**: El estudiante puede hacer login inmediatamente después de ser creado

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

### Error 2: Documento Duplicado
```json
{
    "errors": {
        "document_number": ["Este número de documento ya está registrado"]
    }
}
```
**Solución**: Verificar que el número de documento no esté ya registrado

### Error 3: Contraseña Muy Corta
```json
{
    "errors": {
        "password": ["La contraseña debe tener al menos 6 caracteres"]
    }
}
```
**Solución**: Usar una contraseña de mínimo 6 caracteres

### Error 4: Company No Existe
```json
{
    "errors": {
        "company_id": ["La empresa especificada no existe"]
    }
}
```
**Solución**: Verificar que el `company_id` existe en la tabla `companies`, o no enviar este campo

---

## Notas Técnicas

### Transacciones de Base de Datos
Se utiliza `DB::transaction()` para garantizar que:
- Si falla la creación del usuario, no se crea el estudiante
- Si falla la creación del estudiante, se hace rollback del usuario
- Ambos registros se crean atómicamente o ninguno

### Seguridad de Contraseñas
- Las contraseñas se hashean usando `Hash::make()` con Bcrypt
- La configuración usa 12 rondas de hash (ver `BCRYPT_ROUNDS=12` en `.env`)
- El hash nunca se retorna en las respuestas de la API

### Rol de Usuario
- El usuario se crea automáticamente con rol `["student"]`
- El campo `role` es un array JSON en la base de datos
- Esto permite que en el futuro un usuario tenga múltiples roles

### Zona Horaria
- Por defecto se usa `America/Lima` como timezone
- Esto se puede modificar en futuras versiones si es necesario

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

3. **Crear un estudiante**:
   ```bash
   curl -X POST http://127.0.0.1:8000/api/lms/students \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -d '{
       "password": "test123",
       "company_id": 1,
       "document_number": "12345678",
       "first_name": "Test",
       "last_name": "Student",
       "email": "test@example.com",
       "phone": "+51 999999999",
       "status": "active"
     }'
   ```

4. **Verificar que el usuario puede hacer login**:
   ```bash
   curl -X POST http://127.0.0.1:8000/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email": "test@example.com", "password": "test123"}'
   ```

5. **Verificar en la base de datos** (opcional):
   ```sql
   -- Verificar que el usuario fue creado
   SELECT * FROM users WHERE email = 'test@example.com';

   -- Verificar que el estudiante fue creado
   SELECT * FROM students WHERE email = 'test@example.com';

   -- Verificar la relación entre ambos
   SELECT s.*, u.email, u.role
   FROM students s
   JOIN users u ON s.user_id = u.id
   WHERE s.email = 'test@example.com';
   ```

---

## Migración de Datos Existentes

Si tienes estudiantes creados con el sistema antiguo, no necesitas hacer nada. Los cambios son compatibles hacia atrás y solo afectan la creación de nuevos estudiantes.

---

## Soporte

Si encuentras algún problema o tienes dudas sobre la nueva funcionalidad, por favor:

1. Revisa la documentación completa en `/dat/DOCUMENTACION_BACKEND_API.md`
2. Verifica los logs de Laravel: `php artisan pail`
3. Revisa los mensajes de validación en la respuesta de error

---

**Fecha de actualización**: 2025-10-19
**Versión**: 1.0
**Autor**: Claude Code
