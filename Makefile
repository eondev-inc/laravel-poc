.PHONY: help build up down restart logs shell test migrate fresh seed install clean

# Colores
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
RESET  := $(shell tput -Txterm sgr0)

help: ## Muestra esta ayuda
	@echo ''
	@echo 'Uso:'
	@echo '  ${YELLOW}make${RESET} ${GREEN}<target>${RESET}'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} { \
		if (/^[a-zA-Z_-]+:.*?##.*$$/) {printf "    ${YELLOW}%-20s${GREEN}%s${RESET}\n", $$1, $$2} \
		else if (/^## .*$$/) {printf "  ${CYAN}%s${RESET}\n", substr($$1,4)} \
		}' $(MAKEFILE_LIST)

## Setup
install: ## Instalación completa del proyecto
	@chmod +x setup.sh
	@./setup.sh

build: ## Construir las imágenes de Docker
	docker-compose build

## Docker
up: ## Levantar los contenedores
	docker-compose up -d

down: ## Detener los contenedores
	docker-compose down

restart: down up ## Reiniciar los contenedores

logs: ## Ver logs de todos los contenedores
	docker-compose logs -f

logs-app: ## Ver logs de la aplicación
	docker-compose logs -f app

logs-nginx: ## Ver logs de Nginx
	docker-compose logs -f nginx

logs-db: ## Ver logs de la base de datos
	docker-compose logs -f db

## Aplicación
shell: ## Entrar al contenedor de la aplicación
	docker-compose exec app bash

shell-db: ## Entrar al contenedor de base de datos
	docker-compose exec db mysql -u${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE}

## Base de datos
migrate: ## Ejecutar migraciones
	docker-compose exec app php artisan migrate

migrate-fresh: ## Refrescar base de datos (CUIDADO: Elimina todos los datos)
	docker-compose exec app php artisan migrate:fresh

seed: ## Ejecutar seeders
	docker-compose exec app php artisan db:seed

fresh: ## Refrescar base de datos y ejecutar seeders
	docker-compose exec app php artisan migrate:fresh --seed

## Testing
test: ## Ejecutar todos los tests
	docker-compose exec app php artisan test

pest: ## Ejecutar tests con Pest
	docker-compose exec app ./vendor/bin/pest

pest-coverage: ## Ejecutar tests con cobertura
	docker-compose exec app ./vendor/bin/pest --coverage

## Laravel Artisan
cache-clear: ## Limpiar todos los cachés
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

optimize: ## Optimizar la aplicación para producción
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

key-generate: ## Generar clave de aplicación
	docker-compose exec app php artisan key:generate

queue-work: ## Ejecutar el worker de colas
	docker-compose exec app php artisan queue:work

tinker: ## Abrir Laravel Tinker
	docker-compose exec app php artisan tinker

## Composer
composer-install: ## Instalar dependencias de Composer
	docker-compose exec app composer install

composer-update: ## Actualizar dependencias de Composer
	docker-compose exec app composer update

composer-dump: ## Regenerar autoload de Composer
	docker-compose exec app composer dump-autoload

## Limpieza
clean: ## Limpiar contenedores y volúmenes (CUIDADO: Elimina todos los datos)
	@chmod +x stop.sh
	@./stop.sh

clean-all: clean ## Limpiar todo incluyendo imágenes
	docker-compose down -v --rmi all

## Información
ps: ## Mostrar estado de los contenedores
	docker-compose ps

stats: ## Mostrar estadísticas de recursos de los contenedores
	docker stats

## Desarrollo
artisan: ## Ejecutar comando de Artisan (uso: make artisan cmd="make:controller NombreController")
	docker-compose exec app php artisan $(cmd)

composer: ## Ejecutar comando de Composer (uso: make composer cmd="require package")
	docker-compose exec app composer $(cmd)
