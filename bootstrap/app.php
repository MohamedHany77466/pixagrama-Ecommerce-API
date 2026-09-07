<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use App\Http\Middleware\CheckPermission;

use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
          $middleware->append(\App\Http\Middleware\SetLocale::class);
         $middleware->alias([
            'isAdmin' => AdminMiddleware::class,
            'isCustomer' => CustomerMiddleware::class,
            'permission' => CheckPermission::class,            
        ]);
    })
->withExceptions(function (Exceptions $exceptions): void {

    $exceptions->render(function (ValidationException $e, $request) {
        return response()->json([
            'success' => false,
            'message' => __('messages.validation'),
            'errors' => $e->errors(),
        ], 422);
    });

    $exceptions->render(function (NotFoundHttpException $e, $request) {
        return response()->json([
            'success' => false,
           'message' => __('messages.route_not_found'),
        ], 404);
    });

    $exceptions->render(function (Throwable $e, $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
               'message' => $e->getMessage() ?: __('messages.server_error'),
            ], 500);
        }
    });

})->create();