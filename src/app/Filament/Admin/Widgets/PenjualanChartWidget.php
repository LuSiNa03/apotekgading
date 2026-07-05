<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Penjualan;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PenjualanChartWidget extends ChartWidget
{
    use HasWidgetShield;

    public static function canView(): bool
    {
        return auth()->user() && auth()->user()->can('view_any_penjualan');
    }

    protected static ?string $heading = 'Grafik Penjualan 30 Hari Terakhir';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $days   = collect(range(29, 0))->map(fn ($i) => Carbon::today()->subDays($i));
        $labels = $days->map(fn ($d) => $d->format('d M'))->toArray();

        $penjualanData = $days->map(function ($day) {
            return Penjualan::where('status_pembayaran', 'berhasil')
                ->whereDate('created_at', $day)
                ->sum('total_harga') / 1000; // dalam ribuan
        })->toArray();

        $transaksiData = $days->map(function ($day) {
            return Penjualan::where('status_pembayaran', 'berhasil')
                ->whereDate('created_at', $day)
                ->count();
        })->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Pendapatan (Rp ribu)',
                    'data'            => $penjualanData,
                    'borderColor'     => '#d97706', // amber-600
                    'backgroundColor' => 'rgba(217, 119, 6, 0.8)', // solid amber
                    'fill'            => true,
                    'tension'         => 0, // straight lines like the example
                    'borderWidth'     => 1,
                    'pointRadius'     => 0, // hide points
                    'pointHoverRadius' => 6,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Jumlah Transaksi',
                    'data'            => $transaksiData,
                    'borderColor'     => '#92400e', // amber-800
                    'backgroundColor' => 'rgba(146, 64, 14, 0.9)', // dark solid amber
                    'fill'            => true,
                    'tension'         => 0,
                    'borderWidth'     => 1,
                    'pointRadius'     => 0,
                    'pointHoverRadius' => 6,
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.05)',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
        ];
    }
}
