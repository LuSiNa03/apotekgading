<?php

namespace App\Traits;

use Filament\Navigation\NavigationItem;

trait HasLockedPageNavigation
{
    /**
     * Tentukan apakah user bisa mengakses halaman ini.
     * Jika return false, Filament otomatis melempar 403 jika diakses lewat URL.
     */
    public static function canAccess(): bool
    {
        $pagePermissionName = 'page_' . class_basename(static::class);
        
        return auth()->user() && auth()->user()->can($pagePermissionName);
    }

    /**
     * Selalu daftarkan halaman ke navigasi agar muncul di sidebar.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    /**
     * Override getNavigationItems() untuk menampilkan halaman yang tidak diizinkan
     * sebagai item terkunci (#locked) di sidebar.
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
                ->isActiveWhen(fn () => $hasAccess && request()->routeIs(static::getRouteName()))
                ->url($hasAccess ? static::getUrl() : '#locked'),
        ];
    }
}
