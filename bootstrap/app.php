<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejo específico de AuthenticationException para API
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autenticado. Por favor, inicia sesión.',
                ], 401);
            }
        });

        // Manejo de excepciones para API - Siempre devolver JSON
        $exceptions->render(function (Throwable $e, Request $request) {
            // Solo aplicar a rutas API
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = 500;
                $message = 'Error interno del servidor';
                $errors = null;

                // ValidationException - Errores de validación (422)
                if ($e instanceof ValidationException) {
                    $status = 422;
                    $message = 'Error de validación';
                    $errors = $e->errors();
                }
                // AuthorizationException - No autorizado (403)
                elseif ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                    $status = 403;
                    $message = 'No tienes permisos para realizar esta acción';
                }
                // ModelNotFoundException - Modelo no encontrado (404)
                elseif ($e instanceof ModelNotFoundException) {
                    $status = 404;
                    $message = 'Recurso no encontrado';
                }
                // NotFoundHttpException - Ruta no encontrada (404)
                elseif ($e instanceof NotFoundHttpException) {
                    $status = 404;
                    $message = 'Endpoint no encontrado';
                }
                // MethodNotAllowedHttpException - Método HTTP no permitido (405)
                elseif ($e instanceof MethodNotAllowedHttpException) {
                    $status = 405;
                    $message = 'Método HTTP no permitido';
                }
                // HttpException - Otras excepciones HTTP
                elseif ($e instanceof HttpException) {
                    $status = $e->getStatusCode();
                    $message = $e->getMessage() ?: 'Error en la petición';
                }
                // Otras excepciones - 500
                else {
                    $status = 500;
                    $message = config('app.debug') 
                        ? $e->getMessage() 
                        : 'Error interno del servidor';
                }

                // Construir respuesta JSON consistente
                $response = [
                    'success' => false,
                    'message' => $message,
                ];

                if ($errors) {
                    $response['errors'] = $errors;
                }

                // En modo debug, agregar información adicional útil
                if (config('app.debug')) {
                    $response['debug'] = [
                        'exception' => get_class($e),
                        'file' => basename($e->getFile()),
                        'line' => $e->getLine(),
                    ];
                }

                return response()->json($response, $status);
            }
        });
    })->create();
