<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Settings\GeneralSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

        return $panel
            ->default()
            ->spa()
            ->topNavigation(true)
            ->maxContentWidth('full')
            ->id('admin')
            ->path('admin')
            ->favicon(asset('images/success.png'))
            ->login()
            ->colors([
                'primary' => Color::Amber,
                'secondary' => Color::Cyan,
                'danger' => Color::Red,
                'warning' => Color::Yellow,
                'success' => Color::Green,
                'info' => Color::Blue,
            ])
            ->navigationItems([
                NavigationItem::make('Dashboard')
                    ->icon('heroicon-o-home')
                    ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.pages.dashboard'))
                    ->url(fn(): string => \Filament\Pages\Dashboard::getUrl()),
                NavigationItem::make('Transaksi DO')
                    ->icon('heroicon-o-document-text')
                    ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.resources.transaksi-dos.*'))
                    ->url(fn(): string => route('filament.admin.resources.transaksi-dos.index')),
                NavigationItem::make('Laporan Keuangan')
                    ->icon('heroicon-o-currency-dollar')
                    ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.resources.laporan-keuangans.*'))
                    ->url(fn(): string => route('filament.admin.resources.laporan-keuangans.index')),
                NavigationItem::make('Penjual')
                    ->icon('heroicon-o-users')
                    ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.resources.penjuals.*'))
                    ->url(fn(): string => route('filament.admin.resources.penjuals.index')),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
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
                Authenticate::class
            ]);
    }
}
