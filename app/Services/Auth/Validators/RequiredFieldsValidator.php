<?php

declare(strict_types=1);

namespace App\Services\Auth\Validators;

use App\Services\ChainOfResponsibility\AbstractValidator;
use App\Services\ChainOfResponsibility\ValidationResult;

/**
 * Validador de campos requeridos
 * 
 * Verifica que todos los campos requeridos estén presentes y no vacíos
 */
class RequiredFieldsValidator extends AbstractValidator
{
    public function __construct(
        private array $requiredFields = ['email', 'password']
    ) {}
    
    protected function check(array $data): ValidationResult
    {
        foreach ($this->requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return ValidationResult::failure(
                    "El campo '{$field}' es requerido"
                );
            }
        }
        
        return ValidationResult::success($data);
    }
}
