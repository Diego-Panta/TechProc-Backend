# API Contracts - Módulo SupportTechnical

## Descripción General
Este módulo gestiona el sistema de tickets de soporte técnico, permitiendo a los usuarios crear tickets, responder a ellos y adjuntar archivos a las respuestas.

## Modelos Principales
- **Ticket**: Representa un ticket de soporte
- **TicketReply**: Representa una respuesta a un ticket
- **ReplyAttachment**: Representa un archivo adjunto a una respuesta

---

## Endpoints

### 1. Gestión de Tickets

#### 1.1 Listar Tickets
**GET** `/api/support/tickets`

**Descripción**: Obtiene una lista paginada de tickets. Los usuarios regulares solo ven sus propios tickets. Los administradores/soporte ven todos los tickets.

**Headers**:
```
Authorization: Bearer {token}
```

**Query Parameters**:
| Parámetro | Tipo | Requerido | Descripción | Valores |
|-----------|------|-----------|-------------|---------|
| page | integer | No | Número de página | Default: 1 |
| per_page | integer | No | Items por página | Default: 15, Max: 100 |
| status | string | No | Filtrar por estado | open, pending, closed |
| priority | string | No | Filtrar por prioridad | low, medium, high, urgent |
| type | string | No | Filtrar por tipo | technical, academic, administrative, inquiry |
| search | string | No | Búsqueda en título | Min: 3 caracteres |
| sort_by | string | No | Campo para ordenar | created_at, updated_at, priority |
| sort_order | string | No | Dirección de orden | asc, desc (default: desc) |

**Response 200 - Success**:
```json
{
  "status": "success",
  "data": {
    "tickets": [
      {
        "id": 1,
        "user_id": 5,
        "title": "Problema con el acceso al sistema",
        "type": "technical",
        "status": "open",
        "priority": "high",
        "created_at": "2025-11-08T10:30:00.000000Z",
        "updated_at": "2025-11-08T10:30:00.000000Z",
        "user": {
          "id": 5,
          "name": "Juan Pérez",
          "email": "juan.perez@example.com"
        },
        "replies_count": 3,
        "last_reply": {
          "id": 15,
          "content": "Estamos trabajando en ello...",
          "created_at": "2025-11-08T11:45:00.000000Z",
          "user": {
            "id": 2,
            "name": "Soporte Técnico"
          }
        }
      }
    ],
    "pagination": {
      "total": 45,
      "count": 15,
      "per_page": 15,
      "current_page": 1,
      "total_pages": 3,
      "links": {
        "next": "/api/support/tickets?page=2"
      }
    }
  }
}
```

---

#### 1.2 Crear Ticket
**POST** `/api/support/tickets`

**Descripción**: Crea un nuevo ticket de soporte.

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "title": "No puedo acceder al módulo de reportes",
  "type": "technical",
  "priority": "medium",
  "content": "Al intentar acceder al módulo de reportes, recibo un error 403. He intentado cerrar sesión y volver a entrar sin éxito."
}
```

**Validaciones**:
| Campo | Reglas |
|-------|--------|
| title | required, string, max:255 |
| type | nullable, string, in:technical,academic,administrative,inquiry |
| priority | required, string, in:low,medium,high,urgent |
| content | required, string, min:10 |

**Response 201 - Created**:
```json
{
  "status": "success",
  "message": "Ticket creado exitosamente",
  "data": {
    "ticket": {
      "id": 46,
      "user_id": 5,
      "title": "No puedo acceder al módulo de reportes",
      "type": "technical",
      "status": "open",
      "priority": "medium",
      "created_at": "2025-11-08T12:00:00.000000Z",
      "updated_at": "2025-11-08T12:00:00.000000Z",
      "replies": [
        {
          "id": 120,
          "user_id": 5,
          "content": "Al intentar acceder al módulo de reportes, recibo un error 403. He intentado cerrar sesión y volver a entrar sin éxito.",
          "created_at": "2025-11-08T12:00:00.000000Z",
          "attachments_count": 0
        }
      ]
    }
  }
}
```

**Response 422 - Validation Error**:
```json
{
  "status": "error",
  "message": "Error de validación",
  "errors": {
    "title": ["El campo título es requerido"],
    "priority": ["La prioridad seleccionada no es válida"]
  }
}
```

---

#### 1.3 Ver Detalle de Ticket
**GET** `/api/support/tickets/{id}`

**Descripción**: Obtiene el detalle completo de un ticket incluyendo todas sus respuestas y adjuntos.

**Headers**:
```
Authorization: Bearer {token}
```

**URL Parameters**:
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| id | integer | ID del ticket |

**Response 200 - Success**:
```json
{
  "status": "success",
  "data": {
    "ticket": {
      "id": 46,
      "user_id": 5,
      "title": "No puedo acceder al módulo de reportes",
      "type": "technical",
      "status": "open",
      "priority": "medium",
      "created_at": "2025-11-08T12:00:00.000000Z",
      "updated_at": "2025-11-08T12:00:00.000000Z",
      "user": {
        "id": 5,
        "name": "Juan Pérez",
        "email": "juan.perez@example.com"
      },
      "replies": [
        {
          "id": 120,
          "ticket_id": 46,
          "user_id": 5,
          "content": "Al intentar acceder al módulo de reportes, recibo un error 403.",
          "created_at": "2025-11-08T12:00:00.000000Z",
          "updated_at": "2025-11-08T12:00:00.000000Z",
          "user": {
            "id": 5,
            "name": "Juan Pérez"
          },
          "attachments": [
            {
              "id": 45,
              "type": "image",
              "path": "tickets/46/replies/120/screenshot.png",
              "url": "https://storage.example.com/tickets/46/replies/120/screenshot.png",
              "created_at": "2025-11-08T12:00:00.000000Z"
            }
          ]
        },
        {
          "id": 121,
          "ticket_id": 46,
          "user_id": 2,
          "content": "Hemos recibido tu ticket. Estamos revisando el problema.",
          "created_at": "2025-11-08T12:15:00.000000Z",
          "updated_at": "2025-11-08T12:15:00.000000Z",
          "user": {
            "id": 2,
            "name": "Soporte Técnico"
          },
          "attachments": []
        }
      ]
    }
  }
}
```

**Response 404 - Not Found**:
```json
{
  "status": "error",
  "message": "Ticket no encontrado"
}
```

**Response 403 - Forbidden**:
```json
{
  "status": "error",
  "message": "No tienes permiso para ver este ticket"
}
```

---

#### 1.4 Actualizar Ticket
**PUT** `/api/support/tickets/{id}`

**Descripción**: Actualiza la información de un ticket. Los usuarios solo pueden actualizar el título. El personal de soporte puede actualizar status, priority y type.

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**URL Parameters**:
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| id | integer | ID del ticket |

**Request Body (Usuario regular)**:
```json
{
  "title": "Actualización: No puedo acceder al módulo de reportes - Error 403"
}
```

**Request Body (Personal de soporte)**:
```json
{
  "title": "No puedo acceder al módulo de reportes - Error 403",
  "status": "pending",
  "priority": "high",
  "type": "technical"
}
```

**Validaciones**:
| Campo | Reglas |
|-------|--------|
| title | sometimes, string, max:255 |
| status | sometimes, string, in:open,pending,closed (solo soporte) |
| priority | sometimes, string, in:low,medium,high,urgent (solo soporte) |
| type | sometimes, nullable, string, in:technical,academic,administrative,inquiry (solo soporte) |

**Response 200 - Success**:
```json
{
  "status": "success",
  "message": "Ticket actualizado exitosamente",
  "data": {
    "ticket": {
      "id": 46,
      "user_id": 5,
      "title": "Actualización: No puedo acceder al módulo de reportes - Error 403",
      "type": "technical",
      "status": "pending",
      "priority": "high",
      "created_at": "2025-11-08T12:00:00.000000Z",
      "updated_at": "2025-11-08T13:00:00.000000Z"
    }
  }
}
```

---

#### 1.5 Cerrar Ticket
**POST** `/api/support/tickets/{id}/close`

**Descripción**: Cierra un ticket. Los usuarios pueden cerrar sus propios tickets. El personal de soporte puede cerrar cualquier ticket.

**Headers**:
```
Authorization: Bearer {token}
```

**URL Parameters**:
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| id | integer | ID del ticket |

**Response 200 - Success**:
```json
{
  "status": "success",
  "message": "Ticket cerrado exitosamente",
  "data": {
    "ticket": {
      "id": 46,
      "status": "closed",
      "updated_at": "2025-11-08T14:00:00.000000Z"
    }
  }
}
```

**Response 400 - Bad Request**:
```json
{
  "status": "error",
  "message": "El ticket ya está cerrado"
}
```

---

#### 1.6 Reabrir Ticket
**POST** `/api/support/tickets/{id}/reopen`

**Descripción**: Reabre un ticket cerrado. Solo el propietario del ticket o personal de soporte pueden reabrir tickets.

**Headers**:
```
Authorization: Bearer {token}
```

**URL Parameters**:
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| id | integer | ID del ticket |

**Response 200 - Success**:
```json
{
  "status": "success",
  "message": "Ticket reabierto exitosamente",
  "data": {
    "ticket": {
      "id": 46,
      "status": "open",
      "updated_at": "2025-11-08T15:00:00.000000Z"
    }
  }
}
```

**Response 400 - Bad Request**:
```json
{
  "status": "error",
  "message": "El ticket no está cerrado"
}
```

---

### 2. Gestión de Respuestas

#### 2.1 Crear Respuesta
**POST** `/api/support/tickets/{ticketId}/replies`

**Descripción**: Agrega una respuesta a un ticket. Opcionalmente puede incluir archivos adjuntos.

**Headers**:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**URL Parameters**:
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| ticketId | integer | ID del ticket |

**Request Body (multipart/form-data)**:
```
content: "He verificado los permisos de mi usuario y parecen estar correctos. ¿Podría revisar desde su lado?"
attachments[]: (file) screenshot1.png
attachments[]: (file) error_log.txt
```

**Validaciones**:
| Campo | Reglas |
|-------|--------|
| content | required, string, min:5 |
| attachments | nullable, array, max:5 |
| attachments.* | file, max:10240 (10MB), mimes:jpg,jpeg,png,pdf,doc,docx,txt,zip |

**Response 201 - Created**:
```json
{
  "status": "success",
  "message": "Respuesta agregada exitosamente",
  "data": {
    "reply": {
      "id": 122,
      "ticket_id": 46,
      "user_id": 5,
      "content": "He verificado los permisos de mi usuario y parecen estar correctos. ¿Podría revisar desde su lado?",
      "created_at": "2025-11-08T15:30:00.000000Z",
      "updated_at": "2025-11-08T15:30:00.000000Z",
      "user": {
        "id": 5,
        "name": "Juan Pérez"
      },
      "attachments": [
        {
          "id": 46,
          "type": "image",
          "path": "tickets/46/replies/122/screenshot1.png",
          "url": "https://storage.example.com/tickets/46/replies/122/screenshot1.png",
          "created_at": "2025-11-08T15:30:00.000000Z"
        },
        {
          "id": 47,
          "type": "document",
          "path": "tickets/46/replies/122/error_log.txt",
          "url": "https://storage.example.com/tickets/46/replies/122/error_log.txt",
          "created_at": "2025-11-08T15:30:00.000000Z"
        }
      ]
    }
  }
}
```

**Response 403 - Forbidden**:
```json
{
  "status": "error",
  "message": "No puedes responder a este ticket"
}
```

**Response 400 - Bad Request**:
```json
{
  "status": "error",
  "message": "No se puede responder a un ticket cerrado"
}
```

---

#### 2.2 Actualizar Respuesta
**PUT** `/api/support/tickets/{ticketId}/replies/{replyId}`

**Descripción**: Actualiza el contenido de una respuesta. Solo el autor de la respuesta puede editarla y solo dentro de las primeras 24 horas.

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**URL Parameters**:
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| ticketId | integer | ID del ticket |
| replyId | integer | ID de la respuesta |

**Request Body**:
```json
{
  "content": "He verificado los permisos de mi usuario y parecen estar correctos. ¿Podría revisar desde su lado? (Actualizado con más detalles)"
}
```

**Validaciones**:
| Campo | Reglas |
|-------|--------|
| content | required, string, min:5 |

**Response 200 - Success**:
```json
{
  "status": "success",
  "message": "Respuesta actualizada exitosamente",
  "data": {
    "reply": {
      "id": 122,
      "content": "He verificado los permisos de mi usuario y parecen estar correctos. ¿Podría revisar desde su lado? (Actualizado con más detalles)",
      "updated_at": "2025-11-08T16:00:00.000000Z"
    }
  }
}
```

**Response 403 - Forbidden**:
```json
{
  "status": "error",
  "message": "No puedes editar esta respuesta. Solo puedes editar tus propias respuestas dentro de las primeras 24 horas."
}
```

---

#### 2.3 Eliminar Respuesta
**DELETE** `/api/support/tickets/{ticketId}/replies/{replyId}`

**Descripción**: Elimina una respuesta. Solo el autor puede eliminar su respuesta (excepto la primera respuesta del ticket). Personal de soporte puede eliminar cualquier respuesta.

**Headers**:
```
Authorization: Bearer {token}
```

**URL Parameters**:
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| ticketId | integer | ID del ticket |
| replyId | integer | ID de la respuesta |

**Response 200 - Success**:
```json
{
  "status": "success",
  "message": "Respuesta eliminada exitosamente"
}
```

**Response 403 - Forbidden**:
```json
{
  "status": "error",
  "message": "No puedes eliminar la primera respuesta del ticket"
}
```

---

### 3. Gestión de Adjuntos

#### 3.1 Descargar Adjunto
**GET** `/api/support/attachments/{id}/download`

**Descripción**: Descarga un archivo adjunto. Solo usuarios con acceso al ticket pueden descargar adjuntos.

**Headers**:
```
Authorization: Bearer {token}
```

**URL Parameters**:
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| id | integer | ID del adjunto |

**Response 200 - Success**:
```
Content-Type: application/octet-stream
Content-Disposition: attachment; filename="screenshot1.png"

[Binary file content]
```

**Response 404 - Not Found**:
```json
{
  "status": "error",
  "message": "Archivo no encontrado"
}
```

---

#### 3.2 Eliminar Adjunto
**DELETE** `/api/support/attachments/{id}`

**Descripción**: Elimina un archivo adjunto. Solo el autor de la respuesta o personal de soporte pueden eliminar adjuntos.

**Headers**:
```
Authorization: Bearer {token}
```

**URL Parameters**:
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| id | integer | ID del adjunto |

**Response 200 - Success**:
```json
{
  "status": "success",
  "message": "Archivo eliminado exitosamente"
}
```

**Response 403 - Forbidden**:
```json
{
  "status": "error",
  "message": "No tienes permiso para eliminar este archivo"
}
```

---

### 4. Estadísticas (Solo Personal de Soporte)

#### 4.1 Dashboard de Estadísticas
**GET** `/api/support/statistics`

**Descripción**: Obtiene estadísticas generales del sistema de tickets.

**Headers**:
```
Authorization: Bearer {token}
```

**Query Parameters**:
| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| period | string | No | Periodo de tiempo | today, week, month, year (default: month) |

**Response 200 - Success**:
```json
{
  "status": "success",
  "data": {
    "statistics": {
      "total_tickets": 245,
      "open_tickets": 45,
      "pending_tickets": 32,
      "closed_tickets": 168,
      "by_priority": {
        "low": 50,
        "medium": 120,
        "high": 60,
        "urgent": 15
      },
      "by_type": {
        "technical": 180,
        "academic": 35,
        "administrative": 20,
        "inquiry": 10
      },
      "average_response_time": "2.5 hours",
      "average_resolution_time": "18 hours",
      "tickets_created_today": 8,
      "tickets_resolved_today": 12
    }
  }
}
```

---

## Permisos y Roles

### Roles del Sistema:
- **user**: Usuario regular que puede crear y gestionar sus propios tickets
- **support**: Personal de soporte que puede ver y gestionar todos los tickets
- **admin**: Administrador con acceso completo

### Matriz de Permisos:

| Acción | User | Support | Admin |
|--------|------|---------|-------|
| Ver propios tickets | ✓ | ✓ | ✓ |
| Ver todos los tickets | ✗ | ✓ | ✓ |
| Crear ticket | ✓ | ✓ | ✓ |
| Actualizar propio ticket (título) | ✓ | ✓ | ✓ |
| Actualizar status/prioridad | ✗ | ✓ | ✓ |
| Cerrar propio ticket | ✓ | ✓ | ✓ |
| Cerrar cualquier ticket | ✗ | ✓ | ✓ |
| Responder a ticket con acceso | ✓ | ✓ | ✓ |
| Editar propia respuesta (24h) | ✓ | ✓ | ✓ |
| Eliminar cualquier respuesta | ✗ | ✓ | ✓ |
| Ver estadísticas | ✗ | ✓ | ✓ |

---

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Solicitud exitosa |
| 201 | Created - Recurso creado exitosamente |
| 400 | Bad Request - Solicitud inválida |
| 401 | Unauthorized - No autenticado |
| 403 | Forbidden - No autorizado |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Error de validación |
| 500 | Internal Server Error - Error del servidor |

---

## Notas Técnicas

### Almacenamiento de Archivos
- Los archivos se almacenan en: `storage/app/public/tickets/{ticketId}/replies/{replyId}/`
- Límite de tamaño por archivo: 10MB
- Máximo 5 archivos por respuesta
- Formatos permitidos: jpg, jpeg, png, pdf, doc, docx, txt, zip

### Automáticos del Sistema
- Al crear un ticket, se crea automáticamente la primera respuesta con el contenido inicial
- El status por defecto de un nuevo ticket es "open"
- La priority por defecto es "low"
- Al agregar una respuesta a un ticket cerrado, se reabre automáticamente (opcional, según configuración)

### Notificaciones (Futuro)
- Email al usuario cuando un ticket cambia de estado
- Email al usuario cuando recibe una respuesta
- Email al soporte cuando se crea un nuevo ticket de prioridad high/urgent

### Búsqueda y Filtros
- La búsqueda en título es case-insensitive
- Los filtros se pueden combinar
- El ordenamiento por defecto es por fecha de actualización descendente
