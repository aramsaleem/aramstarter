<?php

use App\Http\Middleware\HandleImpersonation;
use App\Http\Middleware\PreventDuringImpersonation;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        $middleware->web(append: [
            // Signs out every other session (and "remember me" cookie) when the password changes.
            AuthenticateSession::class,
            HandleImpersonation::class,
            SetLocale::class,
        ]);

        $middleware->alias([
            'not-impersonating' => PreventDuringImpersonation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
