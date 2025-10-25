#!/bin/bash

#############################################
# Script de Deployment de Aplicación
# TechProc Backend
#############################################

set -e  # Detener en caso de error

echo "========================================="
echo "🚀 TechProc Backend - Deployment"
echo "========================================="
echo ""

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Función para imprimir mensajes
print_step() {
    echo -e "${GREEN}[STEP]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Variables
APP_DIR="/var/www/techproc-backend"
NGINX_CONFIG="/etc/nginx/sites-available/techproc-backend"

# ========================================
# 1. VERIFICAR DIRECTORIO DE APLICACIÓN
# ========================================
print_step "Verificando directorio de aplicación..."

if [ ! -d "$APP_DIR" ]; then
    print_error "Directorio $APP_DIR no existe"
    exit 1
fi

cd "$APP_DIR"

if [ ! -f "composer.json" ]; then
    print_error "No se encontró composer.json. ¿Clonaste el repositorio?"
    exit 1
fi

echo "✓ Directorio de aplicación encontrado"

# ========================================
# 2. INSTALAR DEPENDENCIAS
# ========================================
print_step "Instalando dependencias de Composer..."
composer install --optimize-autoloader --no-dev

echo "✓ Dependencias instaladas"

# ========================================
# 3. CONFIGURAR ARCHIVO .ENV
# ========================================
print_step "Configurando archivo .env..."

if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "✓ Archivo .env creado desde .env.example"
    else
        print_error "No se encontró .env ni .env.example"
        exit 1
    fi
else
    print_warning "Archivo .env ya existe, no se sobrescribirá"
fi

# Cargar credenciales de base de datos si existen
if [ -f "/root/.techproc_db_credentials" ]; then
    print_step "Cargando credenciales de base de datos..."
    source /root/.techproc_db_credentials

    # Actualizar .env con credenciales
    sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=$DB_CONNECTION/" .env
    sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
    sed -i "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_DATABASE/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USERNAME/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env

    echo "✓ Credenciales de base de datos configuradas"
fi

# Configurar entorno de producción
sed -i "s/APP_ENV=.*/APP_ENV=production/" .env
sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" .env

# ========================================
# 4. GENERAR CLAVES
# ========================================
print_step "Generando claves de aplicación..."

# Generar APP_KEY si no existe
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate
    echo "✓ APP_KEY generada"
else
    echo "✓ APP_KEY ya existe"
fi

# Generar JWT_SECRET si no existe
if ! grep -q "JWT_SECRET=" .env || [ -z "$(grep JWT_SECRET= .env | cut -d'=' -f2)" ]; then
    php artisan jwt:secret --force
    echo "✓ JWT_SECRET generada"
else
    echo "✓ JWT_SECRET ya existe"
fi

# ========================================
# 5. EJECUTAR MIGRACIONES (OPCIONAL)
# ========================================
read -p "¿Deseas ejecutar migraciones de Laravel? (s/n) [n]: " RUN_MIGRATIONS
RUN_MIGRATIONS=${RUN_MIGRATIONS:-n}

if [ "$RUN_MIGRATIONS" == "s" ]; then
    print_step "Ejecutando migraciones..."
    php artisan migrate --force
    echo "✓ Migraciones ejecutadas"
fi

# ========================================
# 6. LIMPIAR Y OPTIMIZAR CACHE
# ========================================
print_step "Optimizando aplicación..."

# Limpiar cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cachear para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✓ Cache optimizado"

# ========================================
# 7. CONFIGURAR PERMISOS
# ========================================
print_step "Configurando permisos de archivos..."

# Cambiar propietario a www-data
chown -R www-data:www-data "$APP_DIR"

# Permisos generales
chmod -R 755 "$APP_DIR"

# Permisos especiales para directorios de escritura
chmod -R 775 "$APP_DIR/storage"
chmod -R 775 "$APP_DIR/bootstrap/cache"

# Si existe directorio de uploads
if [ -d "$APP_DIR/public/uploads" ]; then
    chmod -R 775 "$APP_DIR/public/uploads"
fi

echo "✓ Permisos configurados"

# ========================================
# 8. CONFIGURAR NGINX
# ========================================
print_step "Configurando Nginx..."

# Solicitar dominio o IP
read -p "Ingresa tu dominio o IP pública [$(curl -s ifconfig.me)]: " SERVER_NAME
SERVER_NAME=${SERVER_NAME:-$(curl -s ifconfig.me)}

# Crear configuración de Nginx
cat > "$NGINX_CONFIG" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $SERVER_NAME;

    root $APP_DIR/public;
    index index.php index.html index.htm;

    # Logs
    access_log /var/log/nginx/techproc-access.log;
    error_log /var/log/nginx/techproc-error.log;

    # Aumentar tamaño máximo de upload
    client_max_body_size 100M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;

        # Aumentar timeouts para operaciones largas
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Deshabilitar acceso a archivos sensibles
    location ~ /\.(env|git|svn) {
        deny all;
        return 404;
    }

    # Cache para archivos estáticos
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
EOF

# Habilitar sitio
ln -sf "$NGINX_CONFIG" /etc/nginx/sites-enabled/techproc-backend

# Eliminar default si existe
if [ -f /etc/nginx/sites-enabled/default ]; then
    rm /etc/nginx/sites-enabled/default
fi

# Verificar configuración de Nginx
nginx -t

if [ $? -eq 0 ]; then
    echo "✓ Configuración de Nginx válida"
else
    print_error "Error en la configuración de Nginx"
    exit 1
fi

# ========================================
# 9. REINICIAR SERVICIOS
# ========================================
print_step "Reiniciando servicios..."

systemctl restart php8.2-fpm
systemctl reload nginx

echo "✓ Servicios reiniciados"

# ========================================
# 10. CONFIGURAR SSL (OPCIONAL)
# ========================================
read -p "¿Deseas configurar SSL con Let's Encrypt? (s/n) [n]: " SETUP_SSL
SETUP_SSL=${SETUP_SSL:-n}

if [ "$SETUP_SSL" == "s" ]; then
    print_step "Configurando SSL con Let's Encrypt..."

    read -p "Ingresa tu email para Let's Encrypt: " EMAIL

    certbot --nginx -d "$SERVER_NAME" --non-interactive --agree-tos --email "$EMAIL"

    if [ $? -eq 0 ]; then
        echo "✓ SSL configurado exitosamente"
    else
        print_warning "No se pudo configurar SSL. Verifica que tu dominio apunte a esta IP."
    fi
fi

# ========================================
# 11. VERIFICAR DEPLOYMENT
# ========================================
print_step "Verificando deployment..."

# Probar endpoint de health
HEALTH_CHECK=$(curl -s http://localhost/api/health)

if echo "$HEALTH_CHECK" | grep -q "OK"; then
    echo "✓ API respondiendo correctamente"
else
    print_warning "La API no está respondiendo como se esperaba"
    print_warning "Verifica los logs: tail -f $APP_DIR/storage/logs/laravel.log"
fi

# ========================================
# RESUMEN
# ========================================
echo ""
echo "========================================="
echo "✅ DEPLOYMENT COMPLETADO"
echo "========================================="
echo ""
echo "Información del deployment:"
echo "  • Directorio: $APP_DIR"
echo "  • URL: http://$SERVER_NAME"
echo "  • SSL: $([ "$SETUP_SSL" == "s" ] && echo "Configurado" || echo "No configurado")"
echo ""
echo "Endpoints de prueba:"
echo "  • Health Check: http://$SERVER_NAME/api/health"
echo "  • Estudiantes: http://$SERVER_NAME/api/lms/students"
echo "  • Grupos: http://$SERVER_NAME/api/lms/groups"
echo ""
echo "Logs importantes:"
echo "  • Laravel: $APP_DIR/storage/logs/laravel.log"
echo "  • Nginx Access: /var/log/nginx/techproc-access.log"
echo "  • Nginx Error: /var/log/nginx/techproc-error.log"
echo ""
echo "Comandos útiles:"
echo "  • Ver logs: tail -f $APP_DIR/storage/logs/laravel.log"
echo "  • Actualizar: $APP_DIR/deploy.sh"
echo "  • Reiniciar servicios: systemctl restart php8.2-fpm nginx"
echo ""
echo "========================================="
