<?php

namespace App\Providers\Filament;

use Filament\Enums\GlobalSearchPosition;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class NegosyotrackPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('negosyotrack')
            ->path('')
            ->viteTheme('resources/css/filament/negosyotrack/theme.css')
            ->login()

            // custom by jd
            ->sidebarCollapsibleOnDesktop()
            ->globalSearch(position: GlobalSearchPosition::Sidebar)
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->brandLogo(asset('images/logo-text.png'))
            ->brandLogoHeight('3rem')
            ->topbar(false)

            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Msme Management'),

                NavigationGroup::make()
                    ->label('Calamity Monitoring'),

                NavigationGroup::make()
                    ->label('Price Monitoring'),
                
                NavigationGroup::make()
                    ->label('Economic Map')
            ])

            ->colors([
                'primary' => [
                    50 => '#FEF2F2',
                    100 => '#FEE2E2',
                    200 => '#FCA5A5',
                    300 => '#F87171',
                    400 => '#EF4444',
                    500 => '#DC2626',
                    600 => '#B91C1C',
                    700 => '#991B1B',
                    800 => '#7F1D1D',
                    900 => '#450A0A',
                    950 => '#2F0505',
                ],
            ])

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
