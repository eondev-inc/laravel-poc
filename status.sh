#!/bin/bash

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   Chain POC - Estado del Sistema      ${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Verificar si Docker está corriendo
if ! docker info > /dev/null 2>&1; then
    echo -e "${YELLOW}⚠ Docker no está corriendo${NC}"
    exit 1
fi

echo -e "${GREEN}🐳 Docker Status:${NC}"
docker-compose ps
echo ""

echo -e "${GREEN}📊 Estadísticas de Contenedores:${NC}"
docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}" chain-poc-app chain-poc-nginx chain-poc-db chain-poc-redis 2>/dev/null || echo "Contenedores no están corriendo"
echo ""

echo -e "${GREEN}🌐 URLs Disponibles:${NC}"
echo -e "  API: ${BLUE}http://localhost:8003/api${NC}"
echo -e "  Health Check: ${BLUE}http://localhost:8003/api/health${NC}"
echo -e "  PhpMyAdmin: ${BLUE}http://localhost:8003${NC}"
echo ""

echo -e "${GREEN}📝 Logs Recientes:${NC}"
docker-compose logs --tail=10 app 2>/dev/null || echo "No hay logs disponibles"
echo ""

echo -e "${GREEN}💾 Base de Datos:${NC}"
docker-compose exec db mysql -u${DB_USERNAME:-laravel} -p${DB_PASSWORD:-secret} -e "SELECT COUNT(*) as user_count FROM users;" ${DB_DATABASE:-chain_poc} 2>/dev/null || echo "No se pudo conectar a la base de datos"
echo ""

echo -e "${BLUE}========================================${NC}"
