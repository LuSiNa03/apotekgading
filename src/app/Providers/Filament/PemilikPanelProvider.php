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

class PemilikPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $brandName = 'Apotek Gading';
        $brandLogo = null;
        try {
            if (\Schema::hasTable('identitas_apoteks')) {
                $identitas = \App\Models\IdentitasApotek::getSingle();
                if ($identitas) {
                    $brandName = $identitas->nama_apotek;
                    if ($identitas->logo) {
                        $brandLogo = asset('storage/' . $identitas->logo);
                    }
                }
            }
        } catch (\Exception $e) {}

        return $panel
            ->id('pemilik')
            ->path('pemilik')
            ->brandName($brandName)
            ->brandLogo($brandLogo)
            ->brandLogoHeight('58px')
            ->spa()
            ->login(\App\Filament\Pages\Auth\CustomLogin::class)
            ->passwordReset()
            ->profile(\App\Filament\Pages\Auth\EditProfile::class, isSimple: false)
            ->font('Montserrat')
            ->colors([
                'primary' => '#B91C1C',
            ])
            ->discoverResources(in: app_path('Filament/Pemilik/Resources'), for: 'App\\Filament\\Pemilik\\Resources')
            ->discoverPages(in: app_path('Filament/Pemilik/Pages'), for: 'App\\Filament\\Pemilik\\Pages')
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Admin\Pages\LaporanPage::class,
                \App\Filament\Admin\Pages\IdentitasApotekPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Pemilik/Widgets'), for: 'App\\Filament\\Pemilik\\Widgets')
            ->widgets([
                \App\Filament\Admin\Widgets\PenjualanChartWidget::class,
                \App\Filament\Admin\Widgets\StokHampirHabisWidget::class,
                \App\Filament\Admin\Widgets\HampirKedaluwarsaWidget::class,
            ])
            ->resources([
                \App\Filament\Admin\Resources\UserResource::class,
                \App\Filament\Admin\Resources\ObatResource::class,
                \App\Filament\Admin\Resources\KategoriObatResource::class,
                \App\Filament\Admin\Resources\SupplierResource::class,
                \App\Filament\Admin\Resources\ObatMasukResource::class,
                \App\Filament\Admin\Resources\PenjualanResource::class,
                config('filament-logger.activity_resource'),
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                \DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin::make()
                    ->showEmptyPanelOnMobile(false)
                    ->formPanelPosition('right')
                    ->formPanelWidth('40%')
                    ->emptyPanelBackgroundImageOpacity('80%')
                    ->emptyPanelBackgroundImageUrl('https://images.unsplash.com/photo-1586023492125-27b2c045efd7?q=80&w=1200&auto=format&fit=crop'),
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
            ]);
    }
}
