<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Para API, nunca redirigir, siempre retornar null para que devuelva 401
        // Para rutas web también retornamos null ya que no tenemos ruta de login
        return null;
    }
}
