<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'api/auth/*'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejar excepciones de Spatie Permission para API
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes los permisos necesarios para realizar esta acción.',
                    'error' => 'Forbidden',
                    'required_permission' => $e->getRequiredPermissions(),
                ], 403);
            }
        });

        // Manejar excepciones de autenticación
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No estás autenticado. Por favor inicia sesión.',
                    'error' => 'Unauthenticated',
                ], 401);
            }
        });

        // Manejar excepciones de validación
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los datos proporcionados no son válidos.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Manejar errores 404
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El recurso solicitado no fue encontrado.',
                    'error' => 'Not Found',
                ], 404);
            }
        });

        // Manejar errores 500 (excepciones generales)
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // En producción no exponer detalles del error
                $message = config('app.debug')
                    ? $e->getMessage()
                    : 'Ha ocurrido un error en el servidor.';

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error' => 'Internal Server Error',
                ], 500);
            }
        });
    })->create();
