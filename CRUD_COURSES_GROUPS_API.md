# 📚 **API LMS - CRUD COURSES & GROUPS - Documentación Postman**

## **Base URL**
```
http://127.0.0.1:8000/api/lms
```

---

# 🎓 **COURSES (CURSOS)**

## **1. CREAR CURSO (POST) - ¡Crea automáticamente un grupo!**

### **Endpoint:**
```
POST /api/lms/courses
```

### **Headers:**
```
Content-Type: application/json
```

### **Body (JSON):**
```json
{
  "title": "Introducción a Laravel 11",
  "name": "Laravel Básico",
  "description": "Curso completo de Laravel para principiantes",
  "level": "basic",
  "course_image": "https://example.com/images/laravel.jpg",
  "video_url": "https://youtube.com/watch?v=example",
  "duration": 40.5,
  "sessions": 12,
  "selling_price": 299.99,
  "discount_price": 199.99,
  "prerequisites": "Conocimientos básicos de PHP",
  "certificate_name": true,
  "certificate_issuer": "Academia Tech",
  "bestseller": false,
  "featured": true,
  "highest_rated": false,
  "status": true
}
```

### **Campos Requeridos:**
| Campo | Tipo | Validación | Descripción |
|-------|------|------------|-------------|
| `title` | string | required, max:255 | Título del curso |
| `description` | string | required | Descripción completa del curso |
| `level` | string | required, enum(basic, intermediate, advanced) | Nivel del curso |
| `duration` | numeric | required, min:0 | Duración en horas (puede ser decimal) |
| `sessions` | integer | required, min:1 | Número de sesiones |
| `selling_price` | numeric | required, min:0 | Precio de venta |

### **Campos Opcionales:**
| Campo | Tipo | Default | Descripción |
|-------|------|---------|-------------|
| `name` | string | null | Nombre corto del curso |
| `course_image` | string | null | URL de la imagen del curso |
| `video_url` | string | null | URL del video promocional |
| `discount_price` | numeric | null | Precio con descuento |
| `prerequisites` | string | null | Prerrequisitos del curso |
| `certificate_name` | boolean | false | ¿Otorga certificado? |
| `certificate_issuer` | string | null | Emisor del certificado |
| `bestseller` | boolean | false | ¿Es bestseller? |
| `featured` | boolean | false | ¿Es destacado? |
| `highest_rated` | boolean | false | ¿Es el mejor valorado? |
| `status` | boolean | true | Estado del curso (activo/inactivo) |

### **Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Curso creado exitosamente",
  "data": {
    "id": 5,
    "course_id": 5
  }
}
```

### **✨ ¡IMPORTANTE! - Creación Automática de Grupo:**

Al crear un curso, **automáticamente se crea un grupo** con las siguientes características:

| Campo del Grupo | Valor Generado |
|-----------------|----------------|
| `course_id` | ID del curso creado |
| `code` | `GRP-0001` (auto-incremental, único) |
| `name` | Toma el valor de `name` del curso, o `title` si name es null |
| `start_date` | Hoy + 7 días |
| `end_date` | start_date + 30 días |
| `status` | `draft` si el curso está activo, `cancelled` si está inactivo |

**Ejemplo de grupo creado automáticamente:**
```json
{
  "id": 8,
  "course_id": 5,
  "code": "GRP-0005",
  "name": "Laravel Básico",
  "start_date": "2025-10-30",
  "end_date": "2025-11-29",
  "status": "draft"
}
```

### **Para verificar el grupo creado:**
```
GET /api/lms/groups?course_id=5
```

---

## **2. LISTAR CURSOS (GET)**

### **Endpoint:**
```
GET /api/lms/courses
```

### **Parámetros de Query (Opcionales):**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `limit` | integer | Resultados por página (default: 20) |
| `level` | string | Filtrar por nivel (basic, intermediate, advanced) |
| `status` | boolean | Filtrar por estado (true/false) |
| `search` | string | Buscar en título o nombre |
| `category_id` | integer | Filtrar por categoría |

### **Ejemplo:**
```
GET http://127.0.0.1:8000/api/lms/courses?limit=10&level=basic&status=true
```

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 5,
        "course_id": 5,
        "title": "Introducción a Laravel 11",
        "name": "Laravel Básico",
        "description": "Curso completo de Laravel para principiantes",
        "level": "basic",
        "duration": "40.50",
        "sessions": 12,
        "selling_price": "299.99",
        "discount_price": "199.99",
        "status": true,
        "created_at": "2025-10-23T12:00:00.000000Z"
      }
    ],
    "pagination": {
      "total": 25,
      "current_page": 1,
      "per_page": 10,
      "total_pages": 3
    }
  }
}
```

---

## **3. OBTENER UN CURSO (GET)**

### **Endpoint:**
```
GET /api/lms/courses/{course_id}
```

### **Ejemplo:**
```
GET http://127.0.0.1:8000/api/lms/courses/5
```

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "course_id": 5,
    "title": "Introducción a Laravel 11",
    "name": "Laravel Básico",
    "description": "Curso completo de Laravel para principiantes",
    "level": "basic",
    "course_image": "https://example.com/images/laravel.jpg",
    "video_url": "https://youtube.com/watch?v=example",
    "duration": "40.50",
    "sessions": 12,
    "selling_price": "299.99",
    "discount_price": "199.99",
    "prerequisites": "Conocimientos básicos de PHP",
    "certificate_name": true,
    "certificate_issuer": "Academia Tech",
    "bestseller": false,
    "featured": true,
    "highest_rated": false,
    "status": true,
    "created_at": "2025-10-23T12:00:00.000000Z",
    "updated_at": "2025-10-23T12:00:00.000000Z"
  }
}
```

---

## **4. ACTUALIZAR CURSO (PUT)**

### **Endpoint:**
```
PUT /api/lms/courses/{course_id}
```

### **Headers:**
```
Content-Type: application/json
```

### **Body (JSON) - Todos los campos son opcionales:**
```json
{
  "title": "Introducción a Laravel 11 - Actualizado",
  "selling_price": 349.99,
  "discount_price": 249.99,
  "status": true
}
```

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Curso actualizado exitosamente"
}
```

---

## **5. ELIMINAR CURSO (DELETE)**

### **Endpoint:**
```
DELETE /api/lms/courses/{course_id}
```

### **Ejemplo:**
```
DELETE http://127.0.0.1:8000/api/lms/courses/5
```

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Curso eliminado exitosamente"
}
```

---

# 👥 **GROUPS (GRUPOS)**

## **1. LISTAR GRUPOS (GET)**

### **Endpoint:**
```
GET /api/lms/groups
```

### **Parámetros de Query (Opcionales):**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `limit` | integer | Resultados por página (default: 20) |
| `course_id` | integer | Filtrar por ID del curso |
| `status` | string | Filtrar por estado |
| `search` | string | Buscar por código o nombre |
| `start_date_from` | date | Desde fecha (YYYY-MM-DD) |
| `start_date_to` | date | Hasta fecha (YYYY-MM-DD) |

### **Ejemplo:**
```
GET http://127.0.0.1:8000/api/lms/groups?course_id=5&status=draft
```

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 8,
        "course_id": 5,
        "course": {
          "id": 5,
          "title": "Introducción a Laravel 11",
          "name": "Laravel Básico"
        },
        "code": "GRP-0005",
        "name": "Laravel Básico",
        "start_date": "2025-10-30",
        "end_date": "2025-11-29",
        "status": "draft",
        "created_at": "2025-10-23T12:00:00.000000Z",
        "updated_at": "2025-10-23T12:00:00.000000Z"
      }
    ],
    "pagination": {
      "total": 15,
      "count": 10,
      "per_page": 20,
      "current_page": 1,
      "total_pages": 1,
      "links": {
        "next": null,
        "previous": null
      }
    }
  }
}
```

---

## **2. OBTENER UN GRUPO (GET)**

### **Endpoint:**
```
GET /api/lms/groups/{id}
```

### **Ejemplo:**
```
GET http://127.0.0.1:8000/api/lms/groups/8
```

---

## **3. CREAR GRUPO (POST)**

### **Endpoint:**
```
POST /api/lms/groups
```

### **Headers:**
```
Content-Type: application/json
```

### **Body (JSON):**
```json
{
  "course_id": 5,
  "code": "GRP-TARDE-01",
  "name": "Grupo Tarde - Turno A",
  "start_date": "2025-11-15",
  "end_date": "2025-12-30",
  "status": "draft"
}
```

### **Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Grupo creado exitosamente",
  "data": {
    "id": 9
  }
}
```

---

## **4. ACTUALIZAR GRUPO (PUT)**

### **Endpoint:**
```
PUT /api/lms/groups/{id}
```

### **Body (JSON):**
```json
{
  "name": "Grupo Tarde - Turno A (Actualizado)",
  "status": "open"
}
```

---

## **5. ELIMINAR GRUPO (DELETE)**

### **Endpoint:**
```
DELETE /api/lms/groups/{id}
```

---

# 🔄 **FLUJO COMPLETO DE PRUEBA EN POSTMAN**

## **Paso 1: Crear un Curso**
```
POST http://127.0.0.1:8000/api/lms/courses

Body:
{
  "title": "React JS Avanzado",
  "description": "Curso avanzado de React con Hooks y Context",
  "level": "advanced",
  "duration": 60,
  "sessions": 20,
  "selling_price": 499.99,
  "status": true
}

Respuesta:
{
  "success": true,
  "message": "Curso creado exitosamente",
  "data": {
    "id": 10,
    "course_id": 10
  }
}
```

## **Paso 2: Verificar el Grupo Creado Automáticamente**
```
GET http://127.0.0.1:8000/api/lms/groups?course_id=10

Respuesta:
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 15,
        "course_id": 10,
        "code": "GRP-0010",
        "name": "React JS Avanzado",
        "start_date": "2025-10-30",
        "end_date": "2025-11-29",
        "status": "draft"
      }
    ]
  }
}
```

## **Paso 3: Crear Grupos Adicionales (Opcional)**
```
POST http://127.0.0.1:8000/api/lms/groups

Body:
{
  "course_id": 10,
  "code": "GRP-REACT-NOCHE",
  "name": "React - Turno Noche",
  "start_date": "2025-12-01",
  "end_date": "2026-01-15",
  "status": "open"
}
```

## **Paso 4: Listar Todos los Grupos del Curso**
```
GET http://127.0.0.1:8000/api/lms/groups?course_id=10
```

---

# 📋 **ESTADOS VÁLIDOS**

## **Course Status (Boolean):**
- `true` → Curso activo
- `false` → Curso inactivo

## **Group Status (String):**
- `draft` → Borrador (default para cursos activos)
- `approved` → Aprobado
- `open` → Abierto para inscripciones
- `in_progress` → En progreso
- `completed` → Completado
- `cancelled` → Cancelado (default para cursos inactivos)
- `suspended` → Suspendido

## **Course Level (String):**
- `basic` → Básico
- `intermediate` → Intermedio
- `advanced` → Avanzado

---

# ⚙️ **LÓGICA DE CREACIÓN AUTOMÁTICA DE GRUPOS**

Cuando creas un curso mediante `POST /api/lms/courses`, el sistema automáticamente:

1. ✅ Crea el curso con los datos proporcionados
2. ✅ Asigna `course_id = id` si no existe
3. ✅ **Crea automáticamente un grupo** con:
   - **code:** Generado automáticamente (`GRP-0001`, `GRP-0002`, etc.)
   - **name:** Toma el `name` del curso, si no existe usa el `title`
   - **start_date:** Hoy + 7 días
   - **end_date:** start_date + 30 días
   - **status:** `draft` si el curso está activo, `cancelled` si está inactivo
   - **course_id:** El ID del curso recién creado

---

# 🎯 **CASOS DE USO COMUNES**

## **Crear curso con nombre personalizado para el grupo:**
```json
{
  "title": "Python para Data Science",
  "name": "Python DS",
  "description": "...",
  "level": "intermediate",
  "duration": 50,
  "sessions": 15,
  "selling_price": 399.99
}
```
→ El grupo se creará con nombre: **"Python DS"**

## **Crear curso sin nombre (usa el título):**
```json
{
  "title": "JavaScript Moderno",
  "description": "...",
  "level": "basic",
  "duration": 30,
  "sessions": 10,
  "selling_price": 199.99
}
```
→ El grupo se creará con nombre: **"JavaScript Moderno"**

---

# ✅ **VALIDACIONES Y ERRORES**

## **Error 422 - Validación:**
```json
{
  "success": false,
  "errors": {
    "title": ["El título del curso es obligatorio"],
    "level": ["El nivel debe ser: basic, intermediate o advanced"],
    "selling_price": ["El precio de venta es obligatorio"]
  }
}
```

## **Error 404 - No encontrado:**
```json
{
  "success": false,
  "message": "Curso no encontrado"
}
```

---

**🚀 ¡La API está lista para usarse con creación automática de grupos!**
