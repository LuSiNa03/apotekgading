<?php

namespace App\Traits;

use Filament\Navigation\NavigationItem;

trait HasLockedNavigation
{
    /**
     * Selalu daftarkan ke navigasi — baik punya akses atau tidak.
     * Jika tidak punya akses, item akan tampil dengan ikon gembok merah.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    /**
     * Override getNavigationItems() agar item selalu muncul di sidebar.
     * Jika user tidak punya hak akses → URL diset ke '#locked'
     * sehingga sidebar item.blade.php menampilkan ikon gembok merah.
     */
    public static function getNavigationItems(): array
    {
        $hasAccess = static::canAccess();

        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->sort(static::getNavigationSort())
                ->badge($hasAccess ? static::getNavigationBadge() : null,
                    color: $hasAccess ? static::getNavigationBadgeColor() : null)
                ->badgeTooltip($hasAccess ? static::getNavigationBadgeTooltip() : null)
                ->isActiveWhen(fn () => $hasAccess && request()->routeIs(static::getRouteBaseName() . '.*'))
                ->url($hasAccess ? static::getUrl() : '#locked'),
        ];
    }
}
