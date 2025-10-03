<?php

declare(strict_types=1);

namespace App\Services\Auth\Validators;

use App\Models\User;
use App\Services\ChainOfResponsibility\AbstractValidator;
use App\Services\ChainOfResponsibility\ValidationResult;

/**
 * Validador de existencia de usuario
 * 
 * Verifica que el usuario exista en la base de datos
 */
class UserExistsValidator extends AbstractValidator
{
    protected function check(array $data): ValidationResult
    {
        $email = $data['email'];
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return ValidationResult::failure(
                'Las credenciales proporcionadas son incorrectas'
            );
        }
        
        // Agregar el usuario a los datos para los siguientes validadores
        return ValidationResult::success(array_merge($data, [
            'user' => $user
        ]));
    }
}
