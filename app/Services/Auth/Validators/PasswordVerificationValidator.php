<?php

declare(strict_types=1);

namespace App\Services\Auth\Validators;

use App\Services\ChainOfResponsibility\AbstractValidator;
use App\Services\ChainOfResponsibility\ValidationResult;
use Illuminate\Support\Facades\Hash;

/**
 * Validador de verificación de contraseña
 * 
 * Verifica que la contraseña proporcionada coincida con la almacenada
 */
class PasswordVerificationValidator extends AbstractValidator
{
    protected function check(array $data): ValidationResult
    {
        $password = $data['password'];
        $user = $data['user'] ?? null;
        
        if (!$user) {
            return ValidationResult::failure(
                'Error interno: Usuario no encontrado en la cadena de validación'
            );
        }
        
        if (!Hash::check($password, $user->password)) {
            return ValidationResult::failure(
                'Las credenciales proporcionadas son incorrectas'
            );
        }
        
        return ValidationResult::success($data);
    }
}
