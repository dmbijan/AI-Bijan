<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi(); // SPA guna sesi cookie + CSRF (Sanctum)

        // AUDIT BAB 90 (S1): rate limiting global — perlindungan abuse/brute-force
        $middleware->api(prepend: [
            \App\Http\Middleware\ThrottleRequests::class,
        ]);

        // AUDIT BAB 90 (S2/S3/S4): security headers + CSP
        $middleware->append(\App\Http\Middleware\SecureHeaders::class);

        // ⚠️ PENTING: Livewire memanggil `/livewire/update` melalui AJAX dan
        // laluan itu TIDAK melalui `authMiddleware` panel Filament. Tanpa
        // middleware ini dalam kumpulan `web`, widget/resource Livewire
        // berjalan TANPA TenantContext → semua query pulang 0 rekod dan
        // KPI memaparkan "Tiada Syarikat Aktif".
        $middleware->web(append: [
            \App\Http\Middleware\ResolveFilamentTenant::class,
        ]);

        $middleware->alias([
            'tenant' => \App\Http\Middleware\ResolveTenant::class,
            'perm' => \App\Http\Middleware\RequirePermission::class,
            'platform' => \App\Http\Middleware\PlatformAdmin::class,
            'throttle.api' => \App\Http\Middleware\ThrottleRequests::class,
            'idempotent' => \App\Http\Middleware\IdempotencyMiddleware::class,
            'module' => \App\Http\Middleware\RequireModule::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
