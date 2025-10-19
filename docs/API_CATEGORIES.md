# API de Categorías - Documentación

Esta documentación describe los endpoints disponibles para gestionar las categorías de cursos en el sistema LMS.

## Base URL

```
http://localhost:8000/api/lms
```

---

## 📋 Tabla de Contenidos

1. [Listar todas las categorías](#1-listar-todas-las-categorías)
2. [Obtener una categoría específica](#2-obtener-una-categoría-específica)
3. [Crear una nueva categoría](#3-crear-una-nueva-categoría)
4. [Actualizar una categoría](#4-actualizar-una-categoría)
5. [Eliminar una categoría](#5-eliminar-una-categoría)

---

## 1. Listar todas las categorías

Obtiene una lista de todas las categorías disponibles, incluyendo el conteo de cursos asociados.

### Request

```http
GET /api/lms/categories
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Ejemplo en Postman

**Method:** `GET`
**URL:** `http://localhost:8000/api/lms/categories`

### Respuesta Exitosa (200 OK)

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "category_id": null,
            "name": "Desarrollo Web",
            "slug": "desarrollo-web",
            "image": "https://example.com/images/desarrollo-web.jpg",
            "courses_count": 15,
            "created_at": "2024-01-15T10:30:00Z"
        },
        {
            "id": 2,
            "category_id": 1,
            "name": "Frontend",
            "slug": "frontend",
            "image": "https://example.com/images/frontend.jpg",
            "courses_count": 8,
            "created_at": "2024-01-16T14:20:00Z"
        },
        {
            "id": 3,
            "category_id": null,
            "name": "Data Science",
            "slug": "data-science",
            "image": "https://example.com/images/data-science.jpg",
            "courses_count": 12,
            "created_at": "2024-01-17T09:15:00Z"
        }
    ]
}
```

---

## 2. Obtener una categoría específica

Obtiene los detalles de una categoría por su ID.

### Request

```http
GET /api/lms/categories/{category_id}
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Parámetros de URL

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| category_id | integer | ID de la categoría |

### Ejemplo en Postman

**Method:** `GET`
**URL:** `http://localhost:8000/api/lms/categories/1`

### Respuesta Exitosa (200 OK)

```json
{
    "success": true,
    "data": {
        "id": 1,
        "category_id": null,
        "name": "Desarrollo Web",
        "slug": "desarrollo-web",
        "image": "https://example.com/images/desarrollo-web.jpg",
        "courses_count": 15,
        "created_at": "2024-01-15T10:30:00Z"
    }
}
```

### Respuesta de Error (404 Not Found)

```json
{
    "success": false,
    "message": "Categoría no encontrada"
}
```

---

## 3. Crear una nueva categoría

Crea una nueva categoría en el sistema.

### Request

```http
POST /api/lms/categories
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Body Parameters

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| name | string | Sí | Nombre de la categoría (máx. 100 caracteres) |
| slug | string | Sí | Slug único de la categoría (máx. 100 caracteres) |
| image | string | No | URL de la imagen de la categoría (máx. 255 caracteres) |
| category_id | integer | No | ID de la categoría padre (para subcategorías) |

### Ejemplo en Postman

**Method:** `POST`
**URL:** `http://localhost:8000/api/lms/categories`

**Body (raw - JSON):**

```json
{
    "name": "Desarrollo Web",
    "slug": "desarrollo-web",
    "image": "https://example.com/images/desarrollo-web.jpg",
    "category_id": null
}
```

### Ejemplo 2: Crear una subcategoría

```json
{
    "name": "React Avanzado",
    "slug": "react-avanzado",
    "image": "https://example.com/images/react-avanzado.jpg",
    "category_id": 2
}
```

### Respuesta Exitosa (201 Created)

```json
{
    "success": true,
    "message": "Categoría creada exitosamente",
    "data": {
        "id": 4,
        "category_id": null,
        "name": "Desarrollo Web",
        "slug": "desarrollo-web",
        "image": "https://example.com/images/desarrollo-web.jpg",
        "created_at": "2024-10-19T15:30:00Z"
    }
}
```

### Respuestas de Error (422 Unprocessable Entity)

**Slug duplicado:**

```json
{
    "message": "The slug has already been taken.",
    "errors": {
        "slug": [
            "Este slug ya está en uso"
        ]
    }
}
```

**Campos requeridos faltantes:**

```json
{
    "message": "The name field is required. (and 1 more error)",
    "errors": {
        "name": [
            "El nombre de la categoría es obligatorio"
        ],
        "slug": [
            "El slug es obligatorio"
        ]
    }
}
```

---

## 4. Actualizar una categoría

Actualiza los datos de una categoría existente.

### Request

```http
PUT /api/lms/categories/{category_id}
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Parámetros de URL

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| category_id | integer | ID de la categoría a actualizar |

### Body Parameters

Todos los campos son opcionales en la actualización.

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| name | string | No | Nombre de la categoría (máx. 100 caracteres) |
| slug | string | No | Slug único de la categoría (máx. 100 caracteres) |
| image | string | No | URL de la imagen de la categoría (máx. 255 caracteres) |
| category_id | integer | No | ID de la categoría padre |

### Ejemplo en Postman

**Method:** `PUT`
**URL:** `http://localhost:8000/api/lms/categories/1`

**Body (raw - JSON):**

**Ejemplo 1: Actualizar solo el nombre**

```json
{
    "name": "Desarrollo Web Frontend"
}
```

**Ejemplo 2: Actualizar múltiples campos**

```json
{
    "name": "Desarrollo Web Completo",
    "slug": "desarrollo-web-completo",
    "image": "https://example.com/images/desarrollo-web-nuevo.jpg"
}
```

**Ejemplo 3: Convertir en subcategoría**

```json
{
    "category_id": 5
}
```

### Respuesta Exitosa (200 OK)

```json
{
    "success": true,
    "message": "Categoría actualizada exitosamente",
    "data": {
        "id": 1,
        "category_id": null,
        "name": "Desarrollo Web Frontend",
        "slug": "desarrollo-web",
        "image": "https://example.com/images/desarrollo-web.jpg",
        "courses_count": 15,
        "created_at": "2024-01-15T10:30:00Z"
    }
}
```

### Respuesta de Error (404 Not Found)

```json
{
    "success": false,
    "message": "Categoría no encontrada"
}
```

### Respuesta de Error (422 Unprocessable Entity)

**Slug duplicado:**

```json
{
    "message": "The slug has already been taken.",
    "errors": {
        "slug": [
            "Este slug ya está en uso"
        ]
    }
}
```

---

## 5. Eliminar una categoría

Elimina una categoría del sistema.

### Request

```http
DELETE /api/lms/categories/{category_id}
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Parámetros de URL

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| category_id | integer | ID de la categoría a eliminar |

### Ejemplo en Postman

**Method:** `DELETE`
**URL:** `http://localhost:8000/api/lms/categories/1`

### Respuesta Exitosa (200 OK)

```json
{
    "success": true,
    "message": "Categoría eliminada exitosamente"
}
```

### Respuesta de Error (404 Not Found)

```json
{
    "success": false,
    "message": "Categoría no encontrada"
}
```

---

## 🧪 Colección de Postman

### Configuración de Variables de Entorno

Crea un entorno en Postman con las siguientes variables:

```
base_url = http://localhost:8000
api_prefix = /api/lms
```

Luego puedes usar las URLs como:

```
{{base_url}}{{api_prefix}}/categories
```

---

## 📝 Notas Importantes

1. **Slug único**: El campo `slug` debe ser único en toda la tabla de categorías. Laravel automáticamente valida esto.

2. **Categorías padre**: El campo `category_id` permite crear jerarquías de categorías (categorías y subcategorías).

3. **Validaciones**: Todos los campos tienen validaciones tanto de tipo como de longitud máxima.

4. **Respuestas consistentes**: Todas las respuestas siguen el formato:
   - `success`: booleano indicando si la operación fue exitosa
   - `message`: mensaje descriptivo (en operaciones de creación, actualización y eliminación)
   - `data`: datos devueltos (cuando aplica)

5. **Códigos HTTP**:
   - `200 OK`: Operación exitosa (GET, PUT, DELETE)
   - `201 Created`: Recurso creado exitosamente (POST)
   - `404 Not Found`: Categoría no encontrada
   - `422 Unprocessable Entity`: Errores de validación

---

## 🔄 Flujo de Trabajo Recomendado

### 1. Crear una categoría principal

```bash
POST /api/lms/categories
{
    "name": "Programación",
    "slug": "programacion",
    "image": "https://example.com/programacion.jpg"
}
```

### 2. Crear subcategorías

```bash
POST /api/lms/categories
{
    "name": "JavaScript",
    "slug": "javascript",
    "category_id": 1,
    "image": "https://example.com/javascript.jpg"
}
```

### 3. Listar todas las categorías

```bash
GET /api/lms/categories
```

### 4. Actualizar una categoría

```bash
PUT /api/lms/categories/2
{
    "name": "JavaScript Moderno"
}
```

### 5. Eliminar una categoría

```bash
DELETE /api/lms/categories/2
```

---

## ⚠️ Errores Comunes

### Error 1: Slug duplicado

**Problema:** Intentar crear o actualizar una categoría con un slug que ya existe.

**Solución:** Cambiar el valor del slug a uno único.

### Error 2: Categoría padre no existe

**Problema:** El `category_id` referencia una categoría que no existe.

**Solución:** Verificar que el ID de la categoría padre exista o usar `null`.

### Error 3: Campos exceden longitud máxima

**Problema:** Los campos `name`, `slug` o `image` exceden la longitud permitida.

**Solución:** Reducir el tamaño de los valores según las especificaciones:
- `name`: máximo 100 caracteres
- `slug`: máximo 100 caracteres
- `image`: máximo 255 caracteres

---

## 🎯 Ejemplos de Casos de Uso

### Caso 1: Sistema de categorías multinivel

```json
// Categoría principal
{
    "name": "Tecnología",
    "slug": "tecnologia"
}

// Subcategoría nivel 1
{
    "name": "Desarrollo Web",
    "slug": "desarrollo-web",
    "category_id": 1
}

// Subcategoría nivel 2
{
    "name": "Frontend Frameworks",
    "slug": "frontend-frameworks",
    "category_id": 2
}
```

### Caso 2: Migración de categoría

```json
// Convertir una categoría independiente en subcategoría
PUT /api/lms/categories/5
{
    "category_id": 3
}

// Convertir una subcategoría en categoría principal
PUT /api/lms/categories/5
{
    "category_id": null
}
```

---

## 📞 Soporte

Para más información sobre el proyecto, consulta el archivo `README.md` principal.

**Autor:** Diego Panta P.
**Proyecto:** TechProc Backend v1.0
