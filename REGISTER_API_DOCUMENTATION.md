# Documentación del Endpoint de Registro

## POST /api/auth/register

Este endpoint permite registrar un nuevo usuario y su información de empleado de manera simultánea, respetando las claves foráneas de la base de datos.

### URL
```
POST http://127.0.0.1:8000/api/auth/register
```

### Headers
```
Content-Type: application/json
```

### Body (JSON)

```json
{
  "first_name": "Juan",
  "last_name": "Pérez",
  "email": "juan.perez@incadev.com",
  "password": "password123",
  "phone_number": "+51987654321",
  "role": "admin",
  "reason": "Nuevo empleado del área de tecnología",
  "position_id": 1,
  "department_id": 2,
  "hire_date": "2025-10-18",
  "employment_status": "Active",
  "schedule": "Lunes a Viernes, 9:00 AM - 6:00 PM",
  "speciality": "Backend Development",
  "salary": 5000.00
}
```

### Parámetros

#### Datos del Usuario (Obligatorios):

| Campo | Tipo | Descripción | Validación |
|-------|------|-------------|------------|
| `first_name` | string | Nombre del usuario | Requerido, máx. 50 caracteres |
| `last_name` | string | Apellido del usuario | Requerido, máx. 50 caracteres |
| `email` | string | Email único del usuario | Requerido, formato email válido, único en BD |
| `password` | string | Contraseña | Requerido, mínimo 6 caracteres |
| `role` | string | Rol del usuario | Requerido, valores: admin, lms, seg, infra, web, data |
| `reason` | string | Razón del registro | Requerido, máx. 500 caracteres |
| `position_id` | integer | ID del cargo | Requerido, debe existir en tabla `positions` |
| `department_id` | integer | ID del departamento | Requerido, debe existir en tabla `departments` |

#### Datos del Usuario (Opcionales):

| Campo | Tipo | Descripción | Validación | Default |
|-------|------|-------------|------------|---------|
| `phone_number` | string | Teléfono del usuario | Máx. 20 caracteres | null |

#### Datos del Empleado (Opcionales):

| Campo | Tipo | Descripción | Validación | Default |
|-------|------|-------------|------------|---------|
| `hire_date` | date | Fecha de contratación | Formato: YYYY-MM-DD | Fecha actual |
| `employment_status` | string | Estado laboral | Active, Inactive, Terminated | Active |
| `schedule` | string | Horario de trabajo | Texto libre | null |
| `speciality` | string | Especialidad | Máx. 255 caracteres | null |
| `salary` | decimal | Salario | Número positivo (10,2) | null |

### Respuesta Exitosa (201)

```json
{
  "success": true,
  "message": "Solicitud de registro enviada. Será revisada por un administrador.",
  "data": {
    "request_id": 15,
    "employee_id": 8
  }
}
```

**Nota:** El usuario se crea con `status = 'inactive'` y debe ser activado por un administrador.

### Respuestas de Error

#### Error de Validación (422)
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Error de validación en los datos enviados",
    "details": {
      "email": ["El email ya está en uso"],
      "position_id": ["El position_id seleccionado no existe"],
      "department_id": ["El department_id seleccionado no existe"]
    }
  }
}
```

#### Error de Registro (500)
```json
{
  "success": false,
  "error": {
    "code": "REGISTRATION_ERROR",
    "message": "Error al crear el registro: [detalle del error]"
  }
}
```

### Flujo de Trabajo

```
1. Cliente envía POST con datos de usuario + empleado
   ↓
2. Validar todos los campos (usuario + empleado)
   ↓
3. Validar que position_id exista en tabla positions
   ↓
4. Validar que department_id exista en tabla departments
   ↓
5. Iniciar transacción de base de datos
   ↓
6. Crear registro en tabla 'users'
   ↓
7. Crear registro en tabla 'employees' (con user_id del paso 6)
   ↓
8. Confirmar transacción (commit)
   ↓
9. Retornar: {request_id, employee_id}
```

**Si algo falla:** Toda la operación se revierte (rollback) y no se crea ningún registro.

### Ejemplo con cURL

```bash
curl -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "María",
    "last_name": "González",
    "email": "maria.gonzalez@incadev.com",
    "password": "securePass123",
    "phone_number": "+51987654321",
    "role": "lms",
    "reason": "Nueva instructora del área LMS",
    "position_id": 3,
    "department_id": 1,
    "hire_date": "2025-10-20",
    "employment_status": "Active",
    "schedule": "Lunes a Viernes 8:00-17:00",
    "speciality": "Educación Virtual",
    "salary": 3500.00
  }'
```

### Claves Foráneas y Relaciones

#### Tabla `employees`:
```sql
FOREIGN KEY (user_id) REFERENCES users(id)
FOREIGN KEY (position_id) REFERENCES positions(id)
FOREIGN KEY (department_id) REFERENCES departments(id)
```

#### Tabla `positions`:
```sql
FOREIGN KEY (department_id) REFERENCES departments(id)
```

### Notas Importantes

1. **Transacciones:** El endpoint usa transacciones de base de datos. Si falla la creación del usuario o del empleado, se revierten AMBOS cambios automáticamente.

2. **Validación de Claves Foráneas:** Laravel valida automáticamente que:
   - `position_id` exista en la tabla `positions`
   - `department_id` exista en la tabla `departments`

3. **Estado Inicial:**
   - Usuario: Se crea con `status = 'inactive'`
   - Empleado: Se crea con `employment_status = 'Active'` (por defecto)

4. **Consistencia de Datos:** Asegúrate de que:
   - La `position` que selecciones tenga un `department_id` válido
   - El `department_id` que envíes en el empleado exista en la base de datos
   - (Idealmente deberían coincidir, aunque no es obligatorio por el esquema actual)

### Antes de Usar el Endpoint

Asegúrate de tener en la base de datos:

1. ✅ Al menos un **department** creado
   ```sql
   INSERT INTO departments (department_name, description)
   VALUES ('Tecnología', 'Departamento de TI');
   ```

2. ✅ Al menos una **position** creada (vinculada al department)
   ```sql
   INSERT INTO positions (position_name, department_id)
   VALUES ('Desarrollador', 1);
   ```

### Ejemplo Completo de Prueba

```bash
# 1. Crear un departamento (si no existe)
curl -X POST http://127.0.0.1:8000/api/admin/departments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{"department_name":"Tecnología","description":"Área de TI"}'

# 2. Crear una posición (si no existe)
curl -X POST http://127.0.0.1:8000/api/admin/positions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{"position_name":"Desarrollador Senior","department_id":1}'

# 3. Registrar usuario con empleado
curl -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Carlos",
    "last_name": "Mendoza",
    "email": "carlos.mendoza@incadev.com",
    "password": "password123",
    "phone_number": "+51987654321",
    "role": "admin",
    "reason": "Nuevo administrador del sistema",
    "position_id": 1,
    "department_id": 1,
    "hire_date": "2025-10-18",
    "employment_status": "Active",
    "schedule": "Lunes a Viernes, 9:00 AM - 6:00 PM",
    "speciality": "Administración de Sistemas",
    "salary": 4500.00
  }'
```

### Relaciones de Base de Datos

```
departments (1) ────┬─── (N) positions
                    │
                    └─── (N) employees

positions (1) ────── (N) employees

users (1) ────────── (1) employees
```

### Código Relacionado

- **Controlador:** `app/Domains/AuthenticationSessions/Controllers/AuthController.php:177-259`
- **Modelos:**
  - `app/Domains/AuthenticationSessions/Models/User.php`
  - `app/Domains/Administrator/Models/Employee.php`
  - `app/Domains/Administrator/Models/Position.php`
  - `app/Domains/Administrator/Models/Department.php`

### Seguridad

- Las contraseñas se hashean automáticamente con `Hash::make()`
- El email debe ser único en la base de datos
- Los usuarios nuevos deben ser aprobados por un administrador antes de poder iniciar sesión
