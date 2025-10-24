# API Groups - Documentación Postman

## Información General

**Base URL:** `http://localhost:8000/api/lms`

**Content-Type:** `application/json`

**Accept:** `application/json`

---

## 📋 Endpoints Disponibles

### 1. Listar Grupos (GET)

**Endpoint:** `GET /lms/groups`

**Descripción:** Obtiene una lista paginada de grupos con filtros opcionales.

#### Query Parameters (Opcionales)

| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| `limit` | integer | Cantidad de resultados por página (default: 20) | `10` |
| `course_id` | integer | Filtrar por ID de curso | `1` |
| `status` | string | Filtrar por estado del grupo | `open` |
| `search` | string | Buscar por código o nombre | `GRP-001` |
| `start_date_from` | date | Filtrar desde fecha de inicio | `2025-01-01` |
| `start_date_to` | date | Filtrar hasta fecha de inicio | `2025-12-31` |

#### Ejemplo de Request

```http
GET http://localhost:8000/api/lms/groups?limit=10&status=open&course_id=1
```

#### Ejemplo de Response (200 OK)

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "course_id": 1,
        "course": {
          "id": 1,
          "title": "Introducción a Python",
          "name": "Python Básico"
        },
        "code": "GRP-0001",
        "name": "Grupo Enero 2025",
        "start_date": "2025-01-15",
        "end_date": "2025-03-15",
        "status": "open",
        "created_at": "2025-01-10T10:30:00.000000Z",
        "updated_at": "2025-01-10T10:30:00.000000Z",
        "participants_count": 25,
        "classes_count": 12
      }
    ],
    "pagination": {
      "total": 50,
      "count": 10,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 5,
      "links": {
        "next": "http://localhost:8000/api/lms/groups?page=2",
        "previous": null
      }
    }
  }
}
```

---

### 2. Ver Detalle de Grupo (GET)

**Endpoint:** `GET /lms/groups/{id}`

**Descripción:** Obtiene el detalle de un grupo específico.

#### Path Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `id` | integer | Sí | ID del grupo |

#### Ejemplo de Request

```http
GET http://localhost:8000/api/lms/groups/1
```

#### Ejemplo de Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "course_id": 1,
    "course": {
      "id": 1,
      "title": "Introducción a Python",
      "name": "Python Básico"
    },
    "code": "GRP-0001",
    "name": "Grupo Enero 2025",
    "start_date": "2025-01-15",
    "end_date": "2025-03-15",
    "status": "open",
    "created_at": "2025-01-10T10:30:00.000000Z",
    "updated_at": "2025-01-10T10:30:00.000000Z"
  }
}
```

#### Ejemplo de Response - Error (404 Not Found)

```json
{
  "success": false,
  "message": "Grupo no encontrado"
}
```

---

### 3. Crear Grupo (POST)

**Endpoint:** `POST /lms/groups`

**Descripción:** Crea un nuevo grupo.

#### Request Body (JSON)

| Campo | Tipo | Requerido | Descripción | Valores |
|-------|------|-----------|-------------|---------|
| `course_id` | integer | Sí | ID del curso | Debe existir en la tabla courses |
| `code` | string | Sí | Código único del grupo (max: 50) | Ej: "GRP-0001" |
| `name` | string | Sí | Nombre del grupo (max: 200) | Ej: "Grupo Enero 2025" |
| `start_date` | date | Sí | Fecha de inicio (formato: Y-m-d) | Ej: "2025-01-15" |
| `end_date` | date | Sí | Fecha de fin (formato: Y-m-d) | Debe ser posterior a start_date |
| `status` | string | No | Estado del grupo (default: draft) | draft, approved, open, in_progress, completed, cancelled, suspended |

#### Ejemplo de Request

```http
POST http://localhost:8000/api/lms/groups
Content-Type: application/json

{
  "course_id": 1,
  "code": "GRP-0001",
  "name": "Grupo Enero 2025",
  "start_date": "2025-01-15",
  "end_date": "2025-03-15",
  "status": "draft"
}
```

#### Ejemplo de Response (201 Created)

```json
{
  "success": true,
  "message": "Grupo creado exitosamente",
  "data": {
    "id": 1
  }
}
```

#### Ejemplo de Response - Error de Validación (422 Unprocessable Entity)

```json
{
  "message": "The code has already been taken. (and 1 more error)",
  "errors": {
    "code": [
      "El código del grupo ya existe"
    ],
    "end_date": [
      "La fecha de fin debe ser posterior a la fecha de inicio"
    ]
  }
}
```

---

### 4. Actualizar Grupo (PUT)

**Endpoint:** `PUT /lms/groups/{id}`

**Descripción:** Actualiza un grupo existente.

#### Path Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `id` | integer | Sí | ID del grupo a actualizar |

#### Request Body (JSON)

Todos los campos son opcionales. Solo envía los campos que deseas actualizar.

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `course_id` | integer | No | ID del curso |
| `code` | string | No | Código único del grupo (max: 50) |
| `name` | string | No | Nombre del grupo (max: 200) |
| `start_date` | date | No | Fecha de inicio (formato: Y-m-d) |
| `end_date` | date | No | Fecha de fin (formato: Y-m-d) |
| `status` | string | No | Estado del grupo |

#### Ejemplo de Request

```http
PUT http://localhost:8000/api/lms/groups/1
Content-Type: application/json

{
  "name": "Grupo Enero 2025 - Actualizado",
  "status": "open",
  "end_date": "2025-04-15"
}
```

#### Ejemplo de Response (200 OK)

```json
{
  "success": true,
  "message": "Grupo actualizado exitosamente"
}
```

#### Ejemplo de Response - Error (404 Not Found)

```json
{
  "success": false,
  "message": "Grupo no encontrado"
}
```

---

### 5. Eliminar Grupo (DELETE)

**Endpoint:** `DELETE /lms/groups/{id}`

**Descripción:** Elimina un grupo existente.

#### Path Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `id` | integer | Sí | ID del grupo a eliminar |

#### Ejemplo de Request

```http
DELETE http://localhost:8000/api/lms/groups/1
```

#### Ejemplo de Response (200 OK)

```json
{
  "success": true,
  "message": "Grupo eliminado exitosamente"
}
```

#### Ejemplo de Response - Error (404 Not Found)

```json
{
  "success": false,
  "message": "Grupo no encontrado"
}
```

---

## 📊 Estados de Grupo

| Estado | Descripción |
|--------|-------------|
| `draft` | Borrador (valor por defecto) |
| `approved` | Aprobado para comenzar |
| `open` | Abierto para inscripciones |
| `in_progress` | En progreso / activo |
| `completed` | Completado / finalizado |
| `cancelled` | Cancelado |
| `suspended` | Suspendido temporalmente |

---

## 🔍 Ejemplos de Filtros

### Buscar grupos por curso específico

```http
GET http://localhost:8000/api/lms/groups?course_id=1
```

### Buscar grupos activos

```http
GET http://localhost:8000/api/lms/groups?status=in_progress
```

### Buscar grupos por código o nombre

```http
GET http://localhost:8000/api/lms/groups?search=GRP-001
```

### Buscar grupos que inicien en un rango de fechas

```http
GET http://localhost:8000/api/lms/groups?start_date_from=2025-01-01&start_date_to=2025-12-31
```

### Combinar múltiples filtros

```http
GET http://localhost:8000/api/lms/groups?course_id=1&status=open&limit=20
```

---

## 🧪 Colección Postman - Importar JSON

Puedes importar esta colección directamente en Postman:

```json
{
  "info": {
    "name": "LMS - Groups API",
    "description": "Colección completa del CRUD de Groups",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api/lms",
      "type": "string"
    }
  ],
  "item": [
    {
      "name": "1. Listar Grupos",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "url": {
          "raw": "{{base_url}}/groups?limit=10",
          "host": ["{{base_url}}"],
          "path": ["groups"],
          "query": [
            {
              "key": "limit",
              "value": "10"
            },
            {
              "key": "course_id",
              "value": "1",
              "disabled": true
            },
            {
              "key": "status",
              "value": "open",
              "disabled": true
            },
            {
              "key": "search",
              "value": "GRP",
              "disabled": true
            }
          ]
        }
      }
    },
    {
      "name": "2. Ver Detalle de Grupo",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "url": {
          "raw": "{{base_url}}/groups/1",
          "host": ["{{base_url}}"],
          "path": ["groups", "1"]
        }
      }
    },
    {
      "name": "3. Crear Grupo",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          },
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"course_id\": 1,\n  \"code\": \"GRP-0001\",\n  \"name\": \"Grupo Enero 2025\",\n  \"start_date\": \"2025-01-15\",\n  \"end_date\": \"2025-03-15\",\n  \"status\": \"draft\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/groups",
          "host": ["{{base_url}}"],
          "path": ["groups"]
        }
      }
    },
    {
      "name": "4. Actualizar Grupo",
      "request": {
        "method": "PUT",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          },
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"name\": \"Grupo Enero 2025 - Actualizado\",\n  \"status\": \"open\"\n}"
        },
        "url": {
          "raw": "{{base_url}}/groups/1",
          "host": ["{{base_url}}"],
          "path": ["groups", "1"]
        }
      }
    },
    {
      "name": "5. Eliminar Grupo",
      "request": {
        "method": "DELETE",
        "header": [
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "url": {
          "raw": "{{base_url}}/groups/1",
          "host": ["{{base_url}}"],
          "path": ["groups", "1"]
        }
      }
    }
  ]
}
```

---

## 💡 Notas Importantes

1. **Validación de Fechas:** La `end_date` siempre debe ser posterior a la `start_date`.

2. **Código Único:** El campo `code` debe ser único en toda la tabla de grupos.

3. **Curso Existente:** El `course_id` debe corresponder a un curso existente en la base de datos.

4. **Estado por Defecto:** Si no se especifica el `status` al crear un grupo, se asignará automáticamente `draft`.

5. **Paginación:** Por defecto se muestran 20 grupos por página. Puedes modificar esto con el parámetro `limit`.

6. **Relaciones:** El endpoint incluye automáticamente información del curso relacionado y contadores de participantes y clases (cuando están cargados).

7. **Soft Delete:** Los grupos se eliminan definitivamente de la base de datos (hard delete).

---

## 🐛 Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Solicitud exitosa |
| 201 | Created - Recurso creado exitosamente |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Error de validación |
| 500 | Internal Server Error - Error del servidor |

---

## 📝 Changelog

- **v1.0** (2025-01-23): Documentación inicial del CRUD de Groups
