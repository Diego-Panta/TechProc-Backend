#!/bin/bash

#############################################
# Script de Instalación de Dependencias
# TechProc Backend - Ubuntu 22.04 LTS
#############################################

set -e  # Detener en caso de error

echo "========================================="
echo "🚀 TechProc Backend - Instalación"
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

# Verificar que se está ejecutando como root o con sudo
if [ "$EUID" -ne 0 ]; then
    print_error "Por favor ejecuta este script con sudo"
    exit 1
fi

# ========================================
# 1. ACTUALIZAR SISTEMA
# ========================================
print_step "Actualizando sistema..."
apt update -y
apt upgrade -y

# ========================================
# 2. INSTALAR DEPENDENCIAS BÁSICAS
# ========================================
print_step "Instalando dependencias básicas..."
apt install -y software-properties-common curl wget git unzip vim

# ========================================
# 3. INSTALAR PHP 8.2
# ========================================
print_step "Agregando repositorio de PHP..."
add-apt-repository ppa:ondrej/php -y
apt update -y

print_step "Instalando PHP 8.2 y extensiones..."
apt install -y \
    php8.2 \
    php8.2-fpm \
    php8.2-cli \
    php8.2-common \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-pgsql \
    php8.2-gd \
    php8.2-bcmath \
    php8.2-intl \
    php8.2-redis \
    php8.2-opcache

# Verificar instalación de PHP
PHP_VERSION=$(php -v | head -n 1)
echo "✓ PHP instalado: $PHP_VERSION"

# ========================================
# 4. INSTALAR COMPOSER
# ========================================
print_step "Instalando Composer..."
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

# Verificar instalación de Composer
COMPOSER_VERSION=$(composer --version)
echo "✓ $COMPOSER_VERSION"

# ========================================
# 5. INSTALAR POSTGRESQL 15
# ========================================
print_step "Instalando PostgreSQL 15..."
apt install -y postgresql postgresql-contrib

# Iniciar y habilitar PostgreSQL
systemctl start postgresql
systemctl enable postgresql

# Verificar PostgreSQL
PG_VERSION=$(psql --version)
echo "✓ $PG_VERSION"

# ========================================
# 6. INSTALAR NGINX
# ========================================
print_step "Instalando Nginx..."
apt install -y nginx

# Iniciar y habilitar Nginx
systemctl start nginx
systemctl enable nginx

# Verificar Nginx
NGINX_VERSION=$(nginx -v 2>&1)
echo "✓ $NGINX_VERSION"

# ========================================
# 7. INSTALAR CERTBOT (PARA SSL)
# ========================================
print_step "Instalando Certbot para SSL..."
apt install -y certbot python3-certbot-nginx

# ========================================
# 8. CONFIGURACIONES ADICIONALES
# ========================================
print_step "Configurando PHP..."

# Optimizar php.ini para producción
PHP_INI="/etc/php/8.2/fpm/php.ini"
cp "$PHP_INI" "$PHP_INI.backup"

# Modificar configuraciones
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/' "$PHP_INI"
sed -i 's/post_max_size = .*/post_max_size = 100M/' "$PHP_INI"
sed -i 's/max_execution_time = .*/max_execution_time = 300/' "$PHP_INI"
sed -i 's/memory_limit = .*/memory_limit = 512M/' "$PHP_INI"

echo "✓ PHP configurado"

# Optimizar PHP-FPM
print_step "Configurando PHP-FPM..."
PHP_FPM_POOL="/etc/php/8.2/fpm/pool.d/www.conf"
cp "$PHP_FPM_POOL" "$PHP_FPM_POOL.backup"

# Reiniciar PHP-FPM
systemctl restart php8.2-fpm

echo "✓ PHP-FPM configurado"

# ========================================
# 9. CONFIGURAR FIREWALL UFW (OPCIONAL)
# ========================================
print_step "Configurando firewall UFW..."
ufw --force enable
ufw allow 22    # SSH
ufw allow 80    # HTTP
ufw allow 443   # HTTPS

echo "✓ Firewall configurado"

# ========================================
# 10. CREAR DIRECTORIO PARA LA APLICACIÓN
# ========================================
print_step "Creando directorio para la aplicación..."
mkdir -p /var/www/techproc-backend
chown -R $SUDO_USER:$SUDO_USER /var/www/techproc-backend

echo "✓ Directorio creado: /var/www/techproc-backend"

# ========================================
# RESUMEN
# ========================================
echo ""
echo "========================================="
echo "✅ INSTALACIÓN COMPLETADA"
echo "========================================="
echo ""
echo "Servicios instalados:"
echo "  • PHP 8.2 y extensiones"
echo "  • Composer"
echo "  • PostgreSQL 15"
echo "  • Nginx"
echo "  • Certbot (SSL)"
echo ""
echo "Estado de servicios:"
systemctl is-active --quiet postgresql && echo "  ✓ PostgreSQL: Corriendo" || echo "  ✗ PostgreSQL: Detenido"
systemctl is-active --quiet nginx && echo "  ✓ Nginx: Corriendo" || echo "  ✗ Nginx: Detenido"
systemctl is-active --quiet php8.2-fpm && echo "  ✓ PHP-FPM: Corriendo" || echo "  ✗ PHP-FPM: Detenido"
echo ""
echo "Próximos pasos:"
echo "  1. Configurar PostgreSQL (crear base de datos)"
echo "  2. Clonar/subir el código de la aplicación"
echo "  3. Configurar Nginx"
echo "  4. Configurar archivo .env"
echo ""
echo "Ver documentación completa en: docs/DEPLOYMENT_GCP.md"
echo "========================================="
