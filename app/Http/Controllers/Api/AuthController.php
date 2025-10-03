<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\LoginService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private LoginService $loginService
    ) {}

    /**
     * Registrar un nuevo usuario
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    /**
     * Iniciar sesión usando Chain of Responsibility
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        // Ejecutar la cadena de validación a través del servicio
        $result = $this->loginService->login([
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        // Si la validación falló, retornar error
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error']
            ], 401);
        }

        // Login exitoso
        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'user' => $result['user']
        ]);
    }

    /**
     * Cerrar sesión
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }
}
