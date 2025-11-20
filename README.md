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

**IMPORTANTE**: Usa la misma `APP_KEY` compartida en el grupo de WhatsApp de Jefes.

```env
APP_KEY=base64:LA_APP_KEY_DEL_GRUPO
```

### 4. Ejecutar el servidor

```bash
php artisan serve --port=8001
```

Servidor disponible en: `http://localhost:8001`


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

---

## Solución de Problemas

**Error de conexión a BD**: Verifica las credenciales en `.env`

**Error 403 en login**: El usuario no tiene el rol especificado

**Error 401 (token inválido)**: Verifica que uses la misma `APP_KEY` que el equipo

**Puerto en uso**: Cambia el puerto: `php artisan serve --port=8001`

---
