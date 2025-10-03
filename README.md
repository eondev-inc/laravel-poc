# Chain POC - Backend REST API

API REST construida con **Laravel 11**, **MySQL**, **Redis** y **Docker**.  
Implementa el **patrón Chain of Responsibility** para validación de login.

## 🏆 Características Destacadas

✅ **Arquitectura REST API** con autenticación Sanctum  
✅ **Patrón Chain of Responsibility** implementado en login  
✅ **Rate Limiting** con Redis (protección contra fuerza bruta)  
✅ **Docker Multi-Container** (App, Nginx, MySQL, Redis, phpMyAdmin)  
✅ **Tests Automatizados** con Pest PHP (33 tests pasando)  
✅ **Principios SOLID** aplicados  
✅ **Código limpio y mantenible**

## 🚀 Stack Tecnológico

- **Backend:** Laravel 11 (PHP 8.2)
- **Base de Datos:** MySQL 8.0
- **Cache & Queue:** Redis
- **Servidor Web:** Nginx
- **Autenticación:** Laravel Sanctum
- **Testing:** Pest PHP
- **Contenedores:** Docker & Docker Compose
- **Patrones de Diseño:** Chain of Responsibility, Builder

## 📋 Requisitos Previos

- Docker & Docker Compose instalados
- Git

## 🔧 Instalación

### Opción 1: Script Automático (Recomendado)

```bash
# Clonar el repositorio (si aplica)
git clone <repository-url>
cd chain-poc

# Dar permisos de ejecución al script
chmod +x setup.sh

# Ejecutar el script de instalación
./setup.sh
```

### Opción 2: Instalación Manual

```bash
# 1. Copiar archivo de configuración
cp .env.example .env

# 2. Construir e iniciar contenedores
docker-compose build
docker-compose up -d

# 3. Instalar dependencias
docker-compose exec app composer install

# 4. Generar clave de aplicación
docker-compose exec app php artisan key:generate

# 5. Ejecutar migraciones
docker-compose exec app php artisan migrate

# 6. Instalar Laravel Sanctum
docker-compose exec app composer require laravel/sanctum
docker-compose exec app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
docker-compose exec app php artisan migrate

# 7. Limpiar cachés
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
```

## 🌐 URLs de Acceso

## 🌐 URLs de Acceso

- **API Base:** http://localhost:8000/api
- **Health Check:** http://localhost:8000/api/health
- **PhpMyAdmin:** http://localhost:8080
  - Usuario: `laravel`
  - Contraseña: `secret`

## 📚 Endpoints Disponibles

### Públicos

#### Health Check
```http
GET /api/health
```

#### Registro
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

### Protegidos (Requieren Token)

#### Obtener Usuario Autenticado
```http
GET /api/user
Authorization: Bearer {token}
```

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

#### CRUD de Usuarios
```http
GET /api/users                    # Listar usuarios
POST /api/users                   # Crear usuario
GET /api/users/{id}              # Obtener usuario
PUT /api/users/{id}              # Actualizar usuario
DELETE /api/users/{id}           # Eliminar usuario
Authorization: Bearer {token}
```

## 🔑 Autenticación

La API utiliza **Laravel Sanctum** para autenticación basada en tokens.

1. Registra un usuario o inicia sesión
2. Obtén el `access_token` de la respuesta
3. Incluye el token en el header de tus peticiones:
   ```
   Authorization: Bearer {access_token}
   ```

## 🔗 Patrón Chain of Responsibility

El sistema de **login** implementa el patrón Chain of Responsibility con 6 validadores encadenados:

```
┌──────────────────────────────────┐
│  RequiredFieldsValidator         │ ← ¿email y password presentes?
└──────────────┬───────────────────┘
               ↓
┌──────────────────────────────────┐
│  EmailFormatValidator            │ ← ¿email válido?
└──────────────┬───────────────────┘
               ↓
┌──────────────────────────────────┐
│  PasswordLengthValidator         │ ← ¿8-100 caracteres?
└──────────────┬───────────────────┘
               ↓
┌──────────────────────────────────┐
│  RateLimitValidator (Redis)      │ ← ¿Menos de 5 intentos/5min?
└──────────────┬───────────────────┘
               ↓
┌──────────────────────────────────┐
│  UserExistsValidator (MySQL)     │ ← ¿Usuario existe?
└──────────────┬───────────────────┘
               ↓
┌──────────────────────────────────┐
│  PasswordVerificationValidator   │ ← ¿Contraseña correcta?
└──────────────┬───────────────────┘
               ↓
       [Generate Token]
```

### Ventajas del Patrón

- ✅ **Separación de Responsabilidades**: Cada validador una tarea
- ✅ **Fácil de Extender**: Agregar validadores sin modificar código existente
- ✅ **Testeable**: Tests independientes para cada validador
- ✅ **Mantenible**: Código limpio y organizado
- ✅ **Rate Limiting**: Protección contra ataques de fuerza bruta

### Documentación Completa

📚 [CHAIN_OF_RESPONSIBILITY.md](./CHAIN_OF_RESPONSIBILITY.md) - Documentación detallada del patrón  
📊 [CHAIN_IMPLEMENTATION_SUMMARY.md](./CHAIN_IMPLEMENTATION_SUMMARY.md) - Resumen ejecutivo

### Ejemplos de Validación

```bash
# Login exitoso
curl -X POST http://localhost:8003/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
# ✅ {"success":true,"access_token":"..."}

# Email inválido
curl -X POST http://localhost:8003/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"invalid-email","password":"password123"}'
# ❌ {"success":false,"message":"El formato del email es inválido"}

# Password muy corta
curl -X POST http://localhost:8003/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"123"}'
# ❌ {"success":false,"message":"La contraseña debe tener al menos 8 caracteres"}

# Demasiados intentos (después de 5 intentos fallidos)
curl -X POST http://localhost:8003/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"wrong"}'
# ❌ {"success":false,"message":"Demasiados intentos fallidos. Por favor intenta de nuevo en 5 minuto(s)"}
```

## 🐳 Comandos Docker

### Gestión de Contenedores
```bash
# Iniciar contenedores
docker-compose up -d

# Detener contenedores
docker-compose down

# Ver logs
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f app
```

### Ejecutar Comandos Artisan
```bash
# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Crear migración
docker-compose exec app php artisan make:migration create_table_name

# Crear controlador
docker-compose exec app php artisan make:controller Api/NombreController --api

# Crear modelo
docker-compose exec app php artisan make:model NombreModelo -m

# Limpiar cachés
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
```

### Composer
```bash
# Instalar dependencias
docker-compose exec app composer install

# Actualizar dependencias
docker-compose exec app composer update

# Agregar paquete
docker-compose exec app composer require vendor/package
```

### Base de Datos
```bash
# Ejecutar seeders
docker-compose exec app php artisan db:seed

# Refrescar base de datos
docker-compose exec app php artisan migrate:fresh

# Refrescar con seeders
docker-compose exec app php artisan migrate:fresh --seed
```

### Testing
```bash
# Ejecutar todos los tests
docker-compose exec app php artisan test

# Ejecutar tests con Pest
docker-compose exec app ./vendor/bin/pest

# Ejecutar tests con cobertura
docker-compose exec app ./vendor/bin/pest --coverage
```

## 📁 Estructura del Proyecto

```
chain-poc/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/           # Controladores de la API
│   │           ├── AuthController.php
│   │           └── UserController.php
│   └── Models/
│       └── User.php
├── routes/
│   ├── api.php                # Rutas de la API
│   └── web.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── docker/
│   ├── nginx/                 # Configuración de Nginx
│   ├── php/                   # Configuración de PHP
│   ├── mysql/                 # Configuración de MySQL
│   └── supervisor/            # Configuración de procesos
├── docker-compose.yml
├── Dockerfile
└── setup.sh
```

## 🔒 Variables de Entorno

Las principales variables de entorno se encuentran en el archivo `.env`:

```env
# Base de datos
DB_HOST=db
DB_DATABASE=chain_poc
DB_USERNAME=laravel
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## 🛠️ Desarrollo

### Agregar Nuevos Endpoints

1. Crear el controlador:
```bash
docker-compose exec app php artisan make:controller Api/MiController --api
```

2. Agregar rutas en `routes/api.php`:
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('recursos', MiController::class);
});
```

### Crear Migraciones

```bash
docker-compose exec app php artisan make:migration create_tabla_name
docker-compose exec app php artisan migrate
```

### Crear Seeders

```bash
docker-compose exec app php artisan make:seeder NombreSeeder
docker-compose exec app php artisan db:seed
```

## 🧪 Testing

El proyecto utiliza **Pest PHP** para testing:

```bash
# Ejecutar todos los tests
docker-compose exec app php artisan test

# Ejecutar un test específico
docker-compose exec app ./vendor/bin/pest tests/Feature/NombreTest.php

# Ejecutar con cobertura
docker-compose exec app ./vendor/bin/pest --coverage
```

## 📝 Mejores Prácticas

- Todas las respuestas de la API están en formato JSON
- Uso de Form Requests para validaciones complejas
- Middlewares para protección de rutas
- Cache con Redis para optimización
- Queues para procesos asíncronos
- Logging centralizado

## 🔄 Formato de Respuestas

Todas las respuestas de la API siguen el formato:

```json
{
  "success": true,
  "data": {...},
  "message": "Mensaje opcional"
}
```

## 📞 Soporte

Para problemas o preguntas, por favor abre un issue en el repositorio.

## 📄 Licencia

Este proyecto está bajo la licencia MIT.
