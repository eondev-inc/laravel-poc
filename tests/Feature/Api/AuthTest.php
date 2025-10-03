<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('health check endpoint returns successful response', function () {
    $response = $this->get('/api/health');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'ok',
            'service' => 'Chain POC API'
        ]);
});

test('user can register with valid data', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'access_token',
            'token_type',
            'user' => ['id', 'name', 'email']
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com'
    ]);
});

test('user cannot register with invalid email', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'access_token',
            'token_type',
            'user'
        ]);
});

test('user cannot login with invalid credentials', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false
        ])
        ->assertJsonStructure(['success', 'message']);
});

test('authenticated user can access protected routes', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJson([
            'id' => $user->id,
            'email' => $user->email,
        ]);
});

test('unauthenticated user cannot access protected routes', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Sesión cerrada exitosamente'
        ]);
});
