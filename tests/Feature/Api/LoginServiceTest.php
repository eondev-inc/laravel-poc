<?php

use App\Models\User;
use App\Services\Auth\LoginService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Limpiar rate limiter antes de cada test
    RateLimiter::clear('login:test@example.com');
});

test('login exitoso con credenciales válidas', function () {
    // Crear usuario de prueba
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    $loginService = new LoginService();
    
    $result = $loginService->login([
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    expect($result['success'])->toBeTrue()
        ->and($result)->toHaveKey('access_token')
        ->and($result)->toHaveKey('user')
        ->and($result['user']->email)->toBe('test@example.com');
});

test('login falla cuando email no está presente', function () {
    $loginService = new LoginService();
    
    $result = $loginService->login([
        'password' => 'password123'
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain("El campo 'email' es requerido");
});

test('login falla cuando password no está presente', function () {
    $loginService = new LoginService();
    
    $result = $loginService->login([
        'email' => 'test@example.com'
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain("El campo 'password' es requerido");
});

test('login falla con formato de email inválido', function () {
    $loginService = new LoginService();
    
    $result = $loginService->login([
        'email' => 'invalid-email',
        'password' => 'password123'
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('formato del email es inválido');
});

test('login falla con contraseña muy corta', function () {
    $loginService = new LoginService();
    
    $result = $loginService->login([
        'email' => 'test@example.com',
        'password' => '123'
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('debe tener al menos 8 caracteres');
});

test('login falla cuando el usuario no existe', function () {
    $loginService = new LoginService();
    
    $result = $loginService->login([
        'email' => 'noexiste@example.com',
        'password' => 'password123'
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('credenciales proporcionadas son incorrectas');
});

test('login falla con contraseña incorrecta', function () {
    // Crear usuario
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    $loginService = new LoginService();
    
    $result = $loginService->login([
        'email' => 'test@example.com',
        'password' => 'wrongpassword'
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('credenciales proporcionadas son incorrectas');
});

test('rate limiter bloquea después de múltiples intentos fallidos', function () {
    // Crear usuario
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    $loginService = new LoginService();

    // Hacer 5 intentos fallidos
    for ($i = 0; $i < 5; $i++) {
        $loginService->login([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
    }

    // El 6to intento debe ser bloqueado por rate limiter
    $result = $loginService->login([
        'email' => 'test@example.com',
        'password' => 'wrongpassword'
    ]);

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('Demasiados intentos fallidos');
});

test('rate limiter se limpia después de login exitoso', function () {
    // Crear usuario
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    $loginService = new LoginService();

    // Hacer 4 intentos fallidos
    for ($i = 0; $i < 4; $i++) {
        $loginService->login([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
    }

    // Ahora hacer login exitoso
    $result = $loginService->login([
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    expect($result['success'])->toBeTrue();

    // Verificar que el rate limiter fue limpiado haciendo otro login
    $result2 = $loginService->login([
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    expect($result2['success'])->toBeTrue();
});

test('login genera token de acceso válido', function () {
    // Crear usuario
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    $loginService = new LoginService();
    
    $result = $loginService->login([
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    expect($result['success'])->toBeTrue()
        ->and($result['access_token'])->toBeString()
        ->and($result['token_type'])->toBe('Bearer')
        ->and(strlen($result['access_token']))->toBeGreaterThan(20);
});

test('login elimina tokens anteriores', function () {
    // Crear usuario
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    // Crear un token anterior
    $user->createToken('old_token');
    expect($user->tokens()->count())->toBe(1);

    $loginService = new LoginService();
    
    $result = $loginService->login([
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    // Verificar que solo existe el nuevo token
    expect($result['success'])->toBeTrue()
        ->and($user->fresh()->tokens()->count())->toBe(1);
});
