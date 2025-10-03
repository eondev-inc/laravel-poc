<?php

declare(strict_types=1);

namespace App\Services\ChainOfResponsibility;

use App\Services\ChainOfResponsibility\Contracts\ValidatorInterface;

/**
 * Clase base abstracta para validadores del patrón Chain of Responsibility
 * 
 * Implementa la lógica común para encadenar validadores
 */
abstract class AbstractValidator implements ValidatorInterface
{
    private ?ValidatorInterface $nextValidator = null;
    
    /**
     * Establece el siguiente validador en la cadena
     */
    public function setNext(ValidatorInterface $validator): ValidatorInterface
    {
        $this->nextValidator = $validator;
        return $validator;
    }
    
    /**
     * Valida los datos y continúa la cadena si es exitoso
     */
    public function validate(array $data): ValidationResult
    {
        // Ejecuta la validación específica del validador
        $result = $this->check($data);
        
        // Si falla, detiene la cadena y retorna el error
        if ($result->isFailed()) {
            return $result;
        }
        
        // Si hay siguiente validador, continúa la cadena con los datos actualizados
        if ($this->nextValidator !== null) {
            // Merge de datos acumulados en la cadena
            $mergedData = array_merge($data, $result->data);
            return $this->nextValidator->validate($mergedData);
        }
        
        // Si no hay más validadores, retorna el resultado exitoso
        return $result;
    }
    
    /**
     * Método abstracto que cada validador debe implementar
     * 
     * @param array $data Datos a validar
     * @return ValidationResult Resultado de la validación
     */
    abstract protected function check(array $data): ValidationResult;
}
