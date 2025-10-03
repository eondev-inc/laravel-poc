#!/bin/bash

# Script para detener y limpiar Docker

echo "🛑 Deteniendo contenedores..."
docker-compose down

echo "🧹 Limpiando volúmenes (esto eliminará los datos de la base de datos)..."
docker-compose down -v

echo "✅ Limpieza completada"
