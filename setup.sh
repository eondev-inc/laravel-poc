#!/bin/bash

# Colores para output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   Chain POC - Inicialización Docker   ${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Verificar si Docker está corriendo
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}Error: Docker no está corriendo. Por favor inicia Docker primero.${NC}"
    exit 1
fi

echo -e "${GREEN}1. Copiando archivo .env...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}   ✓ Archivo .env creado${NC}"
else
    echo -e "${BLUE}   ℹ Archivo .env ya existe${NC}"
fi

echo ""
echo -e "${GREEN}2. Construyendo contenedores Docker...${NC}"
docker-compose build

echo ""
echo -e "${GREEN}3. Iniciando contenedores...${NC}"
docker-compose up -d

echo ""
echo -e "${GREEN}4. Esperando a que la base de datos esté lista...${NC}"
sleep 10

echo ""
echo -e "${GREEN}5. Instalando dependencias de Composer...${NC}"
docker-compose exec app composer install

echo ""
echo -e "${GREEN}6. Generando clave de aplicación...${NC}"
docker-compose exec app php artisan key:generate

echo ""
echo -e "${GREEN}7. Limpiando caché...${NC}"
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear

echo ""
echo -e "${GREEN}8. Ejecutando migraciones...${NC}"
docker-compose exec app php artisan migrate:fresh --seed

echo ""
echo -e "${GREEN}9. Configurando permisos...${NC}"
docker-compose exec app chmod -R 775 storage bootstrap/cache

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}   ✓ Instalación completada!${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo -e "${GREEN}La API está disponible en:${NC}"
echo -e "  - API: ${BLUE}http://localhost:8003/api${NC}"
echo -e "  - Health Check: ${BLUE}http://localhost:8003/api/health${NC}"
echo -e "  - PhpMyAdmin: ${BLUE}http://localhost:8080${NC}"
echo ""
echo -e "${GREEN}Para ver los logs:${NC}"
echo -e "  ${BLUE}docker-compose logs -f${NC}"
echo ""
echo -e "${GREEN}Para detener los contenedores:${NC}"
echo -e "  ${BLUE}docker-compose down${NC}"
echo ""
