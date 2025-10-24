# 📚 **API LMS - CRUD CLASSES (CLASES) - Documentación Postman**

## **Base URL**
```
http://127.0.0.1:8000/api/lms/classes
```

---

# 📅 **CLASSES (CLASES)**

## **1. LISTAR CLASES (GET)**

### **Endpoint:**
```
GET /api/lms/classes
```

### **Parámetros de Query (Opcionales):**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `limit` | integer | Número de resultados por página (default: 20) |
| `group_id` | integer | Filtrar por ID del grupo |
| `class_status` | string | Filtrar por estado (SCHEDULED, IN_PROGRESS, FINISHED, CANCELLED) |
| `search` | string | Buscar por nombre de clase |
| `class_date_from` | date | Filtrar desde fecha (YYYY-MM-DD) |
| `class_date_to` | date | Filtrar hasta fecha (YYYY-MM-DD) |

### **Ejemplo de Request:**
```
GET http://127.0.0.1:8000/api/lms/classes?limit=10&group_id=1&class_status=SCHEDULED
```

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "group_id": 1,
        "group": {
          "id": 1,
          "code": "GRP-0001",
          "name": "Laravel Básico",
          "course": {
            "id": 1,
            "title": "Introducción a Laravel 11"
          }
        },
        "class_name": "Introducción y Configuración",
        "meeting_url": "https://meet.google.com/abc-defg-hij",
        "description": "Primera clase del curso - Configuración del entorno",
        "class_date": "2025-11-05",
        "start_time": "09:00:00",
        "end_time": "11:00:00",
        "class_status": "SCHEDULED",
        "created_at": "2025-10-23T12:00:00.000000Z",
        "updated_at": "2025-10-23T12:00:00.000000Z"
      }
    ],
    "pagination": {
      "total": 15,
      "count": 10,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 2,
      "links": {
        "next": "http://127.0.0.1:8000/api/lms/classes?page=2",
        "previous": null
      }
    }
  }
}
```

---

## **2. OBTENER UNA CLASE (GET)**

### **Endpoint:**
```
GET /api/lms/classes/{id}
```

### **Ejemplo de Request:**
```
GET http://127.0.0.1:8000/api/lms/classes/1
```

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "group_id": 1,
    "group": {
      "id": 1,
      "code": "GRP-0001",
      "name": "Laravel Básico",
      "course": {
        "id": 1,
        "title": "Introducción a Laravel 11"
      }
    },
    "class_name": "Introducción y Configuración",
    "meeting_url": "https://meet.google.com/abc-defg-hij",
    "description": "Primera clase del curso - Configuración del entorno de desarrollo con Laravel 11",
    "class_date": "2025-11-05",
    "start_time": "09:00:00",
    "end_time": "11:00:00",
    "class_status": "SCHEDULED",
    "created_at": "2025-10-23T12:00:00.000000Z",
    "updated_at": "2025-10-23T12:00:00.000000Z"
  }
}
```

### **Respuesta de Error (404):**
```json
{
  "success": false,
  "message": "Clase no encontrada"
}
```

---

## **3. CREAR CLASE (POST)**

### **Endpoint:**
```
POST /api/lms/classes
```

### **Headers:**
```
Content-Type: application/json
```

### **Body (JSON):**
```json
{
  "group_id": 1,
  "class_name": "Introducción a Laravel",
  "meeting_url": "https://meet.google.com/abc-defg-hij",
  "description": "Primera clase del curso - Instalación y configuración",
  "class_date": "2025-11-05",
  "start_time": "09:00",
  "end_time": "11:00",
  "class_status": "SCHEDULED"
}
```

### **Campos Requeridos:**
| Campo | Tipo | Validación | Descripción |
|-------|------|------------|-------------|
| `group_id` | integer | required, exists:groups,id | ID del grupo |
| `class_name` | string | required, max:100 | Nombre de la clase |
| `class_date` | date | required, after_or_equal:today | Fecha de la clase (YYYY-MM-DD) |
| `start_time` | time | required, format:HH:mm | Hora de inicio (formato 24h: HH:mm) |
| `end_time` | time | required, format:HH:mm, after:start_time | Hora de fin (debe ser después de start_time) |

### **Campos Opcionales:**
| Campo | Tipo | Default | Descripción |
|-------|------|---------|-------------|
| `meeting_url` | string | null | URL de la reunión virtual (debe ser URL válida) |
| `description` | string | null | Descripción detallada de la clase |
| `class_status` | string | SCHEDULED | Estado: SCHEDULED, IN_PROGRESS, FINISHED, CANCELLED |

### **Formatos de Hora Válidos:**
- ✅ `"09:00"` (9:00 AM)
- ✅ `"14:30"` (2:30 PM)
- ✅ `"18:45"` (6:45 PM)
- ❌ `"9:00"` (debe ser 09:00)
- ❌ `"9:00 AM"` (no se acepta AM/PM)

### **Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Clase creada exitosamente",
  "data": {
    "id": 2
  }
}
```

### **Respuesta de Error de Validación (422):**
```json
{
  "success": false,
  "errors": {
    "class_date": ["La fecha de la clase no puede ser anterior a hoy"],
    "end_time": ["La hora de fin debe ser posterior a la hora de inicio"],
    "start_time": ["La hora de inicio debe tener el formato HH:mm"]
  }
}
```

---

## **4. ACTUALIZAR CLASE (PUT)**

### **Endpoint:**
```
PUT /api/lms/classes/{id}
```

### **Headers:**
```
Content-Type: application/json
```

### **Body (JSON) - Todos los campos son opcionales:**
```json
{
  "class_name": "Introducción a Laravel - Actualizado",
  "class_status": "IN_PROGRESS",
  "meeting_url": "https://zoom.us/j/123456789",
  "description": "Descripción actualizada de la clase"
}
```

### **Campos Opcionales:**
| Campo | Tipo | Validación | Descripción |
|-------|------|------------|-------------|
| `group_id` | integer | sometimes, exists:groups,id | ID del grupo |
| `class_name` | string | sometimes, max:100 | Nombre de la clase |
| `meeting_url` | string | nullable, url | URL de reunión |
| `description` | string | nullable | Descripción |
| `class_date` | date | sometimes | Fecha de la clase |
| `start_time` | time | sometimes, format:HH:mm | Hora de inicio |
| `end_time` | time | sometimes, format:HH:mm | Hora de fin |
| `class_status` | string | sometimes, enum | Estado de la clase |

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Clase actualizada exitosamente"
}
```

### **Respuesta de Error (404):**
```json
{
  "success": false,
  "message": "Clase no encontrada"
}
```

---

## **5. ELIMINAR CLASE (DELETE)**

### **Endpoint:**
```
DELETE /api/lms/classes/{id}
```

### **Ejemplo de Request:**
```
DELETE http://127.0.0.1:8000/api/lms/classes/2
```

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Clase eliminada exitosamente"
}
```

### **Respuesta de Error (404):**
```json
{
  "success": false,
  "message": "Clase no encontrada"
}
```

---

## **Estados Válidos de Clase**

| Estado | Descripción |
|--------|-------------|
| `SCHEDULED` | Programada (default) |
| `IN_PROGRESS` | En progreso / En vivo |
| `FINISHED` | Finalizada |
| `CANCELLED` | Cancelada |

---

# 🔄 **FLUJO COMPLETO DE PRUEBA EN POSTMAN**

## **Paso 1: Crear un Grupo (o usar uno existente)**
```
POST http://127.0.0.1:8000/api/lms/groups

Body:
{
  "course_id": 1,
  "code": "GRP-TEST-01",
  "name": "Grupo de Prueba",
  "start_date": "2025-11-01",
  "end_date": "2025-12-15",
  "status": "open"
}

Respuesta:
{
  "success": true,
  "message": "Grupo creado exitosamente",
  "data": {
    "id": 5
  }
}
```

## **Paso 2: Crear una Clase**
```
POST http://127.0.0.1:8000/api/lms/classes

Body:
{
  "group_id": 5,
  "class_name": "Sesión 1: Fundamentos de Laravel",
  "meeting_url": "https://meet.google.com/xyz-abcd-efg",
  "description": "Introducción a Laravel, MVC y Routing",
  "class_date": "2025-11-05",
  "start_time": "09:00",
  "end_time": "11:00",
  "class_status": "SCHEDULED"
}

Respuesta:
{
  "success": true,
  "message": "Clase creada exitosamente",
  "data": {
    "id": 10
  }
}
```

## **Paso 3: Listar Clases del Grupo**
```
GET http://127.0.0.1:8000/api/lms/classes?group_id=5

Respuesta:
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 10,
        "group_id": 5,
        "class_name": "Sesión 1: Fundamentos de Laravel",
        "class_date": "2025-11-05",
        "start_time": "09:00:00",
        "end_time": "11:00:00",
        "class_status": "SCHEDULED"
      }
    ]
  }
}
```

## **Paso 4: Iniciar la Clase (Cambiar estado)**
```
PUT http://127.0.0.1:8000/api/lms/classes/10

Body:
{
  "class_status": "IN_PROGRESS"
}

Respuesta:
{
  "success": true,
  "message": "Clase actualizada exitosamente"
}
```

## **Paso 5: Finalizar la Clase**
```
PUT http://127.0.0.1:8000/api/lms/classes/10

Body:
{
  "class_status": "FINISHED"
}
```

---

# 📋 **EJEMPLOS DE CASOS DE USO**

## **Caso 1: Crear múltiples clases para un curso**

### Crear Clase 1:
```json
POST /api/lms/classes
{
  "group_id": 5,
  "class_name": "Sesión 1: Introducción",
  "class_date": "2025-11-05",
  "start_time": "09:00",
  "end_time": "11:00"
}
```

### Crear Clase 2:
```json
POST /api/lms/classes
{
  "group_id": 5,
  "class_name": "Sesión 2: MVC y Routing",
  "class_date": "2025-11-07",
  "start_time": "09:00",
  "end_time": "11:00"
}
```

### Crear Clase 3:
```json
POST /api/lms/classes
{
  "group_id": 5,
  "class_name": "Sesión 3: Bases de Datos",
  "class_date": "2025-11-10",
  "start_time": "09:00",
  "end_time": "11:00"
}
```

---

## **Caso 2: Filtrar clases por rango de fechas**
```
GET /api/lms/classes?class_date_from=2025-11-01&class_date_to=2025-11-30
```

---

## **Caso 3: Buscar clases por nombre**
```
GET /api/lms/classes?search=Introducción
```

---

## **Caso 4: Listar solo clases programadas**
```
GET /api/lms/classes?class_status=SCHEDULED
```

---

## **Caso 5: Cancelar una clase**
```
PUT /api/lms/classes/10

Body:
{
  "class_status": "CANCELLED"
}
```

---

# ⚠️ **VALIDACIONES IMPORTANTES**

## **1. Validación de Fechas:**
- ❌ No se pueden crear clases con fecha pasada
- ✅ `class_date` debe ser igual o mayor a hoy
- ✅ Ejemplo válido: `"2025-11-05"`
- ❌ Ejemplo inválido: `"2025-10-20"` (si hoy es 23 de octubre)

## **2. Validación de Horas:**
- ✅ Formato correcto: `"09:00"`, `"14:30"`, `"18:45"`
- ❌ Formato incorrecto: `"9:00"`, `"9:00 AM"`, `"09:00:00"`
- ✅ `end_time` debe ser mayor que `start_time`
- ❌ Ejemplo inválido: start_time: `"10:00"`, end_time: `"09:00"`

## **3. Validación de URLs:**
- ✅ Ejemplos válidos:
  - `"https://meet.google.com/abc-defg-hij"`
  - `"https://zoom.us/j/123456789"`
  - `"https://teams.microsoft.com/l/meetup-join/..."`
- ❌ Ejemplos inválidos:
  - `"meet.google.com"` (falta https://)
  - `"google.com/meet"` (falta https://)

---

# ✅ **CÓDIGOS DE RESPUESTA HTTP**

| Código | Significado | Cuándo ocurre |
|--------|-------------|---------------|
| 200 | OK | GET, PUT, DELETE exitosos |
| 201 | Created | POST exitoso (clase creada) |
| 404 | Not Found | Clase o grupo no encontrado |
| 422 | Unprocessable Entity | Errores de validación |
| 500 | Internal Server Error | Error del servidor |

---

# 📁 **ARCHIVOS CREADOS**

1. ✅ **Modelo:** `app/Domains/Lms/Models/ClassModel.php` (actualizado)
2. ✅ **Interface Repositorio:** `app/Domains/Lms/Repositories/ClassRepositoryInterface.php`
3. ✅ **Repositorio:** `app/Domains/Lms/Repositories/ClassRepository.php`
4. ✅ **Servicio:** `app/Domains/Lms/Services/ClassService.php`
5. ✅ **Request Create:** `app/Domains/Lms/Http/Requests/CreateClassRequest.php`
6. ✅ **Request Update:** `app/Domains/Lms/Http/Requests/UpdateClassRequest.php`
7. ✅ **Resource:** `app/Domains/Lms/Resources/ClassResource.php`
8. ✅ **Collection:** `app/Domains/Lms/Resources/ClassCollection.php`
9. ✅ **Controller:** `app/Domains/Lms/Http/Controllers/ClassController.php`
10. ✅ **Rutas:** `app/Domains/Lms/routes.php` (actualizado)
11. ✅ **Service Provider:** `app/Providers/DomainServiceProvider.php` (actualizado)

---

**🚀 ¡El CRUD de Classes está listo para usarse!**
