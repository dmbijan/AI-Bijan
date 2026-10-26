<!DOCTYPE html>
<html lang="ms" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Log Masuk') — AI Bijan</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}?v={{ @filemtime(public_path('assets/app.css')) }}">
</head>
<body>
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="brand-mark" aria-hidden="true">B</div>
            <div>
                <div style="font-weight:700;font-size:var(--text-md)">AI Bijan</div>
                <div class="text-xs text-muted">Every Business Deserves a Boardroom</div>
            </div>
            <button class="icon-btn" data-theme-toggle aria-label="Tukar tema" style="margin-left:auto">☀</button>
        </div>

        @yield('content')
    </div>
</div>
<script src="{{ asset('assets/app.js') }}?v={{ @filemtime(public_path('assets/app.js')) }}"></script>
@stack('scripts')
</body>
</html>
