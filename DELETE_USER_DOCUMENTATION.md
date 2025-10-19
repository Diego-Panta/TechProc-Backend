# Documentación del Endpoint de Eliminación de Usuario

## DELETE /api/admin/users/{user_id}

Este endpoint elimina un usuario y todos sus datos relacionados en cascada, incluyendo el registro de empleado y las sesiones activas.

### URL
```
DELETE http://127.0.0.1:8000/api/admin/users/{user_id}
```

### Parámetros de Ruta

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `user_id` | integer | ID del usuario a eliminar |

### Headers
```
Content-Type: application/json
Authorization: Bearer {token}
```

**Nota:** Este endpoint requiere autenticación y permisos de administrador (middleware `admin`).

### Proceso de Eliminación en Cascada

Cuando se elimina un usuario, el sistema elimina automáticamente en el siguiente orden:

1. **Registro de empleado** (`employees` table)
   - Si el usuario tiene un registro de empleado asociado, se elimina

2. **Sesiones activas** (`active_sessions` table)
   - Todas las sesiones activas del usuario se eliminan

3. **Usuario** (`users` table)
   - Finalmente se elimina el registro del usuario

**Importante:** Todas estas operaciones se ejecutan dentro de una **transacción de base de datos**. Si cualquier paso falla, todos los cambios se revierten automáticamente.

### Respuesta Exitosa (200)

```json
{
  "success": true,
  "message": "Usuario y datos relacionados eliminados exitosamente"
}
```

### Respuestas de Error

#### Usuario No Encontrado (404)
```json
{
  "success": false,
  "error": {
    "code": "USER_NOT_FOUND",
    "message": "Usuario no encontrado"
  }
}
```

#### Error Interno del Servidor (500)
```json
{
  "success": false,
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "Error interno del servidor: [detalle del error]"
  }
}
```

### Ejemplo con cURL

```bash
curl -X DELETE http://127.0.0.1:8000/api/admin/users/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
```

### Flujo de Trabajo

```
1. Cliente envía DELETE con user_id
   ↓
2. Verificar que el usuario existe
   ↓
3. Iniciar transacción de base de datos
   ↓
4. Buscar y eliminar empleado asociado (si existe)
   ↓
5. Eliminar todas las sesiones activas del usuario
   ↓
6. Eliminar el usuario
   ↓
7. Confirmar transacción (commit)
   ↓
8. Retornar respuesta exitosa
```

### Notas Importantes

1. **Transaccional:** Si algo falla durante el proceso, TODOS los cambios se revierten
2. **Cascada automática:** No es necesario eliminar manualmente el empleado o las sesiones
3. **Seguridad:** Solo usuarios con rol de administrador pueden ejecutar este endpoint
4. **No reversible:** Una vez eliminado, no se puede recuperar (a menos que tengas backups)

### Dependencias Eliminadas

| Tabla | Campo FK | Descripción |
|-------|----------|-------------|
| `employees` | `user_id` | Registro de empleado del usuario |
| `active_sessions` | `user_id` | Todas las sesiones activas del usuario |

### Consideraciones Futuras

Si en el futuro se agregan más tablas con relaciones a `users`, deberás actualizar el método `deleteUser()` para incluir la eliminación de esos registros también. Ejemplos:

- Notificaciones del usuario
- Auditorías del usuario
- Comentarios o actividades
- Archivos subidos por el usuario
- etc.

### Código Relacionado

- **Controlador:** `app/Domains/Administrator/Controllers/AdminController.php:291`
- **Ruta:** `app/Domains/Administrator/routes.php:39`
- **Modelos:**
  - `app/Domains/Administrator/Models/User.php`
  - `app/Domains/Administrator/Models/Employee.php`
  - `app/Domains/AuthenticationSessions/Models/ActiveSession.php`
