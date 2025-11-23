# API de Infraestructura Tecnológica

Base URL: `/api/infrastructure`

**Autenticación:** Bearer Token (Sanctum)

---

## Tech Assets (Activos Tecnológicos)

### Listar Activos
```
GET /infrastructure/assets
```

**Response:**
```json
[
    {
        "id": 1,
        "name": "Laptop Dell XPS 15",
        "type": "hardware",
        "status": "in_use",
        "user_id": 1,
        "acquisition_date": "2024-01-15",
        "expiration_date": "2027-01-15",
        "created_at": "2024-01-15T00:00:00.000000Z",
        "updated_at": "2024-01-15T00:00:00.000000Z"
    }
]
```

### Ver Activo
```
GET /infrastructure/assets/{id}
```

**Response:**
```json
{
    "id": 1,
    "name": "Laptop Dell XPS 15",
    "type": "hardware",
    "status": "in_use",
    "user_id": 1,
    "acquisition_date": "2024-01-15",
    "expiration_date": "2027-01-15",
    "created_at": "2024-01-15T00:00:00.000000Z",
    "updated_at": "2024-01-15T00:00:00.000000Z"
}
```

### Crear Activo
```
POST /infrastructure/assets
Content-Type: application/json
```

**Body:**
```json
{
    "name": "Laptop Dell XPS 15",
    "type": "hardware",
    "status": "in_use",
    "user_id": 1,
    "acquisition_date": "2024-01-15",
    "expiration_date": "2027-01-15"
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| name | string | Sí | Nombre del activo |
| type | string | Sí | Tipo: `hardware`, `software`, `license`, `subscription` |
| status | string | No | Estado: `in_use`, `in_storage`, `in_repair`, `disposed`, `lost` |
| user_id | integer | Sí | ID del usuario asignado |
| acquisition_date | date | No | Fecha de adquisición (YYYY-MM-DD) |
| expiration_date | date | No | Fecha de expiración (YYYY-MM-DD) |

### Actualizar Activo
```
PUT /infrastructure/assets/{id}
Content-Type: application/json
```

**Body:** (solo campos a actualizar)
```json
{
    "name": "Laptop Dell XPS 15 - Actualizado",
    "status": "in_repair"
}
```

### Eliminar Activo
```
DELETE /infrastructure/assets/{id}
```

**Response:**
```json
{
    "message": "Activo eliminado correctamente"
}
```

---

## Hardware

### Listar Hardware
```
GET /infrastructure/hardwares
```

**Response:**
```json
[
    {
        "id": 1,
        "asset_id": 1,
        "model": "Dell XPS 15 9520",
        "serial_number": "SN-DELL-2024-001",
        "warranty_expiration": "2027-01-15",
        "specs": "Intel Core i7-12700H, 32GB RAM, 1TB SSD",
        "created_at": "2024-01-15T00:00:00.000000Z",
        "updated_at": "2024-01-15T00:00:00.000000Z"
    }
]
```

### Ver Hardware
```
GET /infrastructure/hardwares/{id}
```

### Crear Hardware
```
POST /infrastructure/hardwares
Content-Type: application/json
```

**Body:**
```json
{
    "asset_id": 1,
    "model": "Dell XPS 15 9520",
    "serial_number": "SN-DELL-2024-001",
    "warranty_expiration": "2027-01-15",
    "specs": "Intel Core i7-12700H, 32GB RAM, 1TB SSD"
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| asset_id | integer | Sí | ID del TechAsset asociado (debe existir) |
| model | string | No | Modelo del hardware |
| serial_number | string | No | Número de serie |
| warranty_expiration | date | No | Fecha fin de garantía (YYYY-MM-DD) |
| specs | string | No | Especificaciones técnicas |

### Actualizar Hardware
```
PUT /infrastructure/hardwares/{id}
Content-Type: application/json
```

**Body:** (solo campos a actualizar)
```json
{
    "model": "Dell XPS 15 9530",
    "specs": "Intel Core i9-13900H, 64GB RAM, 2TB SSD"
}
```

### Eliminar Hardware
```
DELETE /infrastructure/hardwares/{id}
```

---

## Software

### Listar Software
```
GET /infrastructure/softwares
```

**Response:**
```json
[
    {
        "id": 1,
        "software_name": "Microsoft Office 365",
        "version": "2024",
        "type": "productivity",
        "created_at": "2024-01-15T00:00:00.000000Z",
        "updated_at": "2024-01-15T00:00:00.000000Z"
    }
]
```

### Ver Software
```
GET /infrastructure/softwares/{id}
```

### Crear Software
```
POST /infrastructure/softwares
Content-Type: application/json
```

**Body:**
```json
{
    "software_name": "Microsoft Office 365",
    "version": "2024",
    "type": "productivity"
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| software_name | string | Sí | Nombre del software |
| version | string | No | Versión |
| type | string | No | Tipo de software |

### Actualizar Software
```
PUT /infrastructure/softwares/{id}
Content-Type: application/json
```

### Eliminar Software
```
DELETE /infrastructure/softwares/{id}
```

---

## Licenses (Licencias)

### Listar Licencias
```
GET /infrastructure/licenses
```

**Response:**
```json
[
    {
        "id": 1,
        "software_id": 1,
        "key_code": "XXXXX-XXXXX-XXXXX-XXXXX-XXXXX",
        "provider": "Microsoft",
        "purchase_date": "2024-01-01",
        "expiration_date": "2025-01-01",
        "cost": 299.99,
        "status": "active",
        "created_at": "2024-01-15T00:00:00.000000Z",
        "updated_at": "2024-01-15T00:00:00.000000Z"
    }
]
```

### Ver Licencia
```
GET /infrastructure/licenses/{id}
```

### Crear Licencia
```
POST /infrastructure/licenses
Content-Type: application/json
```

**Body:**
```json
{
    "software_id": 1,
    "key_code": "XXXXX-XXXXX-XXXXX-XXXXX-XXXXX",
    "provider": "Microsoft",
    "purchase_date": "2024-01-01",
    "expiration_date": "2025-01-01",
    "cost": 299.99,
    "status": "active"
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| software_id | integer | Sí | ID del Software asociado |
| key_code | string | Sí | Clave de licencia |
| provider | string | No | Proveedor |
| purchase_date | date | No | Fecha de compra (YYYY-MM-DD) |
| expiration_date | date | No | Fecha de expiración (YYYY-MM-DD) |
| cost | decimal | No | Costo de la licencia |
| status | string | No | Estado: `active`, `expired`, `revoked` |

### Actualizar Licencia
```
PUT /infrastructure/licenses/{id}
Content-Type: application/json
```

### Eliminar Licencia
```
DELETE /infrastructure/licenses/{id}
```

---

## License Assignments (Asignaciones de Licencias)

### Listar Asignaciones
```
GET /infrastructure/assignments
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "license_id": 1,
            "asset_id": 1,
            "assigned_date": "2024-01-15",
            "status": "active",
            "created_at": "2024-01-15T00:00:00.000000Z",
            "updated_at": "2024-01-15T00:00:00.000000Z"
        }
    ]
}
```

### Ver Asignación
```
GET /infrastructure/assignments/{id}
```

### Crear Asignación
```
POST /infrastructure/assignments
Content-Type: application/json
```

**Body:**
```json
{
    "license_id": 1,
    "asset_id": 1,
    "assigned_date": "2024-01-15",
    "status": "active"
}
```

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| license_id | integer | Sí | ID de la licencia (debe existir) |
| asset_id | integer | Sí | ID del activo (debe existir) |
| assigned_date | date | Sí | Fecha de asignación (YYYY-MM-DD) |
| status | string | Sí | Estado de la asignación |

### Actualizar Asignación
```
PUT /infrastructure/assignments/{id}
Content-Type: application/json
```

**Body:** (solo campos a actualizar)
```json
{
    "status": "inactive"
}
```

### Eliminar Asignación
```
DELETE /infrastructure/assignments/{id}
```

---

## Enums Válidos

### TechAssetType (Tipo de Activo)
| Valor | Descripción |
|-------|-------------|
| `hardware` | Hardware físico |
| `software` | Software |
| `license` | Licencia |
| `subscription` | Suscripción |

### TechAssetStatus (Estado de Activo)
| Valor | Descripción |
|-------|-------------|
| `in_use` | En uso |
| `in_storage` | En almacén |
| `in_repair` | En reparación |
| `disposed` | Dado de baja |
| `lost` | Perdido |

---

## Errores Comunes

### Validación (422)
```json
{
    "success": false,
    "message": "Los datos proporcionados no son válidos.",
    "errors": {
        "campo": ["Mensaje de error"]
    }
}
```

### No encontrado (404)
```json
{
    "message": "Activo no encontrado"
}
```

### Error interno (500)
```json
{
    "success": false,
    "message": "Error al crear asignación",
    "error": "Descripción del error"
}
```

---

## Flujo de Creación Recomendado

1. **Crear TechAsset** → Obtener `asset_id`
2. **Crear Hardware** (usando `asset_id`) → Para detalles de hardware
3. **Crear Software** → Obtener `software_id`
4. **Crear License** (usando `software_id`) → Obtener `license_id`
5. **Crear Assignment** (usando `license_id` y `asset_id`) → Asignar licencia a activo
