# TechProc Backend - Especificación del Proyecto

## Información General

**Proyecto:** TechProc Backend API  
**Versión:** 1.0.0  
**Framework:** Laravel 11.x  
**Tipo:** API RESTful  
**Fecha de Implementación:** Octubre 2025  
**Rama:** miguel-dev

---

## Descripción

TechProc Backend es una API RESTful empresarial desarrollada en Laravel que proporciona servicios backend para la gestión integral de una plataforma educativa y de soporte técnico. El sistema está organizado mediante una arquitectura basada en dominios (Domain-Driven Design), donde cada módulo representa un contexto delimitado del negocio.

---

## Arquitectura del Sistema

### Estructura de Dominios

El proyecto implementa una arquitectura modular basada en dominios ubicados en `app/Domains/`:

```
app/Domains/
├── Administrator/          # Gestión administrativa (usuarios, empleados, departamentos)
├── AuthenticationSessions/ # Gestión de sesiones activas
├── DataAnalyst/           # Análisis de datos financieros y transacciones
├── DeveloperWeb/          # Gestión de contenido web (noticias, anuncios, chatbot)
├── Lms/                   # Sistema de Gestión de Aprendizaje (LMS) ✅
├── SupportInfrastructure/ # Soporte de infraestructura
├── SupportSecurity/       # Soporte de seguridad
└── SupportTechnical/      # Sistema de tickets de soporte técnico ✅
```

### Patrón de Organización por Dominio

Cada dominio implementa la siguiente estructura:

```
Dominio/
├── Controllers/          # Controladores del dominio
│   ├── {Resource}Controller.php
│   └── ...
├── Requests/            # Validaciones FormRequest
│   ├── Create{Resource}Request.php
│   ├── Update{Resource}Request.php
│   └── ...
├── Resources/           # Transformadores de respuestas JSON
│   ├── {Resource}Resource.php
│   ├── {Resource}Collection.php
│   └── ...
├── Models/              # Modelos Eloquent
│   ├── {Model}.php
│   └── ...
└── routes.php          # Definición de rutas del dominio
```

---

## Módulos Implementados

### 1. Módulo LMS (Sistema de Gestión de Aprendizaje)

**Ubicación:** `app/Domains/Lms/`

#### Características Implementadas

##### 1.1 Gestión de Cursos
- **CRUD Completo de Cursos**
  - Listar cursos con filtros avanzados (nivel, estado, categoría, búsqueda)
  - Obtener detalles completos de un curso (incluye categorías, instructores, contenidos)
  - Crear cursos con asignación de categorías e instructores
  - Actualizar información de cursos
  - Eliminar cursos
  - Paginación configurable

##### 1.2 Gestión de Estudiantes
- **CRUD Completo de Estudiantes**
  - Listar estudiantes con filtros (estado, empresa, búsqueda)
  - Obtener perfil detallado de estudiante con sus matrículas
  - Crear estudiantes vinculados a usuarios y empresas
  - Actualizar información de estudiantes
  - Eliminar estudiantes
  - Relación con empresas y usuarios

##### 1.3 Gestión de Instructores
- **CRUD de Instructores**
  - Listar instructores con filtros (estado, área de expertise)
  - Crear instructores con biografía y especialización
  - Actualizar información de instructores
  - Contador de cursos asignados
  - Paginación de resultados

##### 1.4 Categorías de Cursos
- **Gestión de Categorías**
  - Listar todas las categorías
  - Contador de cursos por categoría
  - Información de imágenes y slugs

##### 1.5 Matrículas (Enrollments)
- **Sistema de Matrículas**
  - Crear matrículas con múltiples cursos (enrollment_details)
  - Listar matrículas con filtros (estudiante, período académico, estado)
  - Vinculación con períodos académicos
  - Gestión de ofertas de cursos (course_offerings)

#### Controladores Implementados

```php
app/Domains/Lms/Controllers/
├── CourseController.php        # CRUD de cursos
├── StudentController.php       # CRUD de estudiantes
├── InstructorController.php    # CRUD de instructores
├── CategoryController.php      # Listado de categorías
└── EnrollmentController.php    # Gestión de matrículas
```

#### Requests de Validación

```php
app/Domains/Lms/Requests/
├── CreateCourseRequest.php
├── UpdateCourseRequest.php
├── CreateStudentRequest.php
├── UpdateStudentRequest.php
├── CreateInstructorRequest.php
├── UpdateInstructorRequest.php
└── CreateEnrollmentRequest.php
```

#### Resources (Transformadores JSON)

```php
app/Domains/Lms/Resources/
├── CourseResource.php
├── CourseDetailResource.php
├── CourseCollection.php
├── StudentResource.php
├── StudentDetailResource.php
├── InstructorResource.php
├── CategoryResource.php
├── EnrollmentResource.php
└── EnrollmentCollection.php
```

#### Modelos Relacionados

```php
app/Domains/Lms/Models/
├── AcademicPeriod.php      # Períodos académicos
├── Attempt.php             # Intentos de evaluaciones
├── Attendance.php          # Asistencia
├── Category.php            # Categorías de cursos
├── Certificate.php         # Certificados
├── ClassModel.php          # Clases
├── Company.php             # Empresas
├── Course.php              # Cursos ✅
├── CourseCategory.php      # Relación curso-categoría
├── CourseContent.php       # Contenidos del curso
├── CourseInstructor.php    # Relación curso-instructor
├── CourseOffering.php      # Ofertas de cursos
├── Enrollment.php          # Matrículas ✅
├── EnrollmentDetail.php    # Detalles de matrícula
├── Exam.php                # Exámenes
├── Grade.php               # Calificaciones
├── Group.php               # Grupos
├── Instructor.php          # Instructores ✅
├── Material.php            # Materiales
├── Question.php            # Preguntas
├── Schedule.php            # Horarios
├── Student.php             # Estudiantes ✅
└── ...
```

#### Endpoints API

**Prefijo:** `/api/lms`  
**Autenticación:** Bearer Token JWT (implementado en módulo AuthenticationSessions)  
**Middleware:** `auth:api` (será configurado por el equipo de autenticación)

##### Cursos
- `GET /lms/courses` - Listar cursos
- `GET /lms/courses/{course_id}` - Detalles de curso
- `POST /lms/courses` - Crear curso
- `PUT /lms/courses/{course_id}` - Actualizar curso
- `DELETE /lms/courses/{course_id}` - Eliminar curso

##### Estudiantes
- `GET /lms/students` - Listar estudiantes
- `GET /lms/students/{student_id}` - Detalles de estudiante
- `POST /lms/students` - Crear estudiante
- `PUT /lms/students/{student_id}` - Actualizar estudiante
- `DELETE /lms/students/{student_id}` - Eliminar estudiante

##### Instructores
- `GET /lms/instructors` - Listar instructores
- `POST /lms/instructors` - Crear instructor
- `PUT /lms/instructors/{instructor_id}` - Actualizar instructor

##### Categorías
- `GET /lms/categories` - Listar categorías

##### Matrículas
- `GET /lms/enrollments` - Listar matrículas
- `POST /lms/enrollments` - Crear matrícula

---

### 2. Módulo Soporte Técnico

**Ubicación:** `app/Domains/SupportTechnical/`

#### Características Implementadas

##### 2.1 Gestión de Tickets
- **Sistema Completo de Tickets**
  - Listar tickets con filtros avanzados (estado, prioridad, categoría, técnico, búsqueda)
  - Obtener detalles de ticket con historial de seguimiento completo
  - Crear tickets
  - Asignar tickets a técnicos (tomar ticket)
  - Actualizar estado de tickets
  - Resolver tickets con notas de resolución
  - Cerrar tickets con notas de cierre
  - Dashboard con estadísticas completas

##### 2.2 Escalaciones de Tickets
- **Sistema de Escalaciones**
  - Crear escalaciones entre técnicos
  - Listar escalaciones con filtros
  - Aprobar escalaciones (reasignación automática)
  - Registro de razones y observaciones

##### 2.3 Seguimiento de Tickets
- **Sistema de Tracking**
  - Agregar comentarios y actualizaciones
  - Historial completo de acciones
  - Tipos de acción (assignment, update, resolution, closing, escalation)
  - Marcas de tiempo automáticas

##### 2.4 Dashboard y Estadísticas
- **Análisis de Datos**
  - Total de tickets
  - Distribución por estado
  - Distribución por prioridad
  - Distribución por categoría
  - Tiempo promedio de resolución
  - Escalaciones pendientes
  - Filtrado por rango de fechas

#### Controladores Implementados

```php
app/Domains/SupportTechnical/Controllers/
├── TicketController.php           # CRUD y gestión de tickets
├── TicketEscalationController.php # Escalaciones
└── TicketTrackingController.php   # Seguimiento
```

#### Requests de Validación

```php
app/Domains/SupportTechnical/Requests/
├── CreateTicketRequest.php
├── TakeTicketRequest.php
├── UpdateTicketStatusRequest.php
├── ResolveTicketRequest.php
├── CloseTicketRequest.php
├── EscalateTicketRequest.php
├── ApproveEscalationRequest.php
└── AddTrackingCommentRequest.php
```

#### Resources (Transformadores JSON)

```php
app/Domains/SupportTechnical/Resources/
├── TicketResource.php
├── TicketDetailResource.php
├── TicketCollection.php
├── EscalationResource.php
├── EscalationCollection.php
└── TicketTrackingResource.php
```

#### Modelos Relacionados

```php
app/Domains/SupportTechnical/Models/
├── Ticket.php          # Tickets ✅
├── Escalation.php      # Escalaciones ✅
└── TicketTracking.php  # Seguimiento ✅
```

#### Endpoints API

**Prefijo:** `/api/tickets`  
**Autenticación:** Bearer Token JWT (implementado en módulo AuthenticationSessions)  
**Middleware:** `auth:api` (será configurado por el equipo de autenticación)

##### Tickets
- `GET /tickets` - Listar todos los tickets
- `GET /tickets/{ticket_id}` - Detalles de ticket
- `POST /tickets` - Crear ticket
- `POST /tickets/{ticket_id}/take` - Tomar/asignar ticket
- `PUT /tickets/{ticket_id}/status` - Actualizar estado
- `POST /tickets/{ticket_id}/resolve` - Resolver ticket
- `POST /tickets/{ticket_id}/close` - Cerrar ticket
- `GET /tickets/stats` - Estadísticas y dashboard

##### Escalaciones
- `POST /tickets/{ticket_id}/escalate` - Escalar ticket
- `GET /tickets/escalations` - Listar escalaciones
- `POST /tickets/escalations/{escalation_id}/approve` - Aprobar escalación

##### Seguimiento
- `POST /tickets/{ticket_id}/tracking` - Agregar comentario

---

## Stack Tecnológico

### Backend
- **Framework:** Laravel 11.x
- **PHP:** 8.2+
- **Base de Datos:** MySQL/PostgreSQL
- **Autenticación:** Laravel Sanctum (Bearer Token)
- **ORM:** Eloquent

### Dependencias Principales
```json
{
  "php": "^8.2",
  "laravel/framework": "^11.0",
  "tymon/jwt-auth": "^2.0"
}
```

**Nota:** La autenticación JWT será implementada por otro desarrollador en el módulo `AuthenticationSessions`.

---

## Características Técnicas Implementadas

### 1. Autenticación y Seguridad
- **Sistema de Autenticación JWT Personalizado**
  - Sistema implementado en módulo `AuthenticationSessions` (por otro desarrollador)
  - Todos los endpoints protegidos con middleware de autenticación personalizado
  - Tokens JWT Bearer en headers de autorización
  - Gestión de sesiones activas en tabla `active_sessions`
  - Validación de tokens contra base de datos
  - **Nota:** El middleware de autenticación será agregado posteriormente por el equipo de autenticación

### 2. Validación de Datos
- **FormRequest Classes**
  - Validación centralizada en clases Request
  - Mensajes de error personalizados
  - Autorización a nivel de request
  - Validación automática antes de llegar al controlador

### 3. Transformación de Respuestas
- **API Resources (Transformers)**
  - Formato consistente de respuestas JSON
  - Collections para listados paginados
  - Resources individuales para detalles
  - Inclusión condicional de relaciones

### 4. Paginación
- **Laravel Pagination**
  - Paginación automática con Eloquent
  - Parámetros configurables: `page`, `limit`
  - Valor por defecto: 20 registros por página
  - Metadata completa: total, páginas, registros por página

### 5. Filtrado y Búsqueda
- **Query Parameters**
  - Filtros múltiples por diversos criterios
  - Búsqueda full-text en campos relevantes
  - Filtros por relaciones (categoría, empresa, técnico)
  - Filtros por estado y prioridad

### 6. Relaciones Eloquent
- **Eager Loading**
  - Carga optimizada de relaciones con `with()`
  - Prevención de problema N+1
  - Contadores con `withCount()`
  - Relaciones anidadas cargadas eficientemente

### 7. Manejo de Errores
- **Respuestas Consistentes**
  - Formato JSON estándar para errores
  - Códigos HTTP apropiados (404, 422, 500)
  - Mensajes descriptivos
  - Validación de existencia de recursos

### 8. Responses API Estándar
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    // Datos de respuesta
  }
}
```

### 9. Timestamps Automáticos
- `created_at` y `updated_at` en todos los modelos
- Campos de fecha personalizados (creation_date, assignment_date, etc.)
- Casteo automático a objetos DateTime

### 10. Soft Deletes (Preparado)
- Estructura preparada para eliminación lógica
- Posibilidad de restaurar registros eliminados

---

## Base de Datos

### Tablas Principales - Módulo LMS

#### courses
```sql
- id (PK)
- course_id (unique)
- title
- name
- description
- level (basic/intermediate/advanced)
- course_image
- video_url
- duration (decimal)
- sessions (int)
- selling_price (decimal)
- discount_price (decimal)
- prerequisites (text)
- certificate_name (boolean)
- certificate_issuer
- bestseller (boolean)
- featured (boolean)
- highest_rated (boolean)
- status (boolean)
- created_at, updated_at
```

#### students
```sql
- id (PK)
- student_id (unique)
- user_id (FK -> users)
- company_id (FK -> companies)
- document_number
- first_name
- last_name
- email
- phone
- status (active/inactive)
- created_at, updated_at
```

#### instructors
```sql
- id (PK)
- instructor_id (unique)
- user_id (FK -> users)
- bio (text)
- expertise_area
- status (active/inactive)
- created_at, updated_at
```

#### enrollments
```sql
- id (PK)
- enrollment_id (unique)
- student_id (FK -> students)
- academic_period_id (FK -> academic_periods)
- enrollment_type
- enrollment_date
- status (active/inactive)
- created_at, updated_at
```

#### enrollment_details
```sql
- id (PK)
- enrollment_detail_id (unique)
- enrollment_id (FK -> enrollments)
- course_offering_id (FK -> course_offerings)
- created_at, updated_at
```

### Tablas Principales - Módulo Soporte Técnico

#### tickets
```sql
- id (PK)
- ticket_id (unique)
- assigned_technician (FK -> employees)
- user_id (FK -> users)
- title
- description (text)
- priority (baja/media/alta/critica)
- status (abierto/en_proceso/resuelto/cerrado)
- category
- creation_date
- assignment_date
- resolution_date
- close_date
- notes (text)
- created_at, updated_at
```

#### escalations
```sql
- id (PK)
- escalation_id (unique)
- ticket_id (FK -> tickets)
- technician_origin_id (FK -> employees)
- technician_destiny_id (FK -> employees)
- escalation_reason
- observations (text)
- escalation_date
- approved (boolean)
- created_at, updated_at
```

#### ticket_trackings
```sql
- id (PK)
- ticket_tracking_id (unique)
- ticket_id (FK -> tickets)
- comment (text)
- action_type (assignment/update/resolution/closing/escalation)
- follow_up_date
- created_at, updated_at
```

---

## Configuración del Proyecto

### Providers Registrados

```php
// config/app.php
'providers' => [
    // ...
    App\Providers\AppServiceProvider::class,
    App\Providers\DomainServiceProvider::class,
];
```

### Domain Service Provider

El `DomainServiceProvider` carga automáticamente las rutas de todos los dominios:

```php
// app/Providers/DomainServiceProvider.php
public function boot()
{
    $modules = [
        'Administrator',
        'DataAnalyst',
        'DeveloperWeb',
        'Lms',                    // ✅ Implementado
        'SupportInfrastructure',
        'SupportSecurity',
        'SupportTechnical',       // ✅ Implementado
        'AuthenticationSessions',
    ];

    foreach ($modules as $module) {
        $path = base_path("app/Domains/{$module}/routes.php");
        if (file_exists($path)) {
            require $path;
        }
    }
}
```

### Configuración de Autenticación

El sistema de autenticación JWT personalizado será configurado por el equipo responsable del módulo `AuthenticationSessions`. 

Los módulos LMS y SupportTechnical están preparados para recibir el middleware de autenticación una vez esté implementado.

**Estructura esperada:**
- Middleware personalizado para validar tokens JWT
- Validación contra tabla `active_sessions`
- Inyección de usuario autenticado en el request

---

## Testing

### Estructura de Tests
```
tests/
├── Feature/
│   ├── Lms/
│   │   ├── CourseTest.php
│   │   ├── StudentTest.php
│   │   ├── InstructorTest.php
│   │   └── EnrollmentTest.php
│   └── SupportTechnical/
│       ├── TicketTest.php
│       ├── EscalationTest.php
│       └── TicketTrackingTest.php
└── Unit/
    ├── Models/
    │   ├── CourseTest.php
    │   ├── TicketTest.php
    │   └── ...
    └── Requests/
        └── ...
```

---

## Flujo de Trabajo Implementado

### Flujo de Tickets de Soporte

1. **Creación del Ticket**
   - Usuario crea ticket con título, descripción, prioridad y categoría
   - Estado inicial: "abierto"
   - Se registra creation_date automáticamente

2. **Asignación del Ticket**
   - Técnico "toma" el ticket (endpoint `/take`)
   - Se asigna assigned_technician
   - Se registra assignment_date
   - Se crea tracking automático con action_type "assignment"

3. **Trabajo en el Ticket**
   - Técnico puede agregar comentarios (tracking)
   - Actualizar estado a "en_proceso"
   - Cada acción genera un registro de tracking

4. **Escalación (Opcional)**
   - Si el técnico no puede resolver, crea una escalación
   - Especifica técnico destino y razón
   - Estado: pendiente de aprobación
   - Al aprobar, el ticket se reasigna automáticamente

5. **Resolución**
   - Técnico marca ticket como "resuelto"
   - Agrega notas de resolución
   - Se registra resolution_date
   - Se crea tracking con action_type "resolution"

6. **Cierre**
   - Usuario o administrador cierra el ticket
   - Estado cambia a "cerrado"
   - Se registra close_date
   - Se crea tracking con action_type "closing"

### Flujo de Matrículas LMS

1. **Creación de Matrícula**
   - Se crea registro en tabla enrollments
   - Se especifica estudiante y período académico
   - Se pueden agregar múltiples cursos (enrollment_details)
   - Cada curso se vincula mediante course_offering_id

2. **Gestión de Curso**
   - Curso tiene categorías e instructores asignados
   - Puede tener contenidos (videos, materiales)
   - Estados: activo/inactivo
   - Niveles: básico, intermedio, avanzado

---

## Documentación API

### Formato de Request

**Headers Requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "field": "value"
}
```

### Formato de Response Exitosa

```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    // Datos solicitados
  }
}
```

### Formato de Response con Paginación

```json
{
  "success": true,
  "data": {
    "items": [ /* array de recursos */ ],
    "pagination": {
      "current_page": 1,
      "total_pages": 10,
      "total_records": 200,
      "per_page": 20
    }
  }
}
```

### Formato de Response de Error

```json
{
  "success": false,
  "message": "Mensaje de error descriptivo",
  "errors": {
    "field": ["Mensaje de validación"]
  }
}
```

### Códigos HTTP Utilizados

- `200 OK` - Operación exitosa
- `201 Created` - Recurso creado exitosamente
- `400 Bad Request` - Request inválido
- `401 Unauthorized` - No autenticado
- `403 Forbidden` - No autorizado
- `404 Not Found` - Recurso no encontrado
- `422 Unprocessable Entity` - Error de validación
- `500 Internal Server Error` - Error del servidor

---

## Buenas Prácticas Implementadas

### 1. Código Limpio
- Nombres descriptivos de variables y métodos
- Métodos pequeños con responsabilidad única
- Comentarios solo donde es necesario
- PSR-12 coding style

### 2. Principios SOLID
- Single Responsibility: Un controlador por recurso
- Dependency Injection en constructores
- Interface segregation en contratos

### 3. Seguridad
- Validación de entrada de datos
- Protección contra SQL Injection (Eloquent)
- Autenticación en todos los endpoints
- Sanitización automática de datos

### 4. Performance
- Eager loading de relaciones
- Índices en campos de búsqueda frecuente
- Paginación para listados grandes
- Cache preparado para implementar

### 5. Mantenibilidad
- Estructura modular por dominios
- Separación de responsabilidades
- Fácil localización de código
- Escalable para nuevos módulos

---

## Comandos Útiles

### Desarrollo
```bash
# Iniciar servidor de desarrollo
php artisan serve

# Ejecutar migraciones
php artisan migrate

# Crear seeder
php artisan db:seed

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver rutas
php artisan route:list
```

### Testing
```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests específicos
php artisan test --filter=CourseTest

# Con coverage
php artisan test --coverage
```

---

## Próximas Mejoras Sugeridas

### Funcionalidades
- [ ] Sistema de notificaciones en tiempo real
- [ ] Webhooks para eventos importantes
- [ ] Exportación de reportes (PDF, Excel)
- [ ] Integración con servicios de email
- [ ] Sistema de permisos y roles granular
- [ ] Versionado de API (v2, v3)
- [ ] Rate limiting por usuario/IP
- [ ] Logs de auditoría completos

### Técnicas
- [ ] Implementar caché con Redis
- [ ] Jobs y colas para procesos pesados
- [ ] Implementar eventos y listeners
- [ ] GraphQL endpoint alternativo
- [ ] Documentación Swagger/OpenAPI
- [ ] CI/CD con GitHub Actions
- [ ] Docker containerization
- [ ] Monitoring con Laravel Telescope

---

## Equipo de Desarrollo

**Desarrollador Backend:** Miguel  
**Rama de Desarrollo:** miguel-dev  
**Repositorio:** TechProc-Backend  
**Owner:** Diego-Panta

---

## Conclusión

El proyecto TechProc Backend ha sido implementado exitosamente con una arquitectura modular, escalable y mantenible. Los módulos LMS y Soporte Técnico están completamente funcionales, cumpliendo con todas las especificaciones definidas en el documento API_ENDPOINTS.md.

La API proporciona endpoints RESTful seguros, bien documentados y con respuestas consistentes, lista para ser consumida por aplicaciones frontend o servicios externos.

**Estado del Proyecto:** ✅ Implementado y Funcional  
**Fecha de Finalización:** Octubre 2025  
**Versión Estable:** 1.0.0
