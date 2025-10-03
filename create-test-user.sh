#!/bin/bash

# Script para crear un usuario de prueba

echo "🔧 Creando usuario de prueba..."

docker-compose exec app php artisan tinker --execute="
\$user = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('admin123'),
]);
echo 'Usuario creado:' . PHP_EOL;
echo 'Email: admin@example.com' . PHP_EOL;
echo 'Password: admin123' . PHP_EOL;
\$token = \$user->createToken('admin-token')->plainTextToken;
echo 'Token: ' . \$token . PHP_EOL;
"

echo "✅ Usuario de prueba creado exitosamente"
