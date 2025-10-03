<?php

declare(strict_types=1);

namespace App\Services\ChainOfResponsibility;

/**
 * Resultado de la validación
 * 
 * DTO inmutable que representa el resultado de una validación
 */
readonly class ValidationResult
{
    public function __construct(
        public bool $isValid,
        public ?string $errorMessage = null,
        public array $data = []
    ) {}
    
    /**
     * Crea un resultado exitoso
     */
    public static function success(array $data = []): self
    {
        return new self(true, null, $data);
    }
    
    /**
     * Crea un resultado fallido
     */
    public static function failure(string $message): self
    {
        return new self(false, $message);
    }
    
    /**
     * Verifica si la validación fue exitosa
     */
    public function isSuccessful(): bool
    {
        return $this->isValid;
    }
    
    /**
     * Verifica si la validación falló
     */
    public function isFailed(): bool
    {
        return !$this->isValid;
    }
}
