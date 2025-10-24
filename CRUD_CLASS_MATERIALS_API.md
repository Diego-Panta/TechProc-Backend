# 📚 **API LMS - CRUD CLASS MATERIALS (MATERIALES DE CLASE) - Documentación Postman**

## **Base URL**
```
http://127.0.0.1:8000/api/lms/class-materials
```

---

# 📎 **CLASS MATERIALS (MATERIALES DE CLASE)**

Los materiales de clase son recursos digitales asociados a una clase específica (PDFs, videos, enlaces, etc.).

---

## **1. LISTAR MATERIALES (GET)**

### **Endpoint:**
```
GET /api/lms/class-materials
```

### **Parámetros de Query (Opcionales):**
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `limit` | integer | Número de resultados por página (default: 20) |
| `class_id` | integer | Filtrar por ID de la clase |
| `type` | string | Filtrar por tipo de material |
| `search` | string | Buscar en la URL del material |

### **Ejemplo de Request:**
```
GET http://127.0.0.1:8000/api/lms/class-materials?limit=10&class_id=1&type=PDF
```

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "class_id": 1,
        "class": {
          "id": 1,
          "class_name": "Introducción a Laravel",
          "class_date": "2025-11-05",
          "group": {
            "id": 1,
            "name": "Laravel Básico",
            "course": {
              "id": 1,
              "title": "Introducción a Laravel 11"
            }
          }
        },
        "material_url": "https://drive.google.com/file/d/123abc/view",
        "type": "PDF",
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
        "next": "http://127.0.0.1:8000/api/lms/class-materials?page=2",
        "previous": null
      }
    }
  }
}
```

---

## **2. OBTENER UN MATERIAL (GET)**

### **Endpoint:**
```
GET /api/lms/class-materials/{id}
```

### **Ejemplo de Request:**
```
GET http://127.0.0.1:8000/api/lms/class-materials/1
```

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "class_id": 1,
    "class": {
      "id": 1,
      "class_name": "Introducción a Laravel",
      "class_date": "2025-11-05",
      "group": {
        "id": 1,
        "name": "Laravel Básico",
        "course": {
          "id": 1,
          "title": "Introducción a Laravel 11"
        }
      }
    },
    "material_url": "https://drive.google.com/file/d/123abc/view",
    "type": "PDF",
    "created_at": "2025-10-23T12:00:00.000000Z",
    "updated_at": "2025-10-23T12:00:00.000000Z"
  }
}
```

### **Respuesta de Error (404):**
```json
{
  "success": false,
  "message": "Material no encontrado"
}
```

---

## **3. CREAR MATERIAL (POST)**

### **Endpoint:**
```
POST /api/lms/class-materials
```

### **Headers:**
```
Content-Type: application/json
```

### **Body (JSON):**
```json
{
  "class_id": 1,
  "material_url": "https://drive.google.com/file/d/abc123xyz/view",
  "type": "PDF"
}
```

### **Campos Requeridos:**
| Campo | Tipo | Validación | Descripción |
|-------|------|------------|-------------|
| `class_id` | integer | required, exists:classes,id | ID de la clase |
| `material_url` | string | required, url | URL del material (debe ser URL válida) |
| `type` | string | required, max:50, enum | Tipo de material |

### **Tipos de Material Válidos:**
| Tipo | Descripción | Ejemplo de URL |
|------|-------------|----------------|
| `PDF` | Documentos PDF | Google Drive, Dropbox |
| `Video` | Videos educativos | YouTube, Vimeo, Google Drive |
| `Enlace` | Enlaces web | Sitios web, artículos |
| `Documento` | Documentos Word, Excel | Google Docs, OneDrive |
| `Presentación` | Presentaciones | Google Slides, SlideShare |
| `Imagen` | Imágenes | Imgur, Google Photos |
| `Audio` | Archivos de audio | SoundCloud, Google Drive |
| `Otro` | Otros tipos | Cualquier otro recurso |

### **Ejemplos de URLs Válidas:**
```json
// PDF en Google Drive
{
  "class_id": 1,
  "material_url": "https://drive.google.com/file/d/1a2b3c4d5e6f/view",
  "type": "PDF"
}

// Video de YouTube
{
  "class_id": 1,
  "material_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
  "type": "Video"
}

// Presentación en Google Slides
{
  "class_id": 1,
  "material_url": "https://docs.google.com/presentation/d/1abc2def3ghi/edit",
  "type": "Presentación"
}

// Enlace a documentación
{
  "class_id": 1,
  "material_url": "https://laravel.com/docs/11.x/routing",
  "type": "Enlace"
}
```

### **Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Material creado exitosamente",
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
    "material_url": ["La URL del material debe ser válida"],
    "type": ["El tipo debe ser: PDF, Video, Enlace, Documento, Presentación, Imagen, Audio u Otro"],
    "class_id": ["La clase seleccionada no existe"]
  }
}
```

---

## **4. ACTUALIZAR MATERIAL (PUT)**

### **Endpoint:**
```
PUT /api/lms/class-materials/{id}
```

### **Headers:**
```
Content-Type: application/json
```

### **Body (JSON) - Todos los campos son opcionales:**
```json
{
  "material_url": "https://drive.google.com/file/d/nuevaurl123/view",
  "type": "Video"
}
```

### **Campos Opcionales:**
| Campo | Tipo | Validación | Descripción |
|-------|------|------------|-------------|
| `class_id` | integer | sometimes, exists:classes,id | ID de la clase |
| `material_url` | string | sometimes, url | URL del material |
| `type` | string | sometimes, max:50, enum | Tipo de material |

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Material actualizado exitosamente"
}
```

### **Respuesta de Error (404):**
```json
{
  "success": false,
  "message": "Material no encontrado"
}
```

---

## **5. ELIMINAR MATERIAL (DELETE)**

### **Endpoint:**
```
DELETE /api/lms/class-materials/{id}
```

### **Ejemplo de Request:**
```
DELETE http://127.0.0.1:8000/api/lms/class-materials/2
```

### **Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Material eliminado exitosamente"
}
```

### **Respuesta de Error (404):**
```json
{
  "success": false,
  "message": "Material no encontrado"
}
```

---

# 🔄 **FLUJO COMPLETO DE PRUEBA EN POSTMAN**

## **Paso 1: Crear una Clase (o usar una existente)**
```
POST http://127.0.0.1:8000/api/lms/classes

Body:
{
  "group_id": 1,
  "class_name": "Introducción a Laravel",
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
    "id": 5
  }
}
```

## **Paso 2: Agregar Material PDF**
```
POST http://127.0.0.1:8000/api/lms/class-materials

Body:
{
  "class_id": 5,
  "material_url": "https://drive.google.com/file/d/1a2b3c4d5e6f/view",
  "type": "PDF"
}

Respuesta:
{
  "success": true,
  "message": "Material creado exitosamente",
  "data": {
    "id": 10
  }
}
```

## **Paso 3: Agregar Video de YouTube**
```
POST http://127.0.0.1:8000/api/lms/class-materials

Body:
{
  "class_id": 5,
  "material_url": "https://www.youtube.com/watch?v=abc123xyz",
  "type": "Video"
}

Respuesta:
{
  "success": true,
  "message": "Material creado exitosamente",
  "data": {
    "id": 11
  }
}
```

## **Paso 4: Agregar Enlace a Documentación**
```
POST http://127.0.0.1:8000/api/lms/class-materials

Body:
{
  "class_id": 5,
  "material_url": "https://laravel.com/docs/11.x",
  "type": "Enlace"
}
```

## **Paso 5: Listar Todos los Materiales de la Clase**
```
GET http://127.0.0.1:8000/api/lms/class-materials?class_id=5

Respuesta:
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 10,
        "class_id": 5,
        "material_url": "https://drive.google.com/file/d/1a2b3c4d5e6f/view",
        "type": "PDF"
      },
      {
        "id": 11,
        "class_id": 5,
        "material_url": "https://www.youtube.com/watch?v=abc123xyz",
        "type": "Video"
      },
      {
        "id": 12,
        "class_id": 5,
        "material_url": "https://laravel.com/docs/11.x",
        "type": "Enlace"
      }
    ]
  }
}
```

## **Paso 6: Filtrar solo PDFs**
```
GET http://127.0.0.1:8000/api/lms/class-materials?class_id=5&type=PDF
```

---

# 📋 **EJEMPLOS DE CASOS DE USO**

## **Caso 1: Agregar múltiples materiales a una clase**

### Material 1: Presentación
```json
POST /api/lms/class-materials
{
  "class_id": 5,
  "material_url": "https://docs.google.com/presentation/d/abc123/edit",
  "type": "Presentación"
}
```

### Material 2: Código en GitHub
```json
POST /api/lms/class-materials
{
  "class_id": 5,
  "material_url": "https://github.com/usuario/proyecto/tree/main/examples",
  "type": "Enlace"
}
```

### Material 3: Ejercicios en PDF
```json
POST /api/lms/class-materials
{
  "class_id": 5,
  "material_url": "https://drive.google.com/file/d/ejercicios123/view",
  "type": "PDF"
}
```

---

## **Caso 2: Organizar materiales por tipo**

### Listar solo Videos:
```
GET /api/lms/class-materials?type=Video
```

### Listar solo PDFs:
```
GET /api/lms/class-materials?type=PDF
```

### Listar solo Enlaces:
```
GET /api/lms/class-materials?type=Enlace
```

---

## **Caso 3: Actualizar URL de un material**
```
PUT /api/lms/class-materials/10

Body:
{
  "material_url": "https://drive.google.com/file/d/nueva_version_123/view"
}
```

---

## **Caso 4: Cambiar tipo de material**
```
PUT /api/lms/class-materials/11

Body:
{
  "type": "Presentación"
}
```

---

# ⚠️ **VALIDACIONES IMPORTANTES**

## **1. Validación de URLs:**
- ✅ Ejemplos válidos:
  - `"https://drive.google.com/file/d/123/view"`
  - `"https://www.youtube.com/watch?v=abc"`
  - `"https://docs.google.com/document/d/xyz/edit"`
  - `"https://laravel.com/docs"`
- ❌ Ejemplos inválidos:
  - `"drive.google.com/file"` (falta https://)
  - `"www.youtube.com"` (falta https://)
  - `"archivo.pdf"` (no es URL)

## **2. Tipos de Material:**
Solo se aceptan estos tipos:
- `PDF`
- `Video`
- `Enlace`
- `Documento`
- `Presentación`
- `Imagen`
- `Audio`
- `Otro`

---

# 🌐 **EJEMPLOS DE URLS POR PLATAFORMA**

## **Google Drive:**
```
PDF: https://drive.google.com/file/d/1a2b3c4d5e6f/view
Video: https://drive.google.com/file/d/abc123xyz/preview
```

## **YouTube:**
```
https://www.youtube.com/watch?v=dQw4w9WgXcQ
https://youtu.be/dQw4w9WgXcQ
```

## **Google Docs:**
```
Documento: https://docs.google.com/document/d/abc123/edit
Hoja: https://docs.google.com/spreadsheets/d/xyz789/edit
Presentación: https://docs.google.com/presentation/d/def456/edit
```

## **Dropbox:**
```
https://www.dropbox.com/s/abc123xyz/archivo.pdf?dl=0
```

## **OneDrive:**
```
https://onedrive.live.com/view.aspx?resid=ABC123
```

## **Vimeo:**
```
https://vimeo.com/123456789
```

---

# ✅ **CÓDIGOS DE RESPUESTA HTTP**

| Código | Significado | Cuándo ocurre |
|--------|-------------|---------------|
| 200 | OK | GET, PUT, DELETE exitosos |
| 201 | Created | POST exitoso (material creado) |
| 404 | Not Found | Material o clase no encontrados |
| 422 | Unprocessable Entity | Errores de validación |
| 500 | Internal Server Error | Error del servidor |

---

# 🔗 **RELACIÓN CON OTRAS ENTIDADES**

```
Course (Curso)
    └── Group (Grupo)
            └── Class (Clase)
                    └── ClassMaterial (Material de Clase) ← Estamos aquí
```

---

# 📁 **ARCHIVOS CREADOS**

1. ✅ **Modelo:** `app/Domains/Lms/Models/ClassMaterial.php`
2. ✅ **Interface Repositorio:** `app/Domains/Lms/Repositories/ClassMaterialRepositoryInterface.php`
3. ✅ **Repositorio:** `app/Domains/Lms/Repositories/ClassMaterialRepository.php`
4. ✅ **Servicio:** `app/Domains/Lms/Services/ClassMaterialService.php`
5. ✅ **Request Create:** `app/Domains/Lms/Http/Requests/CreateClassMaterialRequest.php`
6. ✅ **Request Update:** `app/Domains/Lms/Http/Requests/UpdateClassMaterialRequest.php`
7. ✅ **Resource:** `app/Domains/Lms/Resources/ClassMaterialResource.php`
8. ✅ **Collection:** `app/Domains/Lms/Resources/ClassMaterialCollection.php`
9. ✅ **Controller:** `app/Domains/Lms/Http/Controllers/ClassMaterialController.php`
10. ✅ **Rutas:** `app/Domains/Lms/routes.php` (actualizado)
11. ✅ **Service Provider:** `app/Providers/DomainServiceProvider.php` (actualizado)

---

# 💡 **CONSEJOS Y MEJORES PRÁCTICAS**

1. **Organización:** Agrupa materiales por tipo para facilitar su búsqueda
2. **URLs permanentes:** Usa URLs permanentes (no temporales) de servicios como Google Drive
3. **Permisos:** Asegúrate de que las URLs sean públicas o compartidas correctamente
4. **Nomenclatura:** Usa nombres descriptivos en las URLs cuando sea posible
5. **Respaldo:** Mantén copias de seguridad de los materiales importantes

---

**🚀 ¡El CRUD de Class Materials está listo para usarse!**
