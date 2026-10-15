<?php

use App\Domain\Exceptions\DomainException;
use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\RedirectBasedOnRole;
use App\Http\Middleware\SecureHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withEvents(discover: false)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases for RBAC
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Request ID for tracing (applied first)
        $middleware->append(AssignRequestId::class);

        // Secure headers middleware (applied globally)
        $middleware->append(SecureHeaders::class);

        // Register custom middleware
        $middleware->append(RedirectBasedOnRole::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Gestion des exceptions métier du domaine
        $exceptions->renderable(function (DomainException $e, Request $request) {
            // Log structuré de l'exception
            Log::warning('Domain exception occurred', $e->toLogContext());

            // Réponse API JSON
            if ($request->expectsJson()) {
                return response()->json($e->toArray(), 422);
            }

            // Réponse web avec flash message
            session()->flash('error', $e->getUserMessage());

            return back()->withInput();
        });
    })->create();
