<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Obat;
use App\Models\Penjualan;
use App\Models\Supplier;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StokHampirHabisWidget extends BaseWidget
{
    use HasWidgetShield;

    public static function canView(): bool
    {
        return auth()->user() && auth()->user()->can('view_any_obat');
    }

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalObat    = Obat::count();
        $stokHabis    = Obat::whereColumn('stok', '<=', 'stok_minimum')->count();
        $hampirExpiry = Obat::where('tanggal_kedaluwarsa', '<=', Carbon::today()->addDays(30))->count();
        $totalSupplier = Supplier::count();

        $pendapatanHariIni = Penjualan::where('status_pembayaran', 'berhasil')
            ->whereDate('created_at', today())
            ->sum('total_harga');

        $pendapatanBulanIni = Penjualan::where('status_pembayaran', 'berhasil')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_harga');

        return [
            Stat::make('Total Jenis Obat', $totalObat)
                ->description('Jumlah seluruh obat terdaftar')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('info'),

            Stat::make('Stok Hampir Habis', $stokHabis)
                ->description('Obat di bawah batas minimum')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stokHabis > 0 ? 'danger' : 'success'),

            Stat::make('Hampir Kedaluwarsa', $hampirExpiry)
                ->description('Kurang dari 30 hari lagi')
                ->descriptionIcon('heroicon-m-clock')
                ->color($hampirExpiry > 0 ? 'warning' : 'success'),

            Stat::make('Total Supplier', $totalSupplier)
                ->description('Supplier aktif terdaftar')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('gray'),

            Stat::make('Pendapatan Hari Ini', 'Rp ' . number_format($pendapatanHariIni, 0, ',', '.'))
                ->description('Total transaksi berhasil hari ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($pendapatanBulanIni, 0, ',', '.'))
                ->description('Total transaksi berhasil bulan ini')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
        ];
    }
}
