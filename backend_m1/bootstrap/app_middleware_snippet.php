<?php
// Gộp phần alias này vào bootstrap/app.php của Laravel 11.
use Illuminate\Foundation\Configuration\Middleware;

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureRole::class,
    ]);
})
