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
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'login',
            'login/*',
            'admin/login',
            'admin/login/*',
            'register',
            'register/*',
            'logout',
            'logout/*',
            'admin/logout',
            'admin/logout/*',
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'approved' => \App\Http\Middleware\ApprovedUserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => 'CSRF token mismatch. Please reload.'], 419);
                }

                if ($request->is('logout') || $request->is('admin/logout')) {
                    \Illuminate\Support\Facades\Auth::logout();
                    return redirect()->route('login');
                }

                return redirect()->route('login');
            }
        });

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'CSRF token mismatch. Please reload.'], 419);
            }

            if ($request->is('logout') || $request->is('admin/logout')) {
                \Illuminate\Support\Facades\Auth::logout();
                return redirect()->route('login');
            }

            return redirect()->route('login');
        });
    })->create();
