<?php

use App\Exceptions\Handler;
use App\Http\Middleware\CustomAuth;
use App\Http\Middleware\CustomAuthenticate;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Laratrust\Middleware\Permission;
use Illuminate\Foundation\Application;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ValidateUUID;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated'], 401)
                : null;
        });
    //    ->appendToGroup('api', ValidateUUID::class);
    })
    ->withSingletons([
            ExceptionHandler::class => Handler::class,
        ])
    ->withExceptions(function (Exceptions $exceptions) {

    })->create();

