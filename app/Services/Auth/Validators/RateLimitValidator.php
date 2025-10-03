<?php

declare(strict_types=1);

namespace App\Services\Auth\Validators;

use App\Services\ChainOfResponsibility\AbstractValidator;
use App\Services\ChainOfResponsibility\ValidationResult;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Validador de límite de intentos (Rate Limiting)
 * 
 * Protege contra ataques de fuerza bruta limitando los intentos de login
 */
class RateLimitValidator extends AbstractValidator
{
    public function __construct(
        private int $maxAttempts = 5,
        private int $decayMinutes = 5
    ) {}
    
    protected function check(array $data): ValidationResult
    {
        $email = $data['email'];
        $key = $this->throttleKey($email);
        
        // Verificar si ha excedido el límite de intentos
        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            
            return ValidationResult::failure(
                "Demasiados intentos fallidos. Por favor intenta de nuevo en {$minutes} minuto(s)"
            );
        }
        
        // Incrementar el contador de intentos
        RateLimiter::hit($key, $this->decayMinutes * 60);
        
        return ValidationResult::success($data);
    }
    
    /**
     * Genera la clave única para el rate limiting
     */
    private function throttleKey(string $email): string
    {
        return 'login:' . strtolower($email);
    }
    
    /**
     * Limpia los intentos después de un login exitoso
     * 
     * Este método debe ser llamado por el servicio de login
     */
    public static function clearAttempts(string $email): void
    {
        $key = 'login:' . strtolower($email);
        RateLimiter::clear($key);
    }
}
