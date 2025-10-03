<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

test('authenticated user can list all users', function () {
    User::factory()->count(5)->create();

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'created_at', 'updated_at']
            ]
        ]);
});

test('authenticated user can create a new user', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email']
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@example.com'
    ]);
});

test('authenticated user can view a specific user', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson('/api/users/' . $this->user->id);

    $response->assertStatus(200)
        ->assertJson([
            'id' => $this->user->id,
            'email' => $this->user->email,
        ]);
});

test('authenticated user can update a user', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->putJson('/api/users/' . $this->user->id, [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user'
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'name' => 'Updated Name'
    ]);
});

test('authenticated user can delete a user', function () {
    $userToDelete = User::factory()->create();

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->deleteJson('/api/users/' . $userToDelete->id);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Usuario eliminado exitosamente'
        ]);

    $this->assertDatabaseMissing('users', [
        'id' => $userToDelete->id
    ]);
});

test('unauthenticated user cannot access user endpoints', function () {
    $response = $this->getJson('/api/users');

    $response->assertStatus(401);
});
