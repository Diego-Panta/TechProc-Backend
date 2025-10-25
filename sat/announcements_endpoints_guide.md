# 📢 Guía de Endpoints - Announcements API

## 📦 Importar en Postman
Archivo: `announcements_postman_collection.json`

---

## 🌐 Endpoints PÚBLICOS (Sin autenticación)

### 1️⃣ Listar announcements públicos
```http
GET /api/developer-web/announcements/public
```

**Query Parameters (opcionales):**
- `target_page` - Filtrar por página (ej: "home", "dashboard")
- `display_type` - Filtrar por tipo (ej: "banner", "modal", "popup")

**Ejemplo:**
```bash
GET http://localhost:8000/api/developer-web/announcements/public?target_page=home
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Anuncio Importante",
      "content": "Contenido del anuncio...",
      "views": 1250,
      "status": "published",
      ...
    }
  ]
}
```

---

### 2️⃣ Ver announcement público (⭐ INCREMENTA VIEWS)
```http
GET /api/developer-web/announcements/public/{id}
```

**⚠️ IMPORTANTE:** Este endpoint incrementa automáticamente el contador de views en +1 cada vez que se llama.

**Ejemplo:**
```bash
GET http://localhost:8000/api/developer-web/announcements/public/1
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Anuncio Importante",
    "content": "Contenido completo...",
    "views": 1251,  // ⬆️ Incrementado automáticamente
    "image_url": "https://...",
    "link_url": "https://...",
    "button_text": "Más info"
  }
}
```

---

## 🔒 Endpoints PROTEGIDOS (Requieren autenticación)

**Header requerido:**
```
Authorization: Bearer tu_token_jwt_aqui
```

---

### 3️⃣ Listar todos los announcements (Admin)
```http
GET /api/developer-web/announcements
```

**Query Parameters (opcionales):**
- `status` - Filtrar por estado: "draft", "published", "archived"
- `target_page` - Filtrar por página objetivo
- `display_type` - Filtrar por tipo de display
- `per_page` - Resultados por página (default: 15)

**Ejemplo:**
```bash
GET http://localhost:8000/api/developer-web/announcements?status=published&per_page=20
```

**Headers:**
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

---

### 4️⃣ Ver announcement específico (Admin)
```http
GET /api/developer-web/announcements/{id}
```

**Nota:** Este endpoint NO incrementa las views (a diferencia del público).

**Ejemplo:**
```bash
GET http://localhost:8000/api/developer-web/announcements/1
```

---

### 5️⃣ Crear nuevo announcement
```http
POST /api/developer-web/announcements
```

**Headers:**
```
Authorization: Bearer tu_token_jwt
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "title": "Nuevo Anuncio Importante",
  "content": "Este es el contenido del anuncio. Puede incluir HTML.",
  "image_url": "https://example.com/images/announcement.jpg",
  "display_type": "banner",
  "target_page": "home",
  "link_url": "https://example.com/more-info",
  "button_text": "Más información",
  "status": "draft",
  "start_date": "2025-01-20T00:00:00Z",
  "end_date": "2025-12-31T23:59:59Z"
}
```

**Campos:**
- `title` ✅ requerido
- `content` ✅ requerido
- `image_url` - URL de la imagen
- `display_type` - Tipo de visualización (banner, modal, popup, etc.)
- `target_page` - Página donde se mostrará
- `link_url` - URL del enlace
- `button_text` - Texto del botón
- `status` - Estado: "draft", "published", "archived"
- `start_date` - Fecha de inicio
- `end_date` - Fecha de fin

**Nota:** El campo `views` se inicializa automáticamente en 0, y `created_by` se asigna del usuario autenticado.

---

### 6️⃣ Actualizar announcement (⭐ Puedes actualizar VIEWS)
```http
PUT /api/developer-web/announcements/{id}
```

**Headers:**
```
Authorization: Bearer tu_token_jwt
Content-Type: application/json
```

**Body (JSON) - Ejemplo actualizando views manualmente:**
```json
{
  "title": "Anuncio Actualizado",
  "content": "Contenido modificado",
  "status": "published",
  "views": 500
}
```

**⭐ IMPORTANTE:** Puedes incluir el campo `views` para establecer el contador manualmente a cualquier valor.

---

### 7️⃣ Eliminar announcement
```http
DELETE /api/developer-web/announcements/{id}
```

**Headers:**
```
Authorization: Bearer tu_token_jwt
```

**Ejemplo:**
```bash
DELETE http://localhost:8000/api/developer-web/announcements/1
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Anuncio eliminado exitosamente"
}
```

---

### 8️⃣ ⭐ RESETEAR VIEWS a 0 (Endpoint específico)
```http
POST /api/developer-web/announcements/{id}/reset-views
```

**Headers:**
```
Authorization: Bearer tu_token_jwt
```

**Ejemplo:**
```bash
POST http://localhost:8000/api/developer-web/announcements/1/reset-views
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Vistas reseteadas a 0",
  "views": 0
}
```

**⭐ Este es el endpoint ESPECÍFICO para resetear el contador de views a cero.**

---

### 9️⃣ ⭐ Obtener estadísticas (incluye total de views)
```http
GET /api/developer-web/announcements/stats/summary
```

**Headers:**
```
Authorization: Bearer tu_token_jwt
```

**Ejemplo:**
```bash
GET http://localhost:8000/api/developer-web/announcements/stats/summary
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "total": 50,
    "draft": 10,
    "published": 35,
    "archived": 5,
    "total_views": 15430,
    "active_count": 20
  }
}
```

**Campos de la respuesta:**
- `total` - Total de anuncios
- `draft` - Cantidad en borrador
- `published` - Cantidad publicados
- `archived` - Cantidad archivados
- `total_views` ⭐ - SUMA TOTAL de views de todos los anuncios
- `active_count` - Cantidad de anuncios actualmente activos

---

## 📊 Resumen de manejo de VIEWS

| Acción | Endpoint | Método | Efecto en Views |
|--------|----------|--------|-----------------|
| Ver público | `/public/{id}` | GET | ⬆️ +1 automático |
| Ver admin | `/{id}` | GET | Sin cambio |
| Crear | `/` | POST | Inicia en 0 |
| Actualizar | `/{id}` | PUT | Puedes modificar manualmente |
| Resetear | `/{id}/reset-views` | POST | ⭐ Establece en 0 |
| Estadísticas | `/stats/summary` | GET | Ver total acumulado |

---

## 🔑 Cómo obtener el token JWT

Si necesitas autenticarte primero:
```http
POST /api/auth/login
```

**Body:**
```json
{
  "email": "tu_email@example.com",
  "password": "tu_password"
}
```

**Respuesta:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {...}
}
```

Usa el valor del campo `token` en el header `Authorization: Bearer {token}`

---

## 📝 Variables de Postman

Configura estas variables en tu entorno de Postman:

1. `base_url` = `http://localhost:8000`
2. `token` = `tu_token_jwt_aqui` (después de hacer login)

---

## ✅ Testing Checklist

- [ ] Listar announcements públicos sin token
- [ ] Ver announcement público (verificar que views aumenta)
- [ ] Hacer login y obtener token
- [ ] Listar todos los announcements con token
- [ ] Crear nuevo announcement
- [ ] Actualizar announcement (incluyendo views manual)
- [ ] Resetear views a 0
- [ ] Ver estadísticas con total_views
- [ ] Eliminar announcement

---

**Archivo de colección Postman:** `announcements_postman_collection.json`

Importa este archivo en Postman para tener todos los endpoints listos para probar! 🚀
