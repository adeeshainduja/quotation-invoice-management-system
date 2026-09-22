<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', EnsureActiveUser::class);

        $middleware->alias([
            'permission' => CheckPermission::class,
            'admin' => EnsureAdmin::class,
        ]);

    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
