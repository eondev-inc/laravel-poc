<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Services\Auth\Validators\EmailFormatValidator;
use App\Services\Auth\Validators\PasswordLengthValidator;
use App\Services\Auth\Validators\PasswordVerificationValidator;
use App\Services\Auth\Validators\RequiredFieldsValidator;
use App\Services\Auth\Validators\UserExistsValidator;
use App\Services\ChainOfResponsibility\Contracts\ValidatorInterface;

/**
 * Servicio de autenticación usando el patrón Chain of Responsibility
 * 
 * Maneja el proceso de login utilizando una cadena de validadores
 */
class LoginService
{
    private ValidatorInterface $validationChain;
    
    public function __construct(?ValidatorInterface $validationChain = null)
    {
        $this->validationChain = $validationChain ?? $this->createDefaultChain();
    }
    
    /**
     * Crea la cadena de validación predeterminada
     */
    private function createDefaultChain(): ValidatorInterface
    {
        return (new ValidationChainBuilder())
            ->add(new RequiredFieldsValidator(['email', 'password']))
            ->add(new EmailFormatValidator())
            ->add(new PasswordLengthValidator(8, 100))
            ->add(new UserExistsValidator())
            ->add(new PasswordVerificationValidator())
            ->build();
    }
    
    /**
     * Procesa el login del usuario
     * 
     * @param array $credentials Credenciales del usuario
     * @return array Resultado del login
     */
    public function login(array $credentials): array
    {
        // Ejecutar la cadena de validación
        $result = $this->validationChain->validate($credentials);
        
        if ($result->isFailed()) {
            return [
                'success' => false,
                'error' => $result->errorMessage
            ];
        }
        
        // Login exitoso
        $user = $result->data['user'];
        
        // Eliminar tokens anteriores (opcional)
        $user->tokens()->delete();
        
        // Generar nuevo token de autenticación
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return [
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ];
    }
    
    /**
     * Obtiene la cadena de validación actual
     * 
     * Útil para testing
     */
    public function getValidationChain(): ValidatorInterface
    {
        return $this->validationChain;
    }
}
