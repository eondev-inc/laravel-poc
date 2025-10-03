<?php

declare(strict_types=1);

namespace App\Services\Auth\Validators;

use App\Services\ChainOfResponsibility\AbstractValidator;
use App\Services\ChainOfResponsibility\ValidationResult;

/**
 * Validador de longitud de contraseña
 * 
 * Verifica que la contraseña cumpla con los requisitos de longitud
 */
class PasswordLengthValidator extends AbstractValidator
{
    public function __construct(
        private int $minLength = 8,
        private int $maxLength = 100
    ) {}
    
    protected function check(array $data): ValidationResult
    {
        $password = $data['password'] ?? '';
        $length = strlen($password);
        
        if ($length < $this->minLength) {
            return ValidationResult::failure(
                "La contraseña debe tener al menos {$this->minLength} caracteres"
            );
        }
        
        if ($length > $this->maxLength) {
            return ValidationResult::failure(
                "La contraseña no puede exceder {$this->maxLength} caracteres"
            );
        }
        
        return ValidationResult::success($data);
    }
}
