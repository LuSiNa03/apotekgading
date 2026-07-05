<?php

namespace App\Services;

use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Auth;

class LockedNavigationService
{
    /**
     * Daftar resource beserta permission dan label/icon-nya.
     * Format: [permission_key, label, icon, url, group, sort]
     */
    public static function getAdminItems(): array
    {
        return [
            // Data Master
            ['view_any_obat',           'Obat',                 'heroicon-o-beaker',                '/admin/obats',              'Data Master', 3],
            ['view_any_kategori::obat', 'Kategori Obat',        'heroicon-o-tag',                   '/admin/kategori-obats',     'Data Master', 1],
            ['view_any_supplier',       'Supplier',             'heroicon-o-building-office-2',     '/admin/suppliers',          'Data Master', 2],
            // Transaksi
            ['view_any_obat::masuk',    'Obat Masuk',           'heroicon-o-arrow-down-tray',       '/admin/obat-masuks',        'Transaksi',   1],
            ['view_any_penjualan',      'Transaksi Penjualan',  'heroicon-o-shopping-cart',         '/admin/penjualans',         'Transaksi',   2],
            // Laporan & Administration
            ['page_LaporanPage',        'Laporan & Export',     'heroicon-o-document-chart-bar',    '/admin/laporan',            'Laporan',     1],
            ['view_any_user',           'Users',                'heroicon-o-users',                 '/admin/users',              'Administration', 1],
            ['page_IdentitasApotekPage','Identitas Apotek',     'heroicon-o-cog-6-tooth',           '/admin/identitas-apotek',   'Administration', 2],
            ['view_any_role',           'Roles',                'heroicon-o-shield-check',          '/admin/shield/roles',       'Administration', 3],
            ['view_any_activity',       'Activity Log',         'heroicon-o-clipboard-document-list','/admin/activity-logs',     'Administration', 4],
        ];
    }

    public static function getPemilikItems(): array
    {
        return [
            ['view_any_obat',           'Obat',                 'heroicon-o-beaker',                '/pemilik/obats',            'Data Master', 3],
            ['view_any_kategori::obat', 'Kategori Obat',        'heroicon-o-tag',                   '/pemilik/kategori-obats',   'Data Master', 1],
            ['view_any_supplier',       'Supplier',             'heroicon-o-building-office-2',     '/pemilik/suppliers',        'Data Master', 2],
            ['view_any_obat::masuk',    'Obat Masuk',           'heroicon-o-arrow-down-tray',       '/pemilik/obat-masuks',      'Transaksi',   1],
            ['view_any_penjualan',      'Transaksi Penjualan',  'heroicon-o-shopping-cart',         '/pemilik/penjualans',       'Transaksi',   2],
            ['page_LaporanPage',        'Laporan & Export',     'heroicon-o-document-chart-bar',    '/pemilik/laporan',          'Laporan',     1],
            ['page_IdentitasApotekPage','Identitas Apotek',     'heroicon-o-cog-6-tooth',           '/pemilik/identitas-apotek', 'Administration', 1],
        ];
    }

    public static function getPetugasItems(): array
    {
        return [
            ['view_any_obat::masuk',    'Obat Masuk',           'heroicon-o-arrow-down-tray',       '/petugas/obat-masuks',      'Transaksi',   1],
            ['view_any_penjualan',      'Transaksi Penjualan',  'heroicon-o-shopping-cart',         '/petugas/penjualans',       'Transaksi',   2],
        ];
    }

    /**
     * Build navigation items with locked state for resources the user can't access.
     */
    public static function buildNavigationItems(array $itemDefinitions): array
    {
        $user = Auth::user();
        $items = [];

        foreach ($itemDefinitions as [$permission, $label, $icon, $url, $group, $sort]) {
            $hasAccess = $user && $user->can($permission);

            $items[] = NavigationItem::make($label)
                ->label($label)
                ->icon($icon)
                ->group($group)
                ->sort($sort)
                ->url($hasAccess ? $url : '#locked')
                ->isActiveWhen(fn () => $hasAccess && request()->is(ltrim($url, '/') . '*'));
        }

        return $items;
    }
}
