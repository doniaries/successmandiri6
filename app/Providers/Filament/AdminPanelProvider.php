<?php

namespace App\Providers\Filament;


use App\Settings\GeneralSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\LegacyComponents\Widget;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\Dashboard;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        try {
            $settings = app(GeneralSettings::class);
            $themeColor = $settings->tema_warna ?? 'amber';
        } catch (\Exception $e) {
            $themeColor = 'amber';
        }

        return $panel
            ->default()
            ->spa()
            ->topNavigation(true)
            // ->sidebarFullyCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->id('admin')
            ->path('admin')
            // ->favicon(asset('images/success.png'))
            ->login()
            ->colors([
                'primary' => Color::Amber,
                'secondary' => Color::Cyan,
                'danger' => Color::Red,
                'warning' => Color::Yellow,
                'success' => Color::Green,
                'info' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,

                // Widgets\FilamentInfoWidget::class,
            ])
            ->authMiddleware([
                Authenticate::class
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
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
            ]);
    }
}
