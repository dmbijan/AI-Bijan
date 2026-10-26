<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('AI BIJAN')
            ->colors([
                'primary' => Color::Amber,
            ])
            // Aplikasi sudah ada laluan `login` tersendiri (SPA). Filament
            // perlu guna laluan login-nya sendiri supaya tidak bertembung.
            ->authGuard('web')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            // ⚠️ JANGAN gabung `discoverWidgets()` dengan `->widgets([])`.
            // Apabila komponen di-cache (`filament:cache-components`), widget
            // daripada `discoverWidgets` menimpa senarai eksplisit dan
            // sebahagian widget hilang senyap. Guna senarai eksplisit SAHAJA.
            ->widgets([
                // Dashboard — susunan mengikut `$sort` pada setiap widget:
                // 1 KPI kewangan · 2 KPI pesanan · 3 trend hasil ·
                // 4 pecahan saluran · 5/6 jadual
                \App\Filament\Widgets\CommandCentreWidget::class,
                \App\Filament\Widgets\PesananRingkasanWidget::class,
                \App\Filament\Widgets\HasilTrendChart::class,
                \App\Filament\Widgets\SaluranPesananChart::class,
                \App\Filament\Widgets\PesananTerkiniWidget::class,
                \App\Filament\Widgets\InvoisTertunggakWidget::class,
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\ResolveFilamentTenant::class,
            ]);
    }
}
