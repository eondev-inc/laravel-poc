<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

test('endpoint login funciona con Chain of Responsibility', function () {
    // Crear usuario
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'access_token',
            'token_type',
            'user' => ['id', 'name', 'email']
        ])
        ->assertJson([
            'success' => true,
            'token_type' => 'Bearer'
        ]);
});

test('endpoint login retorna error con email faltante', function () {
    $response = postJson('/api/login', [
        'password' => 'password123'
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false
        ])
        ->assertJsonPath('message', fn($message) => str_contains($message, 'email'));
});

test('endpoint login retorna error con password faltante', function () {
    $response = postJson('/api/login', [
        'email' => 'test@example.com'
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false
        ])
        ->assertJsonPath('message', fn($message) => str_contains($message, 'password'));
});

test('endpoint login retorna error con email inválido', function () {
    $response = postJson('/api/login', [
        'email' => 'invalid-email',
        'password' => 'password123'
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false
        ]);
});

test('endpoint login retorna error con credenciales incorrectas', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword'
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false
        ]);
});

test('endpoint login bloquea después de múltiples intentos fallidos', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    // Hacer 5 intentos fallidos
    for ($i = 0; $i < 5; $i++) {
        postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
    }

    // El 6to intento debe ser bloqueado
    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword'
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('message', fn($message) => str_contains($message, 'Demasiados intentos'));
});
