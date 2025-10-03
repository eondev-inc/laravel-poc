<?php

declare(strict_types=1);

namespace App\Services\Auth\Validators;

use App\Services\ChainOfResponsibility\AbstractValidator;
use App\Services\ChainOfResponsibility\ValidationResult;

/**
 * Validador de formato de email
 * 
 * Verifica que el email tenga un formato válido
 */
class EmailFormatValidator extends AbstractValidator
{
    protected function check(array $data): ValidationResult
    {
        $email = $data['email'] ?? '';
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ValidationResult::failure(
                'El formato del email es inválido'
            );
        }
        
        return ValidationResult::success($data);
    }
}
