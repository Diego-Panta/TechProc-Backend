# Testing Secondary Email con Postman

Esta guía te ayudará a testear la funcionalidad completa de email secundario usando Postman.

## Requisitos Previos

1. Tener un usuario registrado y autenticado
2. Tener un token de autenticación válido (Bearer token)
3. Tener acceso a un correo electrónico secundario para recibir el código de verificación

---

## 1. Autenticación (Obtener Token)

Primero necesitas autenticarte para obtener un token:

### Endpoint: Login
```
POST http://localhost:8000/api/auth/login
```

### Headers:
```
Content-Type: application/json
Accept: application/json
```

### Body (raw JSON):
```json
{
  "email": "usuario@ejemplo.com",
  "password": "tu_password",
  "role": "admin"
}
```

### Respuesta Exitosa:
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 1,
      "name": "Usuario Test",
      "email": "usuario@ejemplo.com",
      "secondary_email": null,
      "secondary_email_verified": false,
      ...
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**⚠️ IMPORTANTE:** Guarda el `token` de la respuesta. Lo necesitarás para los siguientes pasos.

---

## 2. Agregar Email Secundario

### Endpoint: Add Secondary Email
```
POST http://localhost:8000/api/auth/secondary-email/add
```

### Headers:
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer TU_TOKEN_AQUI
```

### Body (raw JSON):
```json
{
  "secondary_email": "email_secundario@ejemplo.com"
}
```

### Validaciones:
- El email secundario NO puede ser igual al email principal
- El email secundario NO puede estar registrado como email principal de otro usuario
- El email secundario NO puede estar en uso como email secundario de otro usuario

### Respuesta Exitosa:
```json
{
  "success": true,
  "message": "Email secundario agregado. Se ha enviado un código de verificación.",
  "data": {
    "secondary_email": "email_secundario@ejemplo.com",
    "verified": false
  }
}
```

### Respuestas de Error:

**Email ya registrado como principal:**
```json
{
  "success": false,
  "message": "Este email ya está registrado como email principal en el sistema"
}
```

**Email ya usado como secundario:**
```json
{
  "success": false,
  "message": "Este email ya está siendo usado como email secundario por otro usuario"
}
```

**Email igual al principal:**
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "secondary_email": [
      "The secondary email and email must be different."
    ]
  }
}
```

**⚠️ IMPORTANTE:** Después de esta petición, revisa el correo `email_secundario@ejemplo.com` para obtener el código de verificación de 6 dígitos.

---

## 3. Verificar Email Secundario

Después de recibir el código por correo, verifica el email secundario:

### Endpoint: Verify Secondary Email
```
POST http://localhost:8000/api/auth/secondary-email/verify
```

### Headers:
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer TU_TOKEN_AQUI
```

### Body (raw JSON):
```json
{
  "code": "123456"
}
```

### Respuesta Exitosa:
```json
{
  "success": true,
  "message": "Email secundario verificado exitosamente",
  "data": {
    "secondary_email": "email_secundario@ejemplo.com",
    "verified": true,
    "verified_at": "2025-11-23T15:30:00.000000Z"
  }
}
```

### Respuestas de Error:

**No hay email secundario:**
```json
{
  "success": false,
  "message": "No has agregado un email secundario"
}
```

**Email ya verificado:**
```json
{
  "success": false,
  "message": "El email secundario ya está verificado"
}
```

**Código expirado (después de 15 minutos):**
```json
{
  "success": false,
  "message": "El código de verificación ha expirado. Solicita uno nuevo."
}
```

**Código incorrecto:**
```json
{
  "success": false,
  "message": "Código de verificación incorrecto"
}
```

---

## 4. Reenviar Código de Verificación

Si el código expiró o no llegó, puedes solicitar uno nuevo:

### Endpoint: Resend Verification Code
```
POST http://localhost:8000/api/auth/secondary-email/resend-code
```

### Headers:
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer TU_TOKEN_AQUI
```

### Body:
```
(No requiere body)
```

### Respuesta Exitosa:
```json
{
  "success": true,
  "message": "Nuevo código de verificación enviado a tu email secundario"
}
```

### Respuestas de Error:

**No hay email secundario:**
```json
{
  "success": false,
  "message": "No has agregado un email secundario"
}
```

**Email ya verificado:**
```json
{
  "success": false,
  "message": "El email secundario ya está verificado"
}
```

---

## 5. Consultar Información del Usuario (Ver Email Secundario)

Para verificar el estado actual del email secundario:

### Endpoint: Get User Info
```
GET http://localhost:8000/api/auth/me
```

### Headers:
```
Accept: application/json
Authorization: Bearer TU_TOKEN_AQUI
```

### Respuesta Exitosa:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Usuario Test",
      "email": "usuario@ejemplo.com",
      "secondary_email": "email_secundario@ejemplo.com",
      "secondary_email_verified": true,
      "two_factor_enabled": false,
      "roles": ["admin"],
      "permissions": [...]
    }
  }
}
```

---

## 6. Eliminar Email Secundario

Si deseas remover el email secundario:

### Endpoint: Remove Secondary Email
```
DELETE http://localhost:8000/api/auth/secondary-email/remove
```

### Headers:
```
Accept: application/json
Authorization: Bearer TU_TOKEN_AQUI
```

### Body:
```
(No requiere body)
```

### Respuesta Exitosa:
```json
{
  "success": true,
  "message": "Email secundario eliminado exitosamente"
}
```

### Respuestas de Error:

**No hay email secundario:**
```json
{
  "success": false,
  "message": "No tienes un email secundario configurado"
}
```

---

## 7. Usar Email Secundario para Recuperar Contraseña

El email secundario verificado puede ser usado para recuperar la contraseña:

### Endpoint: Forgot Password (con email secundario)
```
POST http://localhost:8000/api/auth/forgot-password
```

### Headers:
```
Content-Type: application/json
Accept: application/json
```

### Body (raw JSON):
```json
{
  "email": "email_secundario@ejemplo.com"
}
```

### Respuesta Exitosa:
```json
{
  "success": true,
  "message": "Se ha enviado un enlace de recuperación a tu correo secundario."
}
```

### Respuestas de Error:

**Email no encontrado o no verificado:**
```json
{
  "success": false,
  "message": "No existe una cuenta con este correo secundario o no ha sido verificado."
}
```

---

## Flujo Completo de Testing

### Paso 1: Login
```bash
POST /api/auth/login
Body: { "email": "usuario@ejemplo.com", "password": "password", "role": "admin" }
→ Guarda el token
```

### Paso 2: Agregar Email Secundario
```bash
POST /api/auth/secondary-email/add
Headers: Authorization: Bearer {token}
Body: { "secondary_email": "secundario@ejemplo.com" }
→ Revisa tu correo y copia el código
```

### Paso 3: Verificar Email
```bash
POST /api/auth/secondary-email/verify
Headers: Authorization: Bearer {token}
Body: { "code": "123456" }
→ Email verificado ✓
```

### Paso 4: Consultar Usuario
```bash
GET /api/auth/me
Headers: Authorization: Bearer {token}
→ Verifica que secondary_email_verified: true
```

### Paso 5 (Opcional): Testear Recuperación
```bash
POST /api/auth/forgot-password
Body: { "email": "secundario@ejemplo.com" }
→ Debe enviar link de recuperación
```

### Paso 6 (Opcional): Eliminar Email
```bash
DELETE /api/auth/secondary-email/remove
Headers: Authorization: Bearer {token}
→ Email secundario eliminado
```

---

## Casos de Prueba Recomendados

### Test 1: Agregar email ya usado
- Intenta agregar un email que ya está registrado como email principal
- Debe rechazar con error 400

### Test 2: Email secundario igual al principal
- Intenta agregar el mismo email del usuario
- Debe rechazar con error de validación

### Test 3: Código expirado
- Agrega un email secundario
- Espera más de 15 minutos
- Intenta verificar con el código antiguo
- Debe rechazar por código expirado

### Test 4: Código incorrecto
- Agrega un email secundario
- Intenta verificar con un código inventado
- Debe rechazar por código incorrecto

### Test 5: Reenvío de código
- Agrega un email secundario
- Solicita reenvío de código
- Verifica que el código antiguo ya no funciona
- Verifica con el nuevo código

### Test 6: Verificar dos veces
- Verifica un email secundario
- Intenta verificarlo de nuevo
- Debe indicar que ya está verificado

### Test 7: Eliminar y agregar de nuevo
- Agrega y verifica un email secundario
- Elimínalo
- Agrégalo de nuevo
- Debe requerir nueva verificación

---

## Notas Importantes

1. **Tiempo de Expiración:** Los códigos expiran después de 15 minutos
2. **Formato del Código:** Siempre es 6 dígitos numéricos
3. **Autenticación:** Todos los endpoints excepto `forgot-password` requieren token Bearer
4. **Base URL:** Ajusta `http://localhost:8000` según tu configuración
5. **Configuración de Email:** Asegúrate de que tu aplicación Laravel tenga configurado el envío de emails (SMTP, Mailtrap, etc.)

---

## Configuración de Postman Collection

Para facilitar el testing, puedes crear una colección en Postman con:

1. **Variable de entorno:**
   - `base_url`: `http://localhost:8000/api`
   - `token`: (se actualiza después del login)

2. **Pre-request Script para endpoints autenticados:**
```javascript
pm.request.headers.add({
    key: 'Authorization',
    value: 'Bearer ' + pm.environment.get('token')
});
```

3. **Test Script para el login:**
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("token", jsonData.data.token);
}
```

---

## Troubleshooting

### El código no llega al email
- Verifica la configuración de email en `.env`
- Revisa los logs de Laravel (`storage/logs/laravel.log`)
- Usa Mailtrap o MailHog para desarrollo
- Verifica que el servicio de email esté configurado correctamente (SMTP, API, etc.)

### Error 401 Unauthorized
- Verifica que el token esté incluido en el header
- Verifica que el token no haya expirado
- Asegúrate de usar `Bearer` antes del token
- Verifica que el usuario esté autenticado correctamente

### Error 500 Internal Server Error
- Revisa los logs de Laravel (`storage/logs/laravel.log`)
- Verifica que la base de datos tenga las columnas necesarias
- Asegúrate de que las migraciones estén ejecutadas

### Error "Column not found: secondary_email"
Si recibes un error como:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'secondary_email' in 'where clause'
```

**Solución:**
Las columnas necesarias para el email secundario ya deberían existir. Verifica ejecutando:
```bash
php artisan migrate:status
```

Busca la migración:
```
2025_11_22_000003_rename_recovery_email_to_secondary_email_in_users_table
```

Si aparece como "Pending", ejecuta:
```bash
php artisan migrate
```

Si aparece como "Ran", las columnas ya existen y el error puede ser por caché. Limpia el caché:
```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

### Verificar que las columnas existen
Puedes verificar que las columnas existen en la tabla users ejecutando:
```bash
php artisan tinker --execute="echo json_encode(\Illuminate\Support\Facades\Schema::getColumnListing('users'), JSON_PRETTY_PRINT);"
```

Deberías ver en el resultado:
- `secondary_email`
- `secondary_email_verified_at`
- `secondary_email_verification_code`
- `secondary_email_code_expires_at`

---

¡Listo! Con esta guía deberías poder testear toda la funcionalidad de email secundario en Postman.
