<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Services\ChainOfResponsibility\Contracts\ValidatorInterface;

/**
 * Builder para construir la cadena de validación
 * 
 * Facilita la creación de cadenas de validadores de forma fluida
 */
class ValidationChainBuilder
{
    private ?ValidatorInterface $firstValidator = null;
    private ?ValidatorInterface $lastValidator = null;
    
    /**
     * Agrega un validador a la cadena
     */
    public function add(ValidatorInterface $validator): self
    {
        if ($this->firstValidator === null) {
            $this->firstValidator = $validator;
            $this->lastValidator = $validator;
        } else {
            $this->lastValidator->setNext($validator);
            $this->lastValidator = $validator;
        }
        
        return $this;
    }
    
    /**
     * Construye y retorna la cadena de validación
     */
    public function build(): ?ValidatorInterface
    {
        return $this->firstValidator;
    }
}
