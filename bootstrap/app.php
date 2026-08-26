<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'approved' => \App\Http\Middleware\EnsureUserIsApproved::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'subscription.level' => \App\Http\Middleware\SubscriptionLevel::class,
            'documents.approved' => \App\Http\Middleware\EnsureDocumentsApproved::class,
        ]);

        // Un visiteur non connecte qui vise une URL /admin est renvoye vers le
        // login admin, pas vers le login des utilisateurs du site.
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('admin', 'admin/*')
                ? route('admin.login')
                : route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
