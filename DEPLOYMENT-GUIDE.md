# Guía de Deployment - TechProc Backend en Google Cloud Platform

## Información del Proyecto

- **Base de datos:** Cloud SQL PostgreSQL (Compartida con equipo)
- **IP DB:** 34.31.100.155
- **Base de datos:** postgres
- **Usuario DB:** incadevdml
- **Contraseña:** TituzNoHagasDR0P!
- **Región:** us-central1 (Iowa)

**NOTA:** La base de datos ya está deployada y configurada por otro miembro del equipo.

---

## Prerequisitos

1. **Google Cloud SDK instalado** (gcloud CLI)
   - Descargar desde: https://cloud.google.com/sdk/docs/install
   - Verificar instalación: `gcloud --version`

2. **Autenticarse en Google Cloud**
   ```bash
   gcloud auth login
   ```

3. **Obtener tu Project ID**
   - Ve a la consola de GCP
   - En la parte superior, verás el nombre del proyecto
   - Al lado aparece el Project ID (ejemplo: `project-48be602-cd967-4478-ab6`)
   - **Necesitas anotar este Project ID**

4. **Configurar el proyecto activo**
   ```bash
   gcloud config set project [TU-PROJECT-ID]
   ```

---

## Paso 1: Preparar archivos de configuración

### 1.1 Crear `app.yaml`

Ya se creó el archivo `app.yaml` en la raíz del proyecto.

**IMPORTANTE:** Antes de continuar, necesitas:
- Obtener tu Project ID de GCP
- Actualizar el archivo `app.yaml` con la información correcta

### 1.2 Actualizar `app.yaml` con tu Project ID

En el archivo `app.yaml`, actualiza la URL de la aplicación:

```yaml
# CAMBIAR ESTO:
APP_URL: "https://[TU-PROJECT-ID].ue.r.appspot.com"

# Ejemplo:
# Si tu Project ID es "my-project-123", quedaría:
# APP_URL: "https://my-project-123.ue.r.appspot.com"
```

**NOTA:** La configuración de la base de datos ya está correcta (apunta a la DB compartida: 34.31.100.155).

### 1.3 Crear `.gcloudignore`

Crear archivo `.gcloudignore` en la raíz con este contenido:

```
.git
.gitignore
.env
.env.*
/vendor/
/node_modules/
/storage/*.key
/storage/logs/*
/storage/framework/cache/*
/storage/framework/sessions/*
/storage/framework/views/*
/bootstrap/cache/*
README.md
tests/
.phpunit.result.cache
phpunit.xml
/public/hot
/public/storage
npm-debug.log
yarn-error.log
```

---

## Paso 2: Habilitar APIs necesarias en GCP

Ejecuta estos comandos para habilitar las APIs requeridas:

```bash
gcloud services enable appengine.googleapis.com
gcloud services enable sqladmin.googleapis.com
gcloud services enable cloudbuild.googleapis.com
```

---

## Paso 3: Crear aplicación de App Engine

**Solo necesitas hacer esto UNA VEZ** (la primera vez):

```bash
gcloud app create --region=us-central
```

Si ya existe, recibirás un mensaje indicándolo (esto es normal).

---

## Paso 4: Optimizar Laravel para producción

Antes de deployar, ejecuta estos comandos en tu máquina local:

```bash
# Instalar dependencias de producción
composer install --optimize-autoloader --no-dev

# Optimizar configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Si tienes assets frontend, compilarlos
npm run build
```

**IMPORTANTE:** Después del deployment, necesitas revertir `--no-dev`:

```bash
composer install
```

---

## Paso 5: Deploy a App Engine

### 5.1 Deploy inicial

```bash
gcloud app deploy app.yaml
```

Te preguntará:
- Región: confirma `us-central`
- Continuar: escribe `Y`

El proceso tomará 5-10 minutos.

### 5.2 Ver logs del deployment

```bash
gcloud app logs tail -s default
```

### 5.3 Abrir la aplicación deployada

```bash
gcloud app browse
```

O visitar: `https://[TU-PROJECT-ID].ue.r.appspot.com`

---

## Paso 6: Configuración post-deployment

### 6.1 Ejecutar migraciones en producción

Si necesitas ejecutar migraciones en producción:

```bash
gcloud app deploy --no-promote
gcloud app instances ssh [INSTANCE-ID] --service default --version [VERSION]

# Una vez dentro del servidor:
cd /srv
php artisan migrate --force
```

**NOTA:** Como ya ejecutaste las migraciones localmente en Cloud SQL, NO necesitas hacer esto.

### 6.2 Verificar estado de la aplicación

```bash
gcloud app versions list
gcloud app services list
```

---

## Paso 7: Probar endpoints

Probar los endpoints públicos en Postman:

```
GET https://[TU-PROJECT-ID].ue.r.appspot.com/api/health
GET https://[TU-PROJECT-ID].ue.r.appspot.com/api/test-public
```

---

## Comandos útiles

### Ver logs en tiempo real
```bash
gcloud app logs tail -s default
```

### Ver versiones deployadas
```bash
gcloud app versions list
```

### Cambiar tráfico entre versiones
```bash
gcloud app services set-traffic default --splits [VERSION-ID]=1
```

### Eliminar versiones antiguas
```bash
gcloud app versions delete [VERSION-ID]
```

### SSH a una instancia
```bash
gcloud app instances ssh [INSTANCE-ID] --service default
```

---

## Solución de problemas

### Error: "Cloud SQL connection failed"

Verificar que:
- La configuración `DB_HOST` en `app.yaml` use el formato: `/cloudsql/[PROJECT-ID]:us-central1:db-incadev`
- La instancia de Cloud SQL esté corriendo
- Las credenciales sean correctas

### Error: "Permission denied"

Ejecutar:
```bash
gcloud auth application-default login
```

### Error: "502 Bad Gateway"

- Revisar logs: `gcloud app logs tail -s default`
- Verificar que las dependencias de PHP estén instaladas correctamente
- Verificar memoria y CPU en `app.yaml`

---

## Configuración de dominio personalizado (Opcional)

Si quieres usar un dominio personalizado:

1. Ir a: App Engine → Settings → Custom domains
2. Agregar dominio
3. Verificar propiedad del dominio
4. Configurar registros DNS según instrucciones de GCP

---

## Costos estimados

Con la configuración actual:
- **App Engine:** ~$0.05 - $0.10/hora (con auto-scaling)
- **Cloud SQL (Enterprise):** ~$0.08/hora
- **Almacenamiento:** Mínimo

**Total mensual estimado:** $50 - $100 USD (con tráfico bajo/medio)

Para reducir costos:
- Usar Cloud SQL Standard Edition
- Ajustar `min_instances` a 0 en `app.yaml`
- Usar instancias f1-micro

---

## Próximos pasos recomendados

1. Configurar CI/CD con Cloud Build
2. Implementar Cloud Storage para archivos estáticos
3. Configurar Cloud CDN para mejor rendimiento
4. Implementar monitoreo con Cloud Monitoring
5. Configurar backups automáticos de Cloud SQL

---

## Notas importantes

- **NO** commitear el archivo `.env` al repositorio
- **NO** exponer credenciales en `app.yaml` (usar Secret Manager para producción)
- Mantener `APP_DEBUG=false` en producción
- Configurar backups automáticos de la base de datos
- Revisar logs regularmente

---

**Documentación oficial:**
- App Engine PHP: https://cloud.google.com/appengine/docs/standard/php
- Cloud SQL: https://cloud.google.com/sql/docs
- gcloud CLI: https://cloud.google.com/sdk/gcloud/reference
