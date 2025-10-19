# API de Periodos Académicos - Documentación

Esta documentación describe los endpoints disponibles para gestionar los periodos académicos en el sistema LMS.

## Base URL

```
http://localhost:8000/api/lms
```

---

## 📋 Tabla de Contenidos

1. [Listar todos los periodos académicos](#1-listar-todos-los-periodos-académicos)
2. [Obtener un periodo académico específico](#2-obtener-un-periodo-académico-específico)
3. [Crear un nuevo periodo académico](#3-crear-un-nuevo-periodo-académico)
4. [Actualizar un periodo académico](#4-actualizar-un-periodo-académico)
5. [Eliminar un periodo académico](#5-eliminar-un-periodo-académico)

---

## 1. Listar todos los periodos académicos

Obtiene una lista de todos los periodos académicos, ordenados por fecha de inicio (más reciente primero).

### Request

```http
GET /api/lms/academic-periods
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Ejemplo en Postman

**Method:** `GET`
**URL:** `http://localhost:8000/api/lms/academic-periods`

### Respuesta Exitosa (200 OK)

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "academic_period_id": null,
            "name": "Semestre 2025-I",
            "start_date": "2025-01-15",
            "end_date": "2025-06-30",
            "status": "open",
            "created_at": "2025-10-19T18:30:00Z"
        },
        {
            "id": 2,
            "academic_period_id": null,
            "name": "Semestre 2024-II",
            "start_date": "2024-08-01",
            "end_date": "2024-12-20",
            "status": "closed",
            "created_at": "2024-07-15T10:00:00Z"
        },
        {
            "id": 3,
            "academic_period_id": 1,
            "name": "Módulo Intensivo - Verano 2025",
            "start_date": "2025-03-01",
            "end_date": "2025-03-31",
            "status": "upcoming",
            "created_at": "2025-10-10T12:00:00Z"
        }
    ]
}
```

---

## 2. Obtener un periodo académico específico

Obtiene los detalles de un periodo académico por su ID.

### Request

```http
GET /api/lms/academic-periods/{academic_period_id}
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Parámetros de URL

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| academic_period_id | integer | ID del periodo académico |

### Ejemplo en Postman

**Method:** `GET`
**URL:** `http://localhost:8000/api/lms/academic-periods/1`

### Respuesta Exitosa (200 OK)

```json
{
    "success": true,
    "data": {
        "id": 1,
        "academic_period_id": null,
        "name": "Semestre 2025-I",
        "start_date": "2025-01-15",
        "end_date": "2025-06-30",
        "status": "open",
        "created_at": "2025-10-19T18:30:00Z"
    }
}
```

### Respuesta de Error (404 Not Found)

```json
{
    "success": false,
    "message": "Periodo académico no encontrado"
}
```

---

## 3. Crear un nuevo periodo académico

Crea un nuevo periodo académico en el sistema.

### Request

```http
POST /api/lms/academic-periods
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Body Parameters

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| name | string | Sí | Nombre del periodo académico (máx. 255 caracteres) |
| start_date | date | Sí | Fecha de inicio (formato: YYYY-MM-DD) |
| end_date | date | Sí | Fecha de fin (formato: YYYY-MM-DD, debe ser posterior a start_date) |
| status | string | No | Estado del periodo: `open`, `closed`, `upcoming` (default: `open`) |
| academic_period_id | integer | No | ID del periodo académico padre (para sub-periodos) |

### Ejemplo en Postman

**Method:** `POST`
**URL:** `http://localhost:8000/api/lms/academic-periods`

**Body (raw - JSON):**

### Ejemplo 1: Crear un periodo académico principal

```json
{
    "name": "Semestre 2025-I",
    "start_date": "2025-01-15",
    "end_date": "2025-06-30",
    "status": "open"
}
```

### Ejemplo 2: Crear un periodo académico con estado "upcoming"

```json
{
    "name": "Semestre 2025-II",
    "start_date": "2025-08-01",
    "end_date": "2025-12-20",
    "status": "upcoming"
}
```

### Ejemplo 3: Crear un sub-periodo dentro de otro periodo

```json
{
    "name": "Módulo Intensivo - Verano 2025",
    "start_date": "2025-03-01",
    "end_date": "2025-03-31",
    "status": "upcoming",
    "academic_period_id": 1
}
```

### Respuesta Exitosa (201 Created)

```json
{
    "success": true,
    "message": "Periodo académico creado exitosamente",
    "data": {
        "id": 1,
        "academic_period_id": null,
        "name": "Semestre 2025-I",
        "start_date": "2025-01-15",
        "end_date": "2025-06-30",
        "status": "open",
        "created_at": "2025-10-19T18:30:00Z"
    }
}
```

### Respuestas de Error (422 Unprocessable Entity)

**Campos requeridos faltantes:**

```json
{
    "message": "The name field is required. (and 2 more errors)",
    "errors": {
        "name": [
            "El nombre del periodo académico es obligatorio"
        ],
        "start_date": [
            "La fecha de inicio es obligatoria"
        ],
        "end_date": [
            "La fecha de fin es obligatoria"
        ]
    }
}
```

**Fecha de fin anterior a fecha de inicio:**

```json
{
    "message": "The end date field must be a date after start date.",
    "errors": {
        "end_date": [
            "La fecha de fin debe ser posterior a la fecha de inicio"
        ]
    }
}
```

**Estado inválido:**

```json
{
    "message": "The selected status is invalid.",
    "errors": {
        "status": [
            "El estado debe ser: open, closed o upcoming"
        ]
    }
}
```

---

## 4. Actualizar un periodo académico

Actualiza los datos de un periodo académico existente.

### Request

```http
PUT /api/lms/academic-periods/{academic_period_id}
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Parámetros de URL

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| academic_period_id | integer | ID del periodo académico a actualizar |

### Body Parameters

Todos los campos son opcionales en la actualización.

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| name | string | No | Nombre del periodo académico (máx. 255 caracteres) |
| start_date | date | No | Fecha de inicio (formato: YYYY-MM-DD) |
| end_date | date | No | Fecha de fin (formato: YYYY-MM-DD, debe ser posterior a start_date) |
| status | string | No | Estado del periodo: `open`, `closed`, `upcoming` |
| academic_period_id | integer | No | ID del periodo académico padre |

### Ejemplo en Postman

**Method:** `PUT`
**URL:** `http://localhost:8000/api/lms/academic-periods/1`

**Body (raw - JSON):**

### Ejemplo 1: Actualizar solo el estado

```json
{
    "status": "closed"
}
```

### Ejemplo 2: Actualizar nombre y fechas

```json
{
    "name": "Semestre 2025-I Actualizado",
    "start_date": "2025-01-20",
    "end_date": "2025-07-05"
}
```

### Ejemplo 3: Cambiar a periodo académico hijo

```json
{
    "academic_period_id": 2
}
```

### Respuesta Exitosa (200 OK)

```json
{
    "success": true,
    "message": "Periodo académico actualizado exitosamente",
    "data": {
        "id": 1,
        "academic_period_id": null,
        "name": "Semestre 2025-I Actualizado",
        "start_date": "2025-01-20",
        "end_date": "2025-07-05",
        "status": "open",
        "created_at": "2025-10-19T18:30:00Z"
    }
}
```

### Respuesta de Error (404 Not Found)

```json
{
    "success": false,
    "message": "Periodo académico no encontrado"
}
```

### Respuesta de Error (422 Unprocessable Entity)

**Fecha de fin anterior a fecha de inicio:**

```json
{
    "message": "The end date field must be a date after start date.",
    "errors": {
        "end_date": [
            "La fecha de fin debe ser posterior a la fecha de inicio"
        ]
    }
}
```

---

## 5. Eliminar un periodo académico

Elimina un periodo académico del sistema.

### Request

```http
DELETE /api/lms/academic-periods/{academic_period_id}
```

### Headers

```
Content-Type: application/json
Accept: application/json
```

### Parámetros de URL

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| academic_period_id | integer | ID del periodo académico a eliminar |

### Ejemplo en Postman

**Method:** `DELETE`
**URL:** `http://localhost:8000/api/lms/academic-periods/1`

### Respuesta Exitosa (200 OK)

```json
{
    "success": true,
    "message": "Periodo académico eliminado exitosamente"
}
```

### Respuesta de Error (404 Not Found)

```json
{
    "success": false,
    "message": "Periodo académico no encontrado"
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
{{base_url}}{{api_prefix}}/academic-periods
```

---

## 📝 Notas Importantes

1. **Validación de fechas**: La fecha de fin (`end_date`) siempre debe ser posterior a la fecha de inicio (`start_date`).

2. **Estados disponibles**:
   - `open`: Periodo activo y abierto para inscripciones
   - `closed`: Periodo cerrado, ya finalizado
   - `upcoming`: Periodo próximo a iniciarse

3. **Periodos jerárquicos**: El campo `academic_period_id` permite crear sub-periodos dentro de periodos principales (por ejemplo, módulos intensivos dentro de un semestre).

4. **Formato de fechas**:
   - Entrada: `YYYY-MM-DD` (ej: `2025-01-15`)
   - Salida: `YYYY-MM-DD` (ej: `2025-01-15`)

5. **Timestamps**:
   - La tabla solo maneja `created_at`, no tiene `updated_at`
   - El `created_at` se retorna en formato ISO 8601

6. **Respuestas consistentes**: Todas las respuestas siguen el formato:
   - `success`: booleano indicando si la operación fue exitosa
   - `message`: mensaje descriptivo (en operaciones de creación, actualización y eliminación)
   - `data`: datos devueltos (cuando aplica)

7. **Códigos HTTP**:
   - `200 OK`: Operación exitosa (GET, PUT, DELETE)
   - `201 Created`: Recurso creado exitosamente (POST)
   - `404 Not Found`: Periodo académico no encontrado
   - `422 Unprocessable Entity`: Errores de validación

---

## 🔄 Flujo de Trabajo Recomendado

### 1. Crear un periodo académico principal

```bash
POST /api/lms/academic-periods
{
    "name": "Año Académico 2025",
    "start_date": "2025-01-01",
    "end_date": "2025-12-31",
    "status": "open"
}
```

### 2. Crear semestres dentro del año

```bash
POST /api/lms/academic-periods
{
    "name": "Semestre 2025-I",
    "start_date": "2025-01-15",
    "end_date": "2025-06-30",
    "status": "open",
    "academic_period_id": 1
}
```

### 3. Listar todos los periodos

```bash
GET /api/lms/academic-periods
```

### 4. Actualizar el estado al finalizar

```bash
PUT /api/lms/academic-periods/2
{
    "status": "closed"
}
```

### 5. Crear periodo para el siguiente ciclo

```bash
POST /api/lms/academic-periods
{
    "name": "Semestre 2025-II",
    "start_date": "2025-08-01",
    "end_date": "2025-12-20",
    "status": "upcoming",
    "academic_period_id": 1
}
```

---

## ⚠️ Errores Comunes

### Error 1: Fecha de fin anterior a fecha de inicio

**Problema:** Intentar crear un periodo donde `end_date` es anterior o igual a `start_date`.

**Solución:** Asegurarse que `end_date` sea posterior a `start_date`.

### Error 2: Estado inválido

**Problema:** Usar un valor de estado que no sea `open`, `closed` o `upcoming`.

**Solución:** Usar solo los estados permitidos.

### Error 3: Formato de fecha incorrecto

**Problema:** Enviar fechas en formato diferente a `YYYY-MM-DD`.

**Solución:** Usar el formato estándar ISO: `2025-01-15`.

### Error 4: Periodo padre no existe

**Problema:** Referenciar un `academic_period_id` que no existe en la base de datos.

**Solución:** Verificar que el ID del periodo padre existe o usar `null` para periodos principales.

---

## 🎯 Ejemplos de Casos de Uso

### Caso 1: Gestión de año académico completo

```json
// 1. Crear año académico
POST /api/lms/academic-periods
{
    "name": "Año Académico 2025",
    "start_date": "2025-01-01",
    "end_date": "2025-12-31",
    "status": "upcoming"
}

// 2. Crear semestre 1
POST /api/lms/academic-periods
{
    "name": "Semestre I - 2025",
    "start_date": "2025-01-15",
    "end_date": "2025-06-30",
    "status": "upcoming",
    "academic_period_id": 1
}

// 3. Crear semestre 2
POST /api/lms/academic-periods
{
    "name": "Semestre II - 2025",
    "start_date": "2025-08-01",
    "end_date": "2025-12-20",
    "status": "upcoming",
    "academic_period_id": 1
}
```

### Caso 2: Módulos intensivos

```json
// Crear módulo de verano dentro del semestre
POST /api/lms/academic-periods
{
    "name": "Módulo Intensivo - Verano",
    "start_date": "2025-03-01",
    "end_date": "2025-03-31",
    "status": "upcoming",
    "academic_period_id": 2
}
```

### Caso 3: Transición de estados

```json
// Cuando el periodo está por comenzar
PUT /api/lms/academic-periods/2
{
    "status": "open"
}

// Cuando el periodo finaliza
PUT /api/lms/academic-periods/2
{
    "status": "closed"
}
```

---

## 📞 Soporte

Para más información sobre el proyecto, consulta el archivo `README.md` principal.

**Autor:** Diego Panta P.
**Proyecto:** TechProc Backend v1.0
