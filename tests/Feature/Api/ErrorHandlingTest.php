<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que endpoint no encontrado devuelve JSON 404
     */
    public function test_endpoint_not_found_returns_json_404(): void
    {
        $response = $this->getJson('/api/non-existent-endpoint');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Endpoint no encontrado',
            ]);
    }

    /**
     * Test que método HTTP no permitido devuelve JSON 405
     */
    public function test_method_not_allowed_returns_json_405(): void
    {
        $response = $this->getJson('/api/login'); // Login requiere POST, no GET

        $response->assertStatus(405)
            ->assertJson([
                'success' => false,
                'message' => 'Método HTTP no permitido',
            ]);
    }

    /**
     * Test que validación fallida devuelve JSON 422
     */
    public function test_validation_error_returns_json_422(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            // Falta email y password
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Error de validación',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /**
     * Test que autenticación fallida devuelve JSON 401
     */
    public function test_unauthenticated_returns_json_401(): void
    {
        $response = $this->getJson('/api/user'); // Requiere autenticación

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'No autenticado',
            ]);
    }

    /**
     * Test que modelo no encontrado devuelve JSON 404
     */
    public function test_model_not_found_returns_json_404(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->getJson('/api/users/99999', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Recurso no encontrado',
            ]);
    }

    /**
     * Test que todas las respuestas de error siguen el formato consistente
     */
    public function test_error_responses_follow_consistent_format(): void
    {
        // Test 404
        $response = $this->getJson('/api/non-existent');
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->assertFalse($response->json('success'));

        // Test 422
        $response = $this->postJson('/api/register', []);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors',
        ]);
        $this->assertFalse($response->json('success'));

        // Test 401
        $response = $this->getJson('/api/user');
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->assertFalse($response->json('success'));
    }
}
