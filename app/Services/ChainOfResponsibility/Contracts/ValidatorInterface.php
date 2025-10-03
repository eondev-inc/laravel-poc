<?php

declare(strict_types=1);

namespace App\Services\ChainOfResponsibility\Contracts;

use App\Services\ChainOfResponsibility\ValidationResult;

/**
 * Interfaz para los validadores del patrón Chain of Responsibility
 */
interface ValidatorInterface
{
    /**
     * Establece el siguiente validador en la cadena
     */
    public function setNext(ValidatorInterface $validator): ValidatorInterface;
    
    /**
     * Valida los datos y continúa la cadena si es exitoso
     */
    public function validate(array $data): ValidationResult;
}
