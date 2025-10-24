#!/bin/bash

#############################################
# Script de Configuración de Base de Datos
# TechProc Backend - PostgreSQL
#############################################

set -e  # Detener en caso de error

echo "========================================="
echo "🗄️  TechProc Backend - Setup Database"
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

# Solicitar información de la base de datos
echo "Por favor proporciona la siguiente información:"
echo ""

read -p "Nombre de la base de datos [techproc_db]: " DB_NAME
DB_NAME=${DB_NAME:-techproc_db}

read -p "Usuario de la base de datos [techproc_user]: " DB_USER
DB_USER=${DB_USER:-techproc_user}

read -sp "Contraseña del usuario (dejar vacío para generar automáticamente): " DB_PASSWORD
echo ""

# Generar contraseña si está vacía
if [ -z "$DB_PASSWORD" ]; then
    DB_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
    print_warning "Contraseña generada automáticamente: $DB_PASSWORD"
    print_warning "¡GUARDA ESTA CONTRASEÑA EN UN LUGAR SEGURO!"
fi

read -p "¿Deseas importar el schema SQL? (s/n) [s]: " IMPORT_SCHEMA
IMPORT_SCHEMA=${IMPORT_SCHEMA:-s}

if [ "$IMPORT_SCHEMA" == "s" ]; then
    read -p "Ruta al archivo SQL [/var/www/techproc-backend/dat/Ultimabd (3).sql]: " SQL_FILE
    SQL_FILE=${SQL_FILE:-"/var/www/techproc-backend/dat/Ultimabd (3).sql"}
fi

echo ""
print_step "Configurando base de datos PostgreSQL..."
echo ""

# ========================================
# 1. CREAR BASE DE DATOS Y USUARIO
# ========================================
print_step "Creando base de datos y usuario..."

sudo -u postgres psql <<EOF
-- Crear base de datos
CREATE DATABASE $DB_NAME;

-- Crear usuario con contraseña
CREATE USER $DB_USER WITH ENCRYPTED PASSWORD '$DB_PASSWORD';

-- Otorgar privilegios
GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;

-- Cambiar owner de la base de datos
ALTER DATABASE $DB_NAME OWNER TO $DB_USER;

-- Otorgar privilegios en el schema public
\c $DB_NAME
GRANT ALL ON SCHEMA public TO $DB_USER;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO $DB_USER;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO $DB_USER;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO $DB_USER;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO $DB_USER;
EOF

echo "✓ Base de datos '$DB_NAME' creada"
echo "✓ Usuario '$DB_USER' creado con privilegios"

# ========================================
# 2. IMPORTAR SCHEMA SQL (OPCIONAL)
# ========================================
if [ "$IMPORT_SCHEMA" == "s" ]; then
    print_step "Importando schema SQL..."

    if [ -f "$SQL_FILE" ]; then
        sudo -u postgres psql -d "$DB_NAME" -f "$SQL_FILE"
        echo "✓ Schema SQL importado exitosamente"
    else
        print_error "Archivo SQL no encontrado: $SQL_FILE"
        print_warning "Deberás importar el schema manualmente más tarde"
    fi
fi

# ========================================
# 3. VERIFICAR CONEXIÓN
# ========================================
print_step "Verificando conexión a la base de datos..."

export PGPASSWORD="$DB_PASSWORD"
psql -h localhost -U "$DB_USER" -d "$DB_NAME" -c "SELECT version();" > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo "✓ Conexión a la base de datos exitosa"
else
    print_error "No se pudo conectar a la base de datos"
    exit 1
fi

# ========================================
# 4. GUARDAR CREDENCIALES EN ARCHIVO
# ========================================
print_step "Guardando credenciales..."

CREDENTIALS_FILE="/root/.techproc_db_credentials"
cat > "$CREDENTIALS_FILE" <<EOF
# Credenciales de Base de Datos - TechProc Backend
# Generadas el: $(date)
# ¡MANTÉN ESTE ARCHIVO SEGURO!

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASSWORD
EOF

chmod 600 "$CREDENTIALS_FILE"
echo "✓ Credenciales guardadas en: $CREDENTIALS_FILE"

# ========================================
# RESUMEN
# ========================================
echo ""
echo "========================================="
echo "✅ BASE DE DATOS CONFIGURADA"
echo "========================================="
echo ""
echo "Información de la base de datos:"
echo "  • Nombre: $DB_NAME"
echo "  • Usuario: $DB_USER"
echo "  • Contraseña: $DB_PASSWORD"
echo "  • Host: 127.0.0.1"
echo "  • Puerto: 5432"
echo ""
echo "Credenciales guardadas en: $CREDENTIALS_FILE"
echo ""
echo "Para configurar tu aplicación Laravel:"
echo "  1. Edita el archivo .env"
echo "  2. Copia las credenciales anteriores en las variables DB_*"
echo ""
echo "Próximo paso:"
echo "  Ejecuta: ./scripts/deploy-app.sh"
echo "========================================="
