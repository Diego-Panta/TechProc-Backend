# DOCUMENTACIÓN COMPLETA API BACKEND - TECHPROC
## Instituto de Capacitación y Desarrollo Virtual (INCADEV)

**Versión:** 1.0
**Fecha:** 2025
**Base URL:** `https://api.incadev.com/v1`

---

## TABLA DE CONTENIDOS

1. [Autenticación y Sesiones](#1-autenticación-y-sesiones)
2. [Módulo Administrador](#2-módulo-administrador)
3. [Módulo Gestor LMS](#3-módulo-gestor-lms)
4. [Módulo Soporte Técnico](#4-módulo-soporte-técnico)
5. [Módulo Soporte - Seguridad](#5-módulo-soporte---seguridad)
6. [Módulo Soporte - Infraestructura](#6-módulo-soporte---infraestructura)
7. [Módulo Developer Web](#7-módulo-developer-web)
8. [Módulo Analista de Datos](#8-módulo-analista-de-datos)

---

## 1. AUTENTICACIÓN Y SESIONES

### 1.1. Iniciar Sesión

**Endpoint:** `POST /auth/login` <-COMPLETADO SDR->

**Descripción:** Autentica un usuario y crea una sesión activa.

**Body JSON:**
```json
{
  "email": "usuario@ejemplo.com",
  "password": "contraseña_segura"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "first_name": "Juan",
      "last_name": "Pérez",
      "email": "usuario@ejemplo.com",
      "role": "admin",
      "profile_photo": "https://...",
      "status": "active"
    },
    "session": {
      "session_id": 123,
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "expires_at": "2025-10-15T10:30:00Z"
    }
  }
}
```

**Respuesta Error (401):**
```json
{
  "success": false,
  "error": {
    "code": "INVALID_CREDENTIALS",
    "message": "Email o contraseña incorrectos"
  }
}
```

**Tablas BD Relacionadas:** `users`, `active_sessions`

---

### 1.2. Cerrar Sesión

**Endpoint:** `POST /auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "session_id": 123
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente"
}
```

**Tablas BD Relacionadas:** `active_sessions`

---

### 1.3. Registrar Usuario (Solicitud de Registro)

**Endpoint:** `POST /auth/register`

**Descripción:** Crea una solicitud de registro pendiente de aprobación por administrador.

**Body JSON:**
```json
{
  "first_name": "María",
  "last_name": "González",
  "email": "maria.gonzalez@ejemplo.com",
  "password": "contraseña_segura",
  "phone_number": "+51 987654321",
  "role": "lms",
  "reason": "Necesito acceso para gestionar cursos del instituto"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Solicitud de registro enviada. Será revisada por un administrador.",
  "data": {
    "request_id": 45
  }
}
```

**Tablas BD Relacionadas:** `users` (status: "inactive"), registros pendientes

---

### 1.4. Obtener Sesiones Activas de Usuario

**Endpoint:** `GET /auth/sessions/{user_id}`

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
      "session_id": 123,
      "ip_address": "192.168.1.100",
      "device": "Chrome on Windows",
      "start_date": "2025-10-08T08:00:00Z",
      "active": true,
      "blocked": false
    }
  ]
}
```

**Tablas BD Relacionadas:** `active_sessions`

---

## 2. MÓDULO ADMINISTRADOR

### 2.1. Gestión de Usuarios

#### 2.1.1. Listar Todos los Usuarios

**Endpoint:** `GET /admin/users`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (int): Número de página (default: 1)
- `limit` (int): Registros por página (default: 20)
- `role` (string): Filtrar por rol (admin, lms, seg, infra, web, data)
- `status` (string): Filtrar por estado (active, inactive, banned)
- `search` (string): Buscar por nombre o email

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "users": [
      {
        "id": 1,
        "first_name": "Juan",
        "last_name": "Pérez",
        "email": "juan.perez@ejemplo.com",
        "role": "admin",
        "status": "active",
        "last_access": "2025-10-08T10:30:00Z",
        "last_access_ip": "192.168.1.100",
        "created_at": "2025-01-15T00:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "total_records": 95,
      "per_page": 20
    }
  }
}
```

**Tablas BD Relacionadas:** `users`

---

#### 2.1.2. Obtener Detalles de Usuario

**Endpoint:** `GET /admin/users/{user_id}`

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
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "juan.perez@ejemplo.com",
    "phone_number": "+51 987654321",
    "address": "Av. Principal 123",
    "birth_date": "1990-05-15",
    "gender": "male",
    "country": "Perú",
    "role": "admin",
    "status": "active",
    "profile_photo": "https://...",
    "last_access": "2025-10-08T10:30:00Z",
    "last_access_ip": "192.168.1.100",
    "created_at": "2025-01-15T00:00:00Z",
    "updated_at": "2025-10-08T10:30:00Z"
  }
}
```

**Tablas BD Relacionadas:** `users`

---

#### 2.1.3. Crear Usuario

**Endpoint:** `POST /admin/users`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "first_name": "Carlos",
  "last_name": "Ramírez",
  "email": "carlos.ramirez@ejemplo.com",
  "password": "contraseña_segura",
  "phone_number": "+51 912345678",
  "address": "Jr. Los Pinos 456",
  "birth_date": "1988-03-20",
  "gender": "male",
  "country": "Perú",
  "role": "lms",
  "status": "active"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Usuario creado exitosamente",
  "data": {
    "id": 156,
    "email": "carlos.ramirez@ejemplo.com"
  }
}
```

**Tablas BD Relacionadas:** `users`

---

#### 2.1.4. Actualizar Usuario

**Endpoint:** `PUT /admin/users/{user_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "first_name": "Carlos Alberto",
  "last_name": "Ramírez Torres",
  "phone_number": "+51 912345678",
  "address": "Jr. Los Pinos 456 - Dpto 301",
  "status": "active",
  "role": "lms"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Usuario actualizado exitosamente"
}
```

**Tablas BD Relacionadas:** `users`

---

#### 2.1.5. Eliminar Usuario

**Endpoint:** `DELETE /admin/users/{user_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Usuario eliminado exitosamente"
}
```

**Tablas BD Relacionadas:** `users`

---

### 2.2. Solicitudes de Registro Pendientes

#### 2.2.1. Listar Solicitudes Pendientes

**Endpoint:** `GET /admin/registration-requests`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (string): pending, approved, rejected

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 45,
      "first_name": "María",
      "last_name": "González",
      "email": "maria.gonzalez@ejemplo.com",
      "phone_number": "+51 987654321",
      "role": "lms",
      "reason": "Necesito acceso para gestionar cursos del instituto",
      "created_at": "2025-10-07T15:30:00Z",
      "status": "pending"
    }
  ]
}
```

**Tablas BD Relacionadas:** `users` (usuarios con status específico)

---

#### 2.2.2. Aprobar Solicitud de Registro

**Endpoint:** `POST /admin/registration-requests/{request_id}/approve`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "role": "lms",
  "status": "active"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Solicitud aprobada. Usuario activado."
}
```

**Tablas BD Relacionadas:** `users`

---

#### 2.2.3. Rechazar Solicitud de Registro

**Endpoint:** `POST /admin/registration-requests/{request_id}/reject`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "rejection_reason": "Información incompleta"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Solicitud rechazada"
}
```

**Tablas BD Relacionadas:** `users`

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

## 5. MÓDULO SOPORTE - SEGURIDAD

### 5.1. Logs de Seguridad

#### 5.1.1. Listar Logs de Seguridad

**Endpoint:** `GET /security/logs`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (int): Número de página
- `limit` (int): Registros por página
- `user_id` (int): Filtrar por usuario
- `event_type` (string): Tipo de evento
- `start_date` (datetime): Fecha inicio
- `end_date` (datetime): Fecha fin
- `source_ip` (string): IP origen

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "logs": [
      {
        "id": 1,
        "id_security_log": 1,
        "user": {
          "id": 45,
          "name": "Ana Martínez",
          "email": "ana.martinez@ejemplo.com"
        },
        "event_type": "login_success",
        "description": "Inicio de sesión exitoso",
        "source_ip": "192.168.1.100",
        "event_date": "2025-10-08T08:30:00Z"
      },
      {
        "id": 2,
        "id_security_log": 2,
        "user": {
          "id": 56,
          "name": "Carlos Mendoza"
        },
        "event_type": "failed_login_attempt",
        "description": "Intento de inicio de sesión fallido - contraseña incorrecta",
        "source_ip": "203.45.67.89",
        "event_date": "2025-10-08T09:15:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 45,
      "total_records": 892,
      "per_page": 20
    }
  }
}
```

**Tablas BD Relacionadas:** `security_logs`, `users`

---

### 5.2. IPs Bloqueadas

#### 5.2.1. Listar IPs Bloqueadas

**Endpoint:** `GET /security/blocked-ips`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `active` (boolean): true (activas), false (inactivas)

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "id_blocked_ip": 1,
      "ip_address": "203.45.67.89",
      "reason": "Múltiples intentos de inicio de sesión fallidos",
      "block_date": "2025-10-08T09:20:00Z",
      "active": true
    }
  ]
}
```

**Tablas BD Relacionadas:** `blocked_ips`

---

#### 5.2.2. Bloquear IP

**Endpoint:** `POST /security/blocked-ips`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "ip_address": "203.45.67.89",
  "reason": "Múltiples intentos de inicio de sesión fallidos"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "IP bloqueada exitosamente",
  "data": {
    "id": 1,
    "id_blocked_ip": 1
  }
}
```

**Tablas BD Relacionadas:** `blocked_ips`

---

#### 5.2.3. Desbloquear IP

**Endpoint:** `PUT /security/blocked-ips/{blocked_ip_id}/unblock`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "IP desbloqueada exitosamente"
}
```

**Tablas BD Relacionadas:** `blocked_ips`

---

### 5.3. Sesiones Activas

#### 5.3.1. Listar Sesiones Activas

**Endpoint:** `GET /security/sessions`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `user_id` (int): Filtrar por usuario
- `active` (boolean): Filtrar por estado activo
- `blocked` (boolean): Filtrar por estado bloqueado

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "session_id": 1,
      "user": {
        "id": 45,
        "name": "Ana Martínez",
        "email": "ana.martinez@ejemplo.com"
      },
      "ip_address": "192.168.1.100",
      "device": "Chrome on Windows 10",
      "start_date": "2025-10-08T08:30:00Z",
      "active": true,
      "blocked": false
    }
  ]
}
```

**Tablas BD Relacionadas:** `active_sessions`, `users`

---

#### 5.3.2. Terminar Sesión

**Endpoint:** `POST /security/sessions/{session_id}/terminate`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Sesión terminada exitosamente"
}
```

**Tablas BD Relacionadas:** `active_sessions`

---

#### 5.3.3. Bloquear Sesión

**Endpoint:** `POST /security/sessions/{session_id}/block`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Sesión bloqueada exitosamente"
}
```

**Tablas BD Relacionadas:** `active_sessions`

---

### 5.4. Alertas de Seguridad

#### 5.4.1. Listar Alertas de Seguridad

**Endpoint:** `GET /security/alerts`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `severity` (string): low, medium, high
- `status` (string): new, investigating, resolved
- `threat_type` (string): Tipo de amenaza

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "id_security_alert": 1,
      "threat_type": "Brute Force Attack",
      "severity": "high",
      "status": "new",
      "blocked_ip": {
        "id": 1,
        "ip_address": "203.45.67.89"
      },
      "detection_date": "2025-10-08T09:20:00Z"
    }
  ]
}
```

**Tablas BD Relacionadas:** `security_alerts`, `blocked_ips`

---

#### 5.4.2. Crear Alerta de Seguridad

**Endpoint:** `POST /security/alerts`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "threat_type": "Suspicious Activity",
  "severity": "medium",
  "blocked_ip_id": 1,
  "status": "new"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Alerta de seguridad creada exitosamente",
  "data": {
    "id": 2,
    "id_security_alert": 2
  }
}
```

**Tablas BD Relacionadas:** `security_alerts`

---

#### 5.4.3. Actualizar Estado de Alerta

**Endpoint:** `PUT /security/alerts/{alert_id}/status`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "status": "resolved"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Estado de alerta actualizado exitosamente"
}
```

**Tablas BD Relacionadas:** `security_alerts`

---

### 5.5. Incidentes de Seguridad

#### 5.5.1. Listar Incidentes

**Endpoint:** `GET /security/incidents`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (string): open, investigating, resolved, closed
- `responsible_id` (int): Filtrar por responsable

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "id_incident": 1,
      "title": "Intento de acceso no autorizado",
      "status": "investigating",
      "alert": {
        "id": 1,
        "threat_type": "Brute Force Attack"
      },
      "responsible": {
        "id": 12,
        "name": "Pedro Sánchez"
      },
      "report_date": "2025-10-08T09:30:00Z"
    }
  ]
}
```

**Tablas BD Relacionadas:** `incidents`, `security_alerts`, `employees`

---

#### 5.5.2. Crear Incidente

**Endpoint:** `POST /security/incidents`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "alert_id": 1,
  "title": "Intento de acceso no autorizado desde IP bloqueada",
  "responsible_id": 12,
  "status": "open"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Incidente creado exitosamente",
  "data": {
    "id": 2,
    "id_incident": 2
  }
}
```

**Tablas BD Relacionadas:** `incidents`

---

#### 5.5.3. Actualizar Estado de Incidente

**Endpoint:** `PUT /security/incidents/{incident_id}/status`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "status": "resolved"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Estado de incidente actualizado exitosamente"
}
```

**Tablas BD Relacionadas:** `incidents`

---

### 5.6. Configuraciones de Seguridad

#### 5.6.1. Listar Configuraciones

**Endpoint:** `GET /security/configurations`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `modulo` (string): Filtrar por módulo
- `active` (boolean): Filtrar por estado activo

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "id_security_configuration": 1,
      "user_id": null,
      "modulo": "authentication",
      "parameter": "max_login_attempts",
      "value": "5",
      "active": true,
      "created_at": "2025-01-01T00:00:00Z"
    },
    {
      "id": 2,
      "id_security_configuration": 2,
      "modulo": "authentication",
      "parameter": "lockout_duration_minutes",
      "value": "30",
      "active": true,
      "created_at": "2025-01-01T00:00:00Z"
    }
  ]
}
```

**Tablas BD Relacionadas:** `security_configurations`

---

#### 5.6.2. Actualizar Configuración

**Endpoint:** `PUT /security/configurations/{config_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "value": "10",
  "active": true
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Configuración actualizada exitosamente"
}
```

**Tablas BD Relacionadas:** `security_configurations`

---

### 5.7. Dashboard de Seguridad

#### 5.7.1. Obtener Estadísticas de Seguridad

**Endpoint:** `GET /security/stats`

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
    "total_security_logs": 892,
    "failed_login_attempts": 45,
    "blocked_ips_count": 12,
    "active_sessions_count": 234,
    "security_alerts": {
      "total": 23,
      "by_severity": {
        "low": 8,
        "medium": 10,
        "high": 5
      },
      "by_status": {
        "new": 5,
        "investigating": 8,
        "resolved": 10
      }
    },
    "incidents": {
      "total": 8,
      "open": 2,
      "investigating": 3,
      "resolved": 3
    }
  }
}
```

**Tablas BD Relacionadas:** `security_logs`, `blocked_ips`, `active_sessions`, `security_alerts`, `incidents`

---

## 6. MÓDULO SOPORTE - INFRAESTRUCTURA

### 6.1. Gestión de Licencias

#### 6.1.1. Listar Licencias

**Endpoint:** `GET /infrastructure/licenses`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (int): Número de página
- `limit` (int): Registros por página
- `status` (string): active, expired, about_to_expire
- `license_type` (string): Tipo de licencia
- `search` (string): Buscar por nombre de software

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "licenses": [
      {
        "id": 1,
        "id_license": 1,
        "software_name": "Microsoft Office 365",
        "license_type": "Subscription",
        "provider": "Microsoft",
        "purchase_date": "2024-01-15",
        "expiration_date": "2025-01-15",
        "seats_total": 100,
        "seats_used": 87,
        "seats_available": 13,
        "cost_annual": 15000.00,
        "status": "active",
        "responsible": {
          "id": 12,
          "name": "Pedro Sánchez"
        },
        "days_until_expiration": 99,
        "created_at": "2024-01-15T00:00:00Z"
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

**Tablas BD Relacionadas:** `licenses`, `employees`

---

#### 6.1.2. Obtener Detalles de Licencia

**Endpoint:** `GET /infrastructure/licenses/{license_id}`

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
    "id_license": 1,
    "software_name": "Microsoft Office 365",
    "license_key": "XXXXX-XXXXX-XXXXX-XXXXX",
    "license_type": "Subscription",
    "provider": "Microsoft",
    "purchase_date": "2024-01-15",
    "expiration_date": "2025-01-15",
    "seats_total": 100,
    "seats_used": 87,
    "seats_available": 13,
    "cost_annual": 15000.00,
    "status": "active",
    "responsible": {
      "id": 12,
      "employee_id": 12,
      "name": "Pedro Sánchez",
      "email": "pedro.sanchez@incadev.com"
    },
    "notes": "Licencia corporativa para todo el personal administrativo",
    "days_until_expiration": 99,
    "created_at": "2024-01-15T00:00:00Z"
  }
}
```

**Tablas BD Relacionadas:** `licenses`, `employees`

---

#### 6.1.3. Crear Licencia

**Endpoint:** `POST /infrastructure/licenses`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "software_name": "Adobe Creative Cloud",
  "license_key": "XXXXX-XXXXX-XXXXX-XXXXX",
  "license_type": "Subscription",
  "provider": "Adobe",
  "purchase_date": "2025-10-01",
  "expiration_date": "2026-10-01",
  "seats_total": 20,
  "seats_used": 0,
  "cost_annual": 8500.00,
  "status": "active",
  "responsible_id": 12,
  "notes": "Licencia para equipo de diseño gráfico"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Licencia creada exitosamente",
  "data": {
    "id": 46,
    "id_license": 46
  }
}
```

**Tablas BD Relacionadas:** `licenses`

---

#### 6.1.4. Actualizar Licencia

**Endpoint:** `PUT /infrastructure/licenses/{license_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "seats_used": 92,
  "expiration_date": "2026-01-15",
  "cost_annual": 16000.00,
  "status": "active",
  "notes": "Renovada por un año más"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Licencia actualizada exitosamente"
}
```

**Tablas BD Relacionadas:** `licenses`

---

#### 6.1.5. Eliminar Licencia

**Endpoint:** `DELETE /infrastructure/licenses/{license_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Licencia eliminada exitosamente"
}
```

**Tablas BD Relacionadas:** `licenses`

---

### 6.2. Gestión de Software

#### 6.2.1. Listar Software

**Endpoint:** `GET /infrastructure/software`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `category` (string): Filtrar por categoría
- `vendor` (string): Filtrar por proveedor
- `search` (string): Buscar por nombre

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "id_software": 1,
      "software_name": "Microsoft Office 365",
      "version": "2024",
      "category": "Productivity",
      "vendor": "Microsoft",
      "license": {
        "id": 1,
        "license_key": "XXXXX-XXXXX-XXXXX-XXXXX",
        "expiration_date": "2025-01-15"
      },
      "installation_date": "2024-01-20T00:00:00Z",
      "last_update": "2024-09-15T00:00:00Z",
      "created_at": "2024-01-20T00:00:00Z"
    }
  ]
}
```

**Tablas BD Relacionadas:** `softwares`, `licenses`

---

#### 6.2.2. Crear Software

**Endpoint:** `POST /infrastructure/software`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "software_name": "Adobe Photoshop",
  "version": "2025",
  "category": "Design",
  "vendor": "Adobe",
  "license_id": 46,
  "installation_date": "2025-10-08T00:00:00Z"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Software registrado exitosamente",
  "data": {
    "id": 67,
    "id_software": 67
  }
}
```

**Tablas BD Relacionadas:** `softwares`

---

#### 6.2.3. Actualizar Software

**Endpoint:** `PUT /infrastructure/software/{software_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "version": "2025.1",
  "last_update": "2025-10-08T00:00:00Z"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Software actualizado exitosamente"
}
```

**Tablas BD Relacionadas:** `softwares`

---

#### 6.2.4. Eliminar Software

**Endpoint:** `DELETE /infrastructure/software/{software_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Software eliminado exitosamente"
}
```

**Tablas BD Relacionadas:** `softwares`

---

### 6.3. Dashboard de Infraestructura

#### 6.3.1. Obtener Estadísticas de Infraestructura

**Endpoint:** `GET /infrastructure/stats`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "licenses": {
      "total": 45,
      "active": 40,
      "expired": 2,
      "about_to_expire": 3,
      "total_annual_cost": 245000.00,
      "total_seats": 1200,
      "seats_used": 987,
      "seats_available": 213
    },
    "software": {
      "total_installed": 67,
      "by_category": {
        "Productivity": 25,
        "Design": 12,
        "Development": 18,
        "Security": 8,
        "Other": 4
      }
    },
    "upcoming_expirations": [
      {
        "license_id": 5,
        "software_name": "Antivirus Enterprise",
        "expiration_date": "2025-10-25",
        "days_remaining": 17
      }
    ]
  }
}
```

**Tablas BD Relacionadas:** `licenses`, `softwares`

---

## 7. MÓDULO DEVELOPER WEB

### 7.1. Gestión de Noticias

#### 7.1.1. Listar Noticias

**Endpoint:** `GET /web/news`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (int): Número de página
- `limit` (int): Registros por página
- `status` (string): draft, published, archived
- `category` (string): Filtrar por categoría
- `search` (string): Buscar por título

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "news": [
      {
        "id": 1,
        "id_news": 1,
        "title": "Nuevos cursos de programación disponibles",
        "slug": "nuevos-cursos-programacion-2025",
        "summary": "Presentamos nuestra nueva oferta de cursos para el 2025",
        "featured_image": "https://...",
        "author": {
          "id": 23,
          "name": "Roberto Silva"
        },
        "category": "Educación",
        "tags": ["cursos", "programación", "2025"],
        "status": "published",
        "views": 1245,
        "published_date": "2025-10-01T00:00:00Z",
        "created_date": "2025-09-28T00:00:00Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "total_records": 92,
      "per_page": 20
    }
  }
}
```

**Tablas BD Relacionadas:** `news`, `users`

---

#### 7.1.2. Obtener Detalles de Noticia

**Endpoint:** `GET /web/news/{news_id}`

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
    "id_news": 1,
    "title": "Nuevos cursos de programación disponibles",
    "slug": "nuevos-cursos-programacion-2025",
    "summary": "Presentamos nuestra nueva oferta de cursos para el 2025",
    "content": "<p>Contenido completo de la noticia en HTML...</p>",
    "featured_image": "https://...",
    "author": {
      "id": 23,
      "name": "Roberto Silva",
      "email": "roberto.silva@incadev.com"
    },
    "category": "Educación",
    "tags": ["cursos", "programación", "2025"],
    "status": "published",
    "views": 1245,
    "published_date": "2025-10-01T00:00:00Z",
    "created_date": "2025-09-28T00:00:00Z",
    "updated_date": "2025-10-01T00:00:00Z",
    "seo_title": "Nuevos cursos de programación 2025 - INCADEV",
    "seo_description": "Descubre nuestra nueva oferta de cursos de programación para el 2025"
  }
}
```

**Tablas BD Relacionadas:** `news`, `users`

---

#### 7.1.3. Crear Noticia

**Endpoint:** `POST /web/news`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "title": "Webinar gratuito: Introducción a Python",
  "slug": "webinar-gratuito-python",
  "summary": "Únete a nuestro webinar gratuito sobre Python",
  "content": "<p>Contenido completo en HTML...</p>",
  "featured_image": "https://...",
  "author_id": 23,
  "category": "Eventos",
  "tags": ["webinar", "python", "gratuito"],
  "status": "published",
  "published_date": "2025-10-08T00:00:00Z",
  "seo_title": "Webinar gratuito de Python - INCADEV",
  "seo_description": "Aprende Python desde cero en nuestro webinar gratuito"
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Noticia creada exitosamente",
  "data": {
    "id": 93,
    "id_news": 93,
    "slug": "webinar-gratuito-python"
  }
}
```

**Tablas BD Relacionadas:** `news`

---

#### 7.1.4. Actualizar Noticia

**Endpoint:** `PUT /web/news/{news_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "title": "Webinar gratuito: Introducción a Python - ACTUALIZADO",
  "summary": "Únete a nuestro webinar gratuito sobre Python. ¡Cupos limitados!",
  "status": "published"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Noticia actualizada exitosamente"
}
```

**Tablas BD Relacionadas:** `news`

---

#### 7.1.5. Eliminar Noticia

**Endpoint:** `DELETE /web/news/{news_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Noticia eliminada exitosamente"
}
```

**Tablas BD Relacionadas:** `news`

---

### 7.2. Gestión de Anuncios

#### 7.2.1. Listar Anuncios

**Endpoint:** `GET /web/announcements`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (string): draft, published, archived
- `target_page` (string): Página objetivo del anuncio
- `display_type` (string): Tipo de visualización

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "id_announcement": 1,
      "title": "Descuento especial en cursos",
      "content": "¡Aprovecha 30% de descuento en todos los cursos!",
      "image_url": "https://...",
      "display_type": "banner",
      "target_page": "home",
      "link_url": "https://incadev.com/cursos",
      "button_text": "Ver cursos",
      "status": "published",
      "start_date": "2025-10-01T00:00:00Z",
      "end_date": "2025-10-31T23:59:59Z",
      "views": 5432,
      "created_by": {
        "id": 23,
        "name": "Roberto Silva"
      },
      "created_date": "2025-09-25T00:00:00Z"
    }
  ]
}
```

**Tablas BD Relacionadas:** `announcements`, `users`

---

#### 7.2.2. Crear Anuncio

**Endpoint:** `POST /web/announcements`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "title": "Matrícula abierta 2025-II",
  "content": "Ya están abiertas las matrículas para el período 2025-II",
  "image_url": "https://...",
  "display_type": "modal",
  "target_page": "lms",
  "link_url": "https://incadev.com/matricula",
  "button_text": "Matricularme ahora",
  "status": "published",
  "start_date": "2025-11-01T00:00:00Z",
  "end_date": "2025-11-30T23:59:59Z",
  "created_by": 23
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Anuncio creado exitosamente",
  "data": {
    "id": 12,
    "id_announcement": 12
  }
}
```

**Tablas BD Relacionadas:** `announcements`

---

#### 7.2.3. Actualizar Anuncio

**Endpoint:** `PUT /web/announcements/{announcement_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "title": "Matrícula abierta 2025-II - Últimos días",
  "status": "published",
  "end_date": "2025-11-15T23:59:59Z"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Anuncio actualizado exitosamente"
}
```

**Tablas BD Relacionadas:** `announcements`

---

#### 7.2.4. Eliminar Anuncio

**Endpoint:** `DELETE /web/announcements/{announcement_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Anuncio eliminado exitosamente"
}
```

**Tablas BD Relacionadas:** `announcements`

---

### 7.3. Gestión de Alertas

#### 7.3.1. Listar Alertas

**Endpoint:** `GET /web/alerts`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (string): active, inactive
- `type` (string): Tipo de alerta

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "id_alert": 1,
      "message": "Mantenimiento programado el sábado 12 de octubre",
      "type": "warning",
      "status": "active",
      "link_url": "https://incadev.com/mantenimiento",
      "link_text": "Más información",
      "start_date": "2025-10-08T00:00:00Z",
      "end_date": "2025-10-12T23:59:59Z",
      "priority": 1,
      "created_by": {
        "id": 23,
        "name": "Roberto Silva"
      },
      "created_date": "2025-10-07T00:00:00Z"
    }
  ]
}
```

**Tablas BD Relacionadas:** `alerts`, `users`

---

#### 7.3.2. Crear Alerta

**Endpoint:** `POST /web/alerts`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "message": "Nueva funcionalidad disponible en el LMS",
  "type": "info",
  "status": "active",
  "link_url": "https://incadev.com/novedades",
  "link_text": "Ver novedades",
  "start_date": "2025-10-08T00:00:00Z",
  "end_date": "2025-10-15T23:59:59Z",
  "priority": 2,
  "created_by": 23
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Alerta creada exitosamente",
  "data": {
    "id": 8,
    "id_alert": 8
  }
}
```

**Tablas BD Relacionadas:** `alerts`

---

#### 7.3.3. Actualizar Alerta

**Endpoint:** `PUT /web/alerts/{alert_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "status": "inactive"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Alerta actualizada exitosamente"
}
```

**Tablas BD Relacionadas:** `alerts`

---

#### 7.3.4. Eliminar Alerta

**Endpoint:** `DELETE /web/alerts/{alert_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Alerta eliminada exitosamente"
}
```

**Tablas BD Relacionadas:** `alerts`

---

### 7.4. Chatbot - FAQs

#### 7.4.1. Listar FAQs

**Endpoint:** `GET /web/chatbot/faqs`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `category` (string): Filtrar por categoría
- `active` (boolean): Filtrar por estado activo
- `search` (string): Buscar en preguntas

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "id_faq": 1,
      "question": "¿Cómo me inscribo a un curso?",
      "answer": "Para inscribirte a un curso, debes...",
      "category": "Inscripciones",
      "keywords": ["inscripción", "curso", "matrícula"],
      "active": true,
      "usage_count": 234,
      "created_date": "2025-01-15T00:00:00Z",
      "updated_date": "2025-09-20T00:00:00Z"
    }
  ]
}
```

**Tablas BD Relacionadas:** `chatbot_faqs`

---

#### 7.4.2. Crear FAQ

**Endpoint:** `POST /web/chatbot/faqs`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "question": "¿Cuál es el horario de atención?",
  "answer": "Nuestro horario de atención es de lunes a viernes de 8:00 AM a 6:00 PM",
  "category": "Atención al Cliente",
  "keywords": ["horario", "atención", "horarios"],
  "active": true
}
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "FAQ creada exitosamente",
  "data": {
    "id": 45,
    "id_faq": 45
  }
}
```

**Tablas BD Relacionadas:** `chatbot_faqs`

---

#### 7.4.3. Actualizar FAQ

**Endpoint:** `PUT /web/chatbot/faqs/{faq_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "answer": "Nuestro horario de atención es de lunes a viernes de 8:00 AM a 7:00 PM y sábados de 9:00 AM a 1:00 PM",
  "active": true
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "FAQ actualizada exitosamente"
}
```

**Tablas BD Relacionadas:** `chatbot_faqs`

---

#### 7.4.4. Eliminar FAQ

**Endpoint:** `DELETE /web/chatbot/faqs/{faq_id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "FAQ eliminada exitosamente"
}
```

**Tablas BD Relacionadas:** `chatbot_faqs`

---

### 7.5. Formularios de Contacto

#### 7.5.1. Listar Formularios de Contacto

**Endpoint:** `GET /web/contact-forms`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (string): pending, in_progress, responded
- `assigned_to` (int): Filtrar por empleado asignado
- `form_type` (string): Tipo de formulario

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "id_contact": 1,
      "full_name": "María López",
      "email": "maria.lopez@ejemplo.com",
      "phone": "+51 987654321",
      "company": "Tech Solutions SAC",
      "subject": "Consulta sobre cursos corporativos",
      "message": "Me gustaría información sobre...",
      "form_type": "sales",
      "status": "pending",
      "assigned_to": null,
      "response": null,
      "response_date": null,
      "submission_date": "2025-10-08T10:30:00Z"
    }
  ]
}
```

**Tablas BD Relacionadas:** `contact_forms`, `employees`

---

#### 7.5.2. Responder Formulario de Contacto

**Endpoint:** `POST /web/contact-forms/{contact_id}/respond`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "assigned_to": 12,
  "response": "Estimada María, gracias por su interés. Le enviamos la información solicitada...",
  "status": "responded"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Respuesta enviada exitosamente"
}
```

**Tablas BD Relacionadas:** `contact_forms`

---

### 7.6. Dashboard Web

#### 7.6.1. Obtener Estadísticas Web

**Endpoint:** `GET /web/stats`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "news": {
      "total": 92,
      "published": 78,
      "draft": 12,
      "archived": 2,
      "total_views": 45678
    },
    "announcements": {
      "total": 12,
      "active": 5,
      "total_views": 12345
    },
    "alerts": {
      "total": 8,
      "active": 3
    },
    "chatbot": {
      "total_faqs": 45,
      "active_faqs": 42,
      "total_conversations": 567,
      "resolved_conversations": 489
    },
    "contact_forms": {
      "total": 234,
      "pending": 23,
      "in_progress": 45,
      "responded": 166
    }
  }
}
```

**Tablas BD Relacionadas:** `news`, `announcements`, `alerts`, `chatbot_faqs`, `chatbot_conversations`, `contact_forms`

---

## 8. MÓDULO ANALISTA DE DATOS

### 8.1. Reportes de Estudiantes

#### 8.1.1. Obtener Estadísticas de Estudiantes

**Endpoint:** `GET /analytics/students/stats`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `start_date` (date): Fecha inicio
- `end_date` (date): Fecha fin
- `academic_period_id` (int): Período académico
- `company_id` (int): Filtrar por empresa

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "total_students": 235,
    "active_students": 198,
    "inactive_students": 37,
    "by_company": [
      {
        "company_id": 3,
        "company_name": "Tech Solutions SAC",
        "student_count": 45
      }
    ],
    "enrollment_trend": [
      {
        "period": "2025-I",
        "enrolled": 180
      },
      {
        "period": "2025-II",
        "enrolled": 198
      }
    ],
    "by_status": {
      "active": 198,
      "inactive": 37
    }
  }
}
```

**Tablas BD Relacionadas:** `students`, `companies`, `enrollments`

---

### 8.2. Reportes de Cursos

#### 8.2.1. Obtener Estadísticas de Cursos

**Endpoint:** `GET /analytics/courses/stats`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `category_id` (int): Filtrar por categoría
- `level` (string): basic, intermediate, advanced

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "total_courses": 45,
    "active_courses": 40,
    "inactive_courses": 5,
    "by_level": {
      "basic": 18,
      "intermediate": 15,
      "advanced": 12
    },
    "by_category": [
      {
        "category_id": 1,
        "category_name": "Programación",
        "course_count": 20
      }
    ],
    "most_enrolled": [
      {
        "course_id": 1,
        "course_title": "Introducción a Python",
        "enrollments": 156
      }
    ],
    "bestsellers": [
      {
        "course_id": 5,
        "course_title": "JavaScript Avanzado",
        "revenue": 45890.50
      }
    ]
  }
}
```

**Tablas BD Relacionadas:** `courses`, `categories`, `enrollments`, `enrollment_details`

---

### 8.3. Reportes de Asistencia

#### 8.3.1. Obtener Estadísticas de Asistencia

**Endpoint:** `GET /analytics/attendance/stats`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `group_id` (int): Filtrar por grupo
- `start_date` (date): Fecha inicio
- `end_date` (date): Fecha fin

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "total_classes": 120,
    "total_attendances_recorded": 2345,
    "average_attendance_rate": 87.5,
    "by_group": [
      {
        "group_id": 12,
        "group_name": "Python Básico - Grupo A",
        "total_classes": 12,
        "attendance_rate": 92.3
      }
    ],
    "attendance_trend": [
      {
        "date": "2025-10-01",
        "attendance_count": 156,
        "attendance_rate": 88.2
      }
    ]
  }
}
```

**Tablas BD Relacionadas:** `attendances`, `classes`, `groups`, `group_participants`

---

### 8.4. Reportes de Calificaciones

#### 8.4.1. Obtener Estadísticas de Calificaciones

**Endpoint:** `GET /analytics/grades/stats`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `group_id` (int): Filtrar por grupo
- `course_id` (int): Filtrar por curso
- `academic_period_id` (int): Filtrar por período académico

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "total_grades_recorded": 1567,
    "average_grade": 16.8,
    "passing_rate": 85.3,
    "by_group": [
      {
        "group_id": 12,
        "group_name": "Python Básico - Grupo A",
        "average_grade": 17.2,
        "passing_rate": 91.5
      }
    ],
    "grade_distribution": {
      "0-10": 45,
      "11-13": 123,
      "14-16": 567,
      "17-18": 456,
      "19-20": 376
    },
    "top_performers": [
      {
        "user_id": 45,
        "student_name": "Ana Martínez",
        "average_grade": 19.5
      }
    ]
  }
}
```

**Tablas BD Relacionadas:** `grade_records`, `final_grades`, `groups`, `users`

---

### 8.5. Reportes Financieros

#### 8.5.1. Obtener Estadísticas Financieras

**Endpoint:** `GET /analytics/financial/stats`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `start_date` (date): Fecha inicio
- `end_date` (date): Fecha fin
- `revenue_source_id` (int): Filtrar por fuente de ingresos

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "total_revenue": 567890.50,
    "total_expenses": 234567.80,
    "net_income": 333322.70,
    "by_revenue_source": [
      {
        "source_id": 1,
        "source_name": "Matrículas",
        "amount": 456789.50
      },
      {
        "source_id": 2,
        "source_name": "Cursos Corporativos",
        "amount": 111101.00
      }
    ],
    "revenue_trend": [
      {
        "month": "2025-08",
        "revenue": 45678.90
      },
      {
        "month": "2025-09",
        "revenue": 56789.50
      },
      {
        "month": "2025-10",
        "revenue": 67890.20
      }
    ],
    "pending_payments": {
      "count": 45,
      "total_amount": 23456.70
    }
  }
}
```

**Tablas BD Relacionadas:** `invoices`, `payments`, `revenue_sources`, `financial_transactions`

---

### 8.6. Reportes de Tickets

#### 8.6.1. Obtener Análisis de Tickets

**Endpoint:** `GET /analytics/tickets/stats`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `start_date` (date): Fecha inicio
- `end_date` (date): Fecha fin
- `category` (string): Filtrar por categoría

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
    "resolution_metrics": {
      "average_resolution_time_hours": 8.5,
      "median_resolution_time_hours": 6.0,
      "first_response_time_hours": 2.3
    },
    "technician_performance": [
      {
        "technician_id": 12,
        "technician_name": "Pedro Sánchez",
        "tickets_resolved": 45,
        "average_resolution_time_hours": 7.2
      }
    ],
    "escalation_rate": 15.3
  }
}
```

**Tablas BD Relacionadas:** `tickets`, `employees`, `escalations`, `ticket_trackings`

---

### 8.7. Reportes de Seguridad

#### 8.7.1. Obtener Análisis de Seguridad

**Endpoint:** `GET /analytics/security/stats`

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
    "total_security_events": 892,
    "by_event_type": {
      "login_success": 567,
      "login_failure": 89,
      "password_change": 45,
      "suspicious_activity": 12,
      "unauthorized_access_attempt": 8
    },
    "blocked_ips": {
      "total": 12,
      "active": 8,
      "this_period": 3
    },
    "security_alerts": {
      "total": 23,
      "by_severity": {
        "low": 8,
        "medium": 10,
        "high": 5
      }
    },
    "incidents": {
      "total": 8,
      "resolved": 5,
      "in_progress": 3
    },
    "failed_login_rate": 13.5,
    "top_threat_ips": [
      {
        "ip_address": "203.45.67.89",
        "attempt_count": 45,
        "blocked": true
      }
    ]
  }
}
```

**Tablas BD Relacionadas:** `security_logs`, `blocked_ips`, `security_alerts`, `incidents`

---

### 8.8. Dashboard General de Analítica

#### 8.8.1. Obtener Dashboard Completo

**Endpoint:** `GET /analytics/dashboard`

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
    "students": {
      "total": 235,
      "active": 198,
      "growth_rate": 12.5
    },
    "courses": {
      "total": 45,
      "active": 40,
      "total_enrollments": 567
    },
    "attendance": {
      "average_rate": 87.5,
      "trend": "up"
    },
    "performance": {
      "average_grade": 16.8,
      "passing_rate": 85.3
    },
    "revenue": {
      "total": 567890.50,
      "growth_rate": 8.3
    },
    "support": {
      "open_tickets": 68,
      "average_resolution_time_hours": 8.5
    },
    "security": {
      "active_alerts": 5,
      "blocked_ips": 8
    },
    "recent_activities": [
      {
        "type": "enrollment",
        "description": "15 nuevas matrículas hoy",
        "timestamp": "2025-10-08T14:30:00Z"
      }
    ]
  }
}
```

**Tablas BD Relacionadas:** Múltiples tablas del sistema

---

### 8.9. Exportar Reportes

#### 8.9.1. Exportar Reporte en Excel/PDF

**Endpoint:** `POST /analytics/export`

**Headers:**
```
Authorization: Bearer {token}
```

**Body JSON:**
```json
{
  "report_type": "students",
  "format": "excel",
  "filters": {
    "start_date": "2025-01-01",
    "end_date": "2025-10-08",
    "academic_period_id": 3
  },
  "include_charts": true
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Reporte generado exitosamente",
  "data": {
    "download_url": "https://api.incadev.com/downloads/reports/students_2025-10-08.xlsx",
    "expires_at": "2025-10-08T18:00:00Z"
  }
}
```

**Tablas BD Relacionadas:** Según tipo de reporte

---

## NOTAS ADICIONALES

### Códigos de Error Comunes

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Descripción del error",
    "details": {}
  }
}
```

**Códigos de Error:**
- `UNAUTHORIZED` (401): No autenticado
- `FORBIDDEN` (403): Sin permisos
- `NOT_FOUND` (404): Recurso no encontrado
- `VALIDATION_ERROR` (422): Errores de validación
- `INTERNAL_ERROR` (500): Error interno del servidor

### Paginación

Todas las respuestas paginadas incluyen:
```json
{
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_records": 95,
    "per_page": 20
  }
}
```

### Autenticación

Todas las peticiones (excepto login y registro) requieren header:
```
Authorization: Bearer {token}
```

### Fechas y Timestamps

Todos los timestamps están en formato ISO 8601 UTC:
```
2025-10-08T10:30:00Z
```

---

**FIN DE LA DOCUMENTACIÓN**
