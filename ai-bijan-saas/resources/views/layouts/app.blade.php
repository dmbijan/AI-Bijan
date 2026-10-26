<!DOCTYPE html>
<html lang="ms" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Papan Pemuka') — AI Bijan</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}?v={{ @filemtime(public_path('assets/app.css')) }}">
    @stack('styles')
</head>
<body>
<div class="app" data-sidebar="expanded">
    <div class="sidebar-scrim" hidden></div>

    <aside class="sidebar" aria-label="Navigasi utama">
        <div class="sidebar-head">
            <div class="brand-mark" aria-hidden="true">B</div>
            <span class="brand-text">AI Bijan</span>
        </div>

        <nav class="sidebar-nav">
            @php
                // Navigasi mengikut kumpulan produk (BAB 12)
                $groups = [
                    'Utama' => [
                        ['Papan Pemuka', '/app', '◈'],
                        ['Pejabat Maya', '/app/pejabat', '◉'],
                    ],
                    'AI & Automasi' => [
                        ['Ejen AI', '/app/ai-agents', '◆'],
                        ['Boardroom', '/app/boardroom', '◈'],
                        ['Pembantu Peribadi', '/app/personal-ai', '◇'],
                        ['Automasi Aliran', '/app/flows', '⇄'],
                        ['AI Gateway', '/app/ai-gateway', '⚡'],
                    ],
                    'Jualan & Pelanggan' => [
                        ['POS', '/app/pos', '▤'],
                        ['CRM', '/app/crm', '◍'],
                        ['Invois', '/app/invoicing', '▦'],
                        ['Marketplace', '/app/marketplace', '▧'],
                        ['B-Booking', '/app/booking', '▣'],
                        ['Omnichannel', '/app/omnichannel', '◈'],
                        ['Helpdesk', '/app/helpdesk', '◔'],
                        ['Kesetiaan', '/app/loyalty', '★'],
                        ['Affiliate', '/app/affiliate', '◇'],
                        ['B-Link', '/app/link', '⚯'],
                    ],
                    'Pemasaran' => [
                        ['Kempen', '/app/marketing', '◈'],
                        ['B-Site', '/app/site', '▩'],
                    ],
                    'Operasi' => [
                        ['Inventori', '/app/inventory', '▤'],
                        ['COGS', '/app/cogs', '▥'],
                        ['Perkilangan', '/app/manufacturing', '⚙'],
                        ['Logistik', '/app/logistics', '▷'],
                        ['SCM', '/app/scm', '⛓'],
                        ['Aset', '/app/assets', '▣'],
                    ],
                    'Kewangan' => [
                        ['Kewangan', '/app/finance', '▦'],
                        ['Payroll', '/app/payroll', '▧'],
                    ],
                    'Orang & Projek' => [
                        ['HRMS', '/app/hrms', '◍'],
                        ['Projek', '/app/projects', '▦'],
                        ['Sign & Docs', '/app/sign-docs', '✎'],
                    ],
                    'Analitik' => [
                        ['Analitik', '/app/analytics', '◔'],
                        ['Brain Hub', '/app/brainhub', '◇'],
                        ['Industry Packs', '/app/industry', '▨'],
                    ],
                ];
            @endphp

            @foreach ($groups as $groupTitle => $items)
                <div class="nav-group">
                    <div class="nav-group-title">{{ $groupTitle }}</div>
                    @foreach ($items as [$label, $href, $icon])
                        <a href="{{ $href }}"
                           class="nav-item"
                           @if (request()->is(ltrim($href, '/')) || request()->is(ltrim($href, '/') . '/*')) aria-current="page" @endif>
                            <span class="nav-icon" aria-hidden="true">{{ $icon }}</span>
                            <span class="nav-label">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="sidebar-foot">
            <button class="nav-item btn-ghost" data-logout style="width:100%;border:0;background:transparent;cursor:pointer;">
                <span class="nav-icon" aria-hidden="true">⏻</span>
                <span class="nav-label sidebar-foot-text">Log Keluar</span>
            </button>
        </div>
    </aside>

    <div class="content">
        <header class="topbar">
            <button class="icon-btn" data-sidebar-toggle aria-label="Togol navigasi">☰</button>
            <span class="topbar-title">@yield('title', 'Papan Pemuka')</span>
            <button class="icon-btn" data-theme-toggle aria-label="Tukar tema">☀</button>
        </header>

        <main class="page">
            @yield('content')
        </main>
    </div>
</div>

<div class="toast-stack" role="status" aria-live="polite"></div>
<script src="{{ asset('assets/app.js') }}?v={{ @filemtime(public_path('assets/app.js')) }}"></script>
@stack('scripts')
</body>
</html>
