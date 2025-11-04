# TechProc Backend - Servicio de Autenticación Compartido

Backend centralizado para autenticación, gestión de usuarios, roles y permisos.

---

## Instalación y Configuración

### 1. Clonar el repositorio

```bash
git clone <URL_DEL_REPOSITORIO>
cd TechProc-Backend
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar el archivo .env

```bash
cp .env.example .env
```

Edita el archivo `.env` con tu configuración de base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tu_nombre_db
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

**IMPORTANTE**: Usa la misma `APP_KEY` compartida en el grupo de WhatsApp de Jefes.

```env
APP_KEY=base64:LA_APP_KEY_DEL_GRUPO
```

### 4. Ejecutar seeders (EN ESTE ORDEN)

```bash
php artisan db:seed --class=PermissionsSeeder
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=UsersSeeder
```

### 5. Ejecutar el servidor

```bash
php artisan serve --port=8001
```

Servidor disponible en: `http://localhost:8001`

---


## Roles Disponibles

### GRUPO 03 - SOPORTE Y ADMINISTRACIÓN
- `super_admin` - Super administrador (todos los permisos)
- `admin` - Administrador (gestión completa de usuarios, roles y permisos)
- `support` - Soporte técnico (gestión de tickets)
- `infrastructure` - Infraestructura (gestión de activos tecnológicos)
- `security` - Seguridad (gestión de seguridad de usuarios)
- `academic_analyst` - Analista académico (análisis de notas y asistencias)
- `web` - Desarrollador web (gestión del chatbot y contenido web)

### GRUPO 06 - AUDITORÍA Y ENCUESTAS
- `survey_admin` - Administrador de encuestas
- `audit_manager` - Jefe de auditores
- `auditor` - Auditor (solo lectura)

### GRUPO QUEZADA - RECURSOS HUMANOS Y FINANZAS
- `human_resources` - Recursos humanos (gestión de personal)
- `financial_manager` - Gerente financiero (gestión de flujos financieros)
- `system_viewer` - Visualizador del sistema (solo lectura)
- `enrollment_manager` - Gerente de matrículas
- `data_analyst` - Analista de datos (diseño de KPIs)

### GRUPO HURTADO - MARKETING
- `marketing` - Empleado de marketing (manejo de redes sociales)
- `marketing_admin` - Administrador de marketing (supervisión de campañas)

### GRUPO VÁSQUEZ - ACADÉMICO
- `teacher` - Profesor/Docente (gestión de clases y evaluaciones)
- `student` - Estudiante (acceso básico)

### GRUPO DE LEYTON - TUTORÍAS Y ADMINISTRACIÓN
- `tutor` - Instructor/Profesor/Psicólogo (manejo de tutorías)
- `administrative_clerk` - Empleado administrativo (trámites documentarios)

---

## Para probar

1. Abre Postman
2. Importa el archivo (que se encuentra en este repositorio: ./TechProc-API.postman_collection.json): `TechProc-API.postman_collection.json`
3. Cambia la variable `base_url` a: `http://localhost:8001/api`

---

## Comandos Útiles (si es que hay problemas)

```bash
# Limpiar caché
php artisan cache:clear && php artisan config:clear

# Ver rutas
php artisan route:list

# Refrescar base de datos (elimina todos los datos)
php artisan migrate:fresh
php artisan db:seed --class=PermissionsSeeder && php artisan db:seed --class=RolesSeeder && php artisan db:seed --class=UsersSeeder
```

---

## Solución de Problemas

**Error de conexión a BD**: Verifica las credenciales en `.env`

**Error 403 en login**: El usuario no tiene el rol especificado

**Error 401 (token inválido)**: Verifica que uses la misma `APP_KEY` que el equipo

**Puerto en uso**: Cambia el puerto: `php artisan serve --port=8002`

---