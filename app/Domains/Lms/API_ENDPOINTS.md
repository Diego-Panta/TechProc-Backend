---

## 3. MÓDULO GESTOR LMS

### 3.1. Gestión de Cursos

#### 3.1.1. Listar Cursos

**Endpoint:** `GET /lms/courses`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (int): Número de página
- `limit` (int): Registros por página
- `level` (string): basic, intermediate, advanced
- `status` (boolean): true (activo), false (inactivo)
- `search` (string): Buscar por título
- `category_id` (int): Filtrar por categoría

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "courses": [
      {
        "id": 1,
        "course_id": 1,
        "title": "Introducción a Python",
        "description": "Curso básico de programación en Python",
        "level": "basic",
        "course_image": "https://...",
        "duration": 40.00,
        "sessions": 12,
        "selling_price": 299.99,
        "discount_price": 199.99,
        "status": true,
        "bestseller": true,
        "featured": false,
        "created_at": "2025-01-10T00:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 3,
      "total_records": 45,
      "per_page": 20
    }
  }
}
```

**Tablas BD Relacionadas:** `courses`

---

#### 3.1.2. Obtener Detalles de Curso

**Endpoint:** `GET /lms/courses/{course_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "course_id": 1,
    "title": "Introducción a Python",
    "description": "Curso básico de programación en Python para principiantes",
    "level": "basic",
    "course_image": "https://...",
    "video_url": "https://...",
    "duration": 40.00,
    "sessions": 12,
    "selling_price": 299.99,
    "discount_price": 199.99,
    "prerequisites": "Conocimientos básicos de computación",
    "certificate_name": true,
    "certificate_issuer": "INCADEV",
    "bestseller": true,
    "featured": false,
    "highest_rated": false,
    "status": true,
    "categories": [
      {
        "category_id": 1,
        "name": "Programación",
        "slug": "programacion"
      }
    ],
    "instructors": [
      {
        "instructor_id": 5,
        "user_id": 23,
        "name": "Dr. Roberto Silva",
        "expertise_area": "Ciencias de la Computación"
      }
    ],
    "contents": [
      {
        "id": 1,
        "session": 1,
        "type": "video",
        "title": "Introducción al curso",
        "order_number": 1
      }
    ],
    "created_at": "2025-01-10T00:00:00Z",
    "updated_at": "2025-10-01T00:00:00Z"
  }
}
```

**Tablas BD Relacionadas:** `courses`, `course_categories`, `categories`, `course_instructors`, `instructors`, `course_contents`

---

#### 3.1.3. Crear Curso

**Endpoint:** `POST /lms/courses`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "title": "JavaScript Avanzado",
  "description": "Curso avanzado de JavaScript moderno",
  "level": "advanced",
  "course_image": "https://...",
  "video_url": "https://...",
  "duration": 60.00,
  "sessions": 20,
  "selling_price": 499.99,
  "discount_price": 349.99,
  "prerequisites": "JavaScript básico e intermedio",
  "certificate_name": true,
  "certificate_issuer": "INCADEV",
  "status": true,
  "category_ids": [1, 3],
  "instructor_ids": [5, 8]
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Curso creado exitosamente",
  "data": {
    "id": 46,
    "course_id": 46
  }
}
```

**Tablas BD Relacionadas:** `courses`, `course_categories`, `course_instructors`

---

#### 3.1.4. Actualizar Curso

**Endpoint:** `PUT /lms/courses/{course_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "title": "JavaScript Avanzado - Edición 2025",
  "description": "Curso avanzado actualizado con las últimas características de JavaScript",
  "selling_price": 449.99,
  "discount_price": 299.99,
  "status": true
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Curso actualizado exitosamente"
}
```

**Tablas BD Relacionadas:** `courses`

---

#### 3.1.5. Eliminar Curso

**Endpoint:** `DELETE /lms/courses/{course_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Curso eliminado exitosamente"
}
```

**Tablas BD Relacionadas:** `courses`

---

### 3.2. Gestión de Estudiantes

#### 3.2.1. Listar Estudiantes

**Endpoint:** `GET /lms/students`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (int): Número de página
- `limit` (int): Registros por página
- `status` (string): active, inactive
- `search` (string): Buscar por nombre o email
- `company_id` (int): Filtrar por empresa

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "students": [
      {
        "id": 1,
        "student_id": 1,
        "user_id": 45,
        "first_name": "Ana",
        "last_name": "Martínez",
        "email": "ana.martinez@ejemplo.com",
        "phone": "+51 987654321",
        "status": "active",
        "company": {
          "id": 3,
          "name": "Tech Solutions SAC"
        },
        "created_at": "2025-02-01T00:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 12,
      "total_records": 235,
      "per_page": 20
    }
  }
}
```

**Tablas BD Relacionadas:** `students`, `users`, `companies`

---

#### 3.2.2. Obtener Detalles de Estudiante

**Endpoint:** `GET /lms/students/{student_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "student_id": 1,
    "user_id": 45,
    "first_name": "Ana",
    "last_name": "Martínez",
    "email": "ana.martinez@ejemplo.com",
    "phone": "+51 987654321",
    "document_number": "12345678",
    "status": "active",
    "company": {
      "id": 3,
      "name": "Tech Solutions SAC",
      "industry": "Tecnología"
    },
    "enrollments": [
      {
        "enrollment_id": 23,
        "course_title": "Introducción a Python",
        "enrollment_date": "2025-03-01",
        "status": "active"
      }
    ],
    "created_at": "2025-02-01T00:00:00Z"
  }
}
```

**Tablas BD Relacionadas:** `students`, `users`, `companies`, `enrollments`, `enrollment_details`

---

#### 3.2.3. Crear Estudiante

**Endpoint:** `POST /lms/students`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "user_id": 78,
  "company_id": 3,
  "document_number": "87654321",
  "first_name": "Luis",
  "last_name": "Torres",
  "email": "luis.torres@ejemplo.com",
  "phone": "+51 912345678",
  "status": "active"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Estudiante creado exitosamente",
  "data": {
    "id": 236,
    "student_id": 236
  }
}
```

**Tablas BD Relacionadas:** `students`

---

#### 3.2.4. Actualizar Estudiante

**Endpoint:** `PUT /lms/students/{student_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "phone": "+51 912345679",
  "company_id": 5,
  "status": "active"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Estudiante actualizado exitosamente"
}
```

**Tablas BD Relacionadas:** `students`

---

#### 3.2.5. Eliminar Estudiante

**Endpoint:** `DELETE /lms/students/{student_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Estudiante eliminado exitosamente"
}
```

**Tablas BD Relacionadas:** `students`

---

### 3.3. Gestión de Instructores

#### 3.3.1. Listar Instructores

**Endpoint:** `GET /lms/instructors`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (int): Número de página
- `limit` (int): Registros por página
- `status` (string): active, inactive
- `expertise_area` (string): Filtrar por área de expertise

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "instructors": [
      {
        "id": 1,
        "instructor_id": 1,
        "user_id": 23,
        "name": "Dr. Roberto Silva",
        "email": "roberto.silva@incadev.com",
        "bio": "PhD en Ciencias de la Computación con 15 años de experiencia",
        "expertise_area": "Programación, Inteligencia Artificial",
        "status": "active",
        "courses_count": 8,
        "created_at": "2025-01-05T00:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 2,
      "total_records": 24,
      "per_page": 20
    }
  }
}
```

**Tablas BD Relacionadas:** `instructors`, `users`

---

#### 3.3.2. Crear Instructor

**Endpoint:** `POST /lms/instructors`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "user_id": 89,
  "bio": "Ingeniero de Software con especialización en desarrollo web",
  "expertise_area": "JavaScript, React, Node.js",
  "status": "active"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Instructor creado exitosamente",
  "data": {
    "id": 25,
    "instructor_id": 25
  }
}
```

**Tablas BD Relacionadas:** `instructors`

---

#### 3.3.3. Actualizar Instructor

**Endpoint:** `PUT /lms/instructors/{instructor_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "bio": "Ingeniero de Software Senior con especialización en desarrollo web full-stack",
  "expertise_area": "JavaScript, React, Node.js, Python, Docker",
  "status": "active"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Instructor actualizado exitosamente"
}
```

**Tablas BD Relacionadas:** `instructors`

---

### 3.4. Categorías de Cursos

#### 3.4.1. Listar Categorías

**Endpoint:** `GET /lms/categories`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "name": "Programación",
      "slug": "programacion",
      "image": "https://...",
      "courses_count": 45,
      "created_at": "2025-01-01T00:00:00Z"
    }
  ]
}
```

**Tablas BD Relacionadas:** `categories`

---

### 3.5. Matrículas (Enrollments)

#### 3.5.1. Crear Matrícula

**Endpoint:** `POST /lms/enrollments`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "student_id": 45,
  "academic_period_id": 3,
  "course_offering_ids": [12, 15, 18],
  "enrollment_type": "new",
  "enrollment_date": "2025-03-01",
  "status": "active"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Matrícula creada exitosamente",
  "data": {
    "enrollment_id": 456
  }
}
```

**Tablas BD Relacionadas:** `enrollments`, `enrollment_details`

---

#### 3.5.2. Listar Matrículas

**Endpoint:** `GET /lms/enrollments`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `student_id` (int): Filtrar por estudiante
- `academic_period_id` (int): Filtrar por período académico
- `status` (string): active, inactive

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "enrollment_id": 456,
      "student": {
        "id": 45,
        "name": "Ana Martínez"
      },
      "academic_period": {
        "id": 3,
        "name": "2025-I"
      },
      "enrollment_date": "2025-03-01",
      "status": "active",
      "courses": [
        {
          "course_offering_id": 12,
          "course_title": "Introducción a Python"
        }
      ]
    }
  ]
}
```

**Tablas BD Relacionadas:** `enrollments`, `enrollment_details`, `students`, `academic_periods`, `course_offerings`

---

## 4. MÓDULO SOPORTE TÉCNICO

### 4.1. Gestión de Tickets

#### 4.1.1. Listar Todos los Tickets

**Endpoint:** `GET /tickets`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (int): Número de página
- `limit` (int): Registros por página
- `status` (string): abierto, en_proceso, resuelto, cerrado
- `priority` (string): baja, media, alta, critica
- `category` (string): Categoría del ticket
- `assigned_technician` (int): ID del técnico asignado
- `search` (string): Buscar por título o descripción

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "tickets": [
      {
        "id": 1,
        "ticket_id": 1,
        "title": "Error al cargar página de cursos",
        "description": "La página de cursos no carga correctamente",
        "priority": "alta",
        "status": "abierto",
        "category": "Web",
        "user": {
          "id": 56,
          "name": "Carlos Mendoza",
          "email": "carlos.mendoza@ejemplo.com"
        },
        "assigned_technician": {
          "id": 12,
          "name": "Pedro Sánchez"
        },
        "creation_date": "2025-10-08T09:00:00Z",
        "assignment_date": "2025-10-08T09:15:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 8,
      "total_records": 156,
      "per_page": 20
    }
  }
}
```

**Tablas BD Relacionadas:** `tickets`, `users`, `employees`

---

#### 4.1.2. Obtener Detalles de Ticket

**Endpoint:** `GET /tickets/{ticket_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "ticket_id": 1,
    "title": "Error al cargar página de cursos",
    "description": "La página de cursos no carga correctamente al hacer clic en el botón de navegación",
    "priority": "alta",
    "status": "en_proceso",
    "category": "Web",
    "notes": "Se está investigando el problema",
    "user": {
      "id": 56,
      "name": "Carlos Mendoza",
      "email": "carlos.mendoza@ejemplo.com",
      "phone": "+51 987654321"
    },
    "assigned_technician": {
      "id": 12,
      "employee_id": 12,
      "name": "Pedro Sánchez",
      "speciality": "Desarrollo Web"
    },
    "creation_date": "2025-10-08T09:00:00Z",
    "assignment_date": "2025-10-08T09:15:00Z",
    "resolution_date": null,
    "close_date": null,
    "tracking": [
      {
        "ticket_tracking_id": 1,
        "comment": "Ticket asignado al técnico",
        "action_type": "assignment",
        "follow_up_date": "2025-10-08T09:15:00Z"
      },
      {
        "ticket_tracking_id": 2,
        "comment": "Iniciando investigación del problema",
        "action_type": "update",
        "follow_up_date": "2025-10-08T10:00:00Z"
      }
    ]
  }
}
```

**Tablas BD Relacionadas:** `tickets`, `users`, `employees`, `ticket_trackings`

---

#### 4.1.3. Crear Ticket

**Endpoint:** `POST /tickets`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "user_id": 56,
  "title": "Error al cargar página de cursos",
  "description": "La página de cursos no carga correctamente al hacer clic en el botón de navegación",
  "priority": "alta",
  "category": "Web"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Ticket creado exitosamente",
  "data": {
    "id": 157,
    "ticket_id": 157
  }
}
```

**Tablas BD Relacionadas:** `tickets`

---

#### 4.1.4. Tomar Ticket (Asignar a Técnico)

**Endpoint:** `POST /tickets/{ticket_id}/take`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "technician_id": 12
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Ticket asignado exitosamente"
}
```

**Tablas BD Relacionadas:** `tickets`, `ticket_trackings`

---

#### 4.1.5. Actualizar Estado de Ticket

**Endpoint:** `PUT /tickets/{ticket_id}/status`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "status": "resuelto",
  "notes": "Problema corregido. Se actualizó el código de la página."
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Estado del ticket actualizado exitosamente"
}
```

**Tablas BD Relacionadas:** `tickets`, `ticket_trackings`

---

#### 4.1.6. Resolver Ticket

**Endpoint:** `POST /tickets/{ticket_id}/resolve`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "resolution_notes": "Se corrigió el error en el componente de navegación. Se actualizó la ruta de carga de cursos.",
  "technician_id": 12
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Ticket resuelto exitosamente"
}
```

**Tablas BD Relacionadas:** `tickets`, `ticket_trackings`

---

#### 4.1.7. Cerrar Ticket

**Endpoint:** `POST /tickets/{ticket_id}/close`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "closing_notes": "Usuario confirmó que el problema fue solucionado"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Ticket cerrado exitosamente"
}
```

**Tablas BD Relacionadas:** `tickets`, `ticket_trackings`

---

### 4.2. Escalaciones de Tickets

#### 4.2.1. Escalar Ticket

**Endpoint:** `POST /tickets/{ticket_id}/escalate`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "technician_origin_id": 12,
  "technician_destiny_id": 18,
  "escalation_reason": "Requiere conocimientos avanzados de infraestructura",
  "observations": "El problema es más complejo de lo esperado, requiere especialista en servidores"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Escalación creada exitosamente. Pendiente de aprobación.",
  "data": {
    "escalation_id": 23
  }
}
```

**Tablas BD Relacionadas:** `escalations`, `tickets`, `employees`

---

#### 4.2.2. Listar Escalaciones

**Endpoint:** `GET /tickets/escalations`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `ticket_id` (int): Filtrar por ticket
- `approved` (boolean): Filtrar por estado de aprobación

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 23,
      "escalation_id": 23,
      "ticket": {
        "id": 1,
        "title": "Error al cargar página de cursos"
      },
      "technician_origin": {
        "id": 12,
        "name": "Pedro Sánchez",
        "speciality": "Desarrollo Web"
      },
      "technician_destiny": {
        "id": 18,
        "name": "Laura Fernández",
        "speciality": "Infraestructura"
      },
      "escalation_reason": "Requiere conocimientos avanzados de infraestructura",
      "observations": "El problema es más complejo de lo esperado",
      "escalation_date": "2025-10-08T11:00:00Z",
      "approved": false
    }
  ]
}
```

**Tablas BD Relacionadas:** `escalations`, `tickets`, `employees`

---

#### 4.2.3. Aprobar Escalación

**Endpoint:** `POST /tickets/escalations/{escalation_id}/approve`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Escalación aprobada. Ticket reasignado."
}
```

**Tablas BD Relacionadas:** `escalations`, `tickets`

---

### 4.3. Seguimiento de Tickets

#### 4.3.1. Agregar Comentario a Ticket

**Endpoint:** `POST /tickets/{ticket_id}/tracking`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "comment": "Se realizó prueba en ambiente de desarrollo. Funciona correctamente.",
  "action_type": "update"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Comentario agregado exitosamente"
}
```

**Tablas BD Relacionadas:** `ticket_trackings`

---

### 4.4. Dashboard de Tickets

#### 4.4.1. Obtener Estadísticas de Tickets

**Endpoint:** `GET /tickets/stats`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `start_date` (date): Fecha inicio
- `end_date` (date): Fecha fin

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "total_tickets": 156,
    "by_status": {
      "abierto": 23,
      "en_proceso": 45,
      "resuelto": 67,
      "cerrado": 21
    },
    "by_priority": {
      "baja": 34,
      "media": 78,
      "alta": 32,
      "critica": 12
    },
    "by_category": {
      "Web": 45,
      "Infraestructura": 34,
      "Seguridad": 23,
      "LMS": 54
    },
    "average_resolution_time_hours": 8.5,
    "pending_escalations": 3
  }
}
```

**Tablas BD Relacionadas:** `tickets`, `escalations`

---