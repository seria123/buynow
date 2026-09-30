<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // A reverse proxy (ngrok on the laptop, or the cloud host's own router
        // in production) terminates TLS and then talks plain HTTP to this app.
        // The socket Laravel sees is therefore http://<internal host>:<internal
        // port>, while the browser sees https://<public hostname>. Without
        // trusting that proxy, Request::getScheme() returns http and
        // Request::getHost() returns the internal host, so every generated URL
        // (including @vite asset URLs, redirects, signed URLs and M-Pesa
        // callback URLs) is downgraded to http -> blocked as mixed content on an
        // HTTPS page.
        //
        // Trusting the proxy lets Symfony read X-Forwarded-Proto / -Host / -Port
        // from it, so URLs are generated with the real public scheme and
        // hostname. The scheme is NOT hardcoded: it is only taken from the
        // header when the request actually arrives from a trusted proxy, so a
        // direct local request to http://localhost:10000 (which carries no
        // X-Forwarded-* headers) is still generated as http and still works.
        //
        // '*' is Laravel's supported wildcard: it trusts only the address the
        // request actually came from (the immediate peer), not arbitrary
        // X-Forwarded-For chains. That is exactly the topology in production,
        // where the container port is reachable only from the platform's router,
        // and it removes the need to hardcode an IP that changes on every
        // provider. Locally under `network_mode: host` that peer is 127.0.0.1,
        // which is ngrok and the local browser alike.
        //
        // Override with TRUSTED_PROXIES in .env if the platform publishes its
        // proxy CIDR ranges, e.g. TRUSTED_PROXIES="10.0.0.0/8,172.16.0.0/12".
        // Use `php artisan config:clear` after changing it.
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', '*'),
            // Explicit list rather than relying on the default so the intent is
            // recorded next to the proxy list.
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PREFIX,
        );

        // Exclude cart routes from CSRF verification (for debugging)
        $middleware->validateCsrfTokens(except: [
            'cart/*',
            'mpesa/callback',
            'api/mpesa/callback',
            'm-pesa/validation',
            'm-pesa/confirmation',
        ]);
        
        $middleware->web(append: [
            HandleInertiaRequests::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
