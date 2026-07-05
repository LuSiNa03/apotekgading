<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Obat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class HampirKedaluwarsaWidget extends BaseWidget
{
    use HasWidgetShield;

    public static function canView(): bool
    {
        return auth()->user() && auth()->user()->can('view_any_obat');
    }

    protected static ?string $heading = '⚠️ Obat Hampir Kedaluwarsa (< 30 Hari)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Obat::query()
                    ->where('tanggal_kedaluwarsa', '<=', Carbon::today()->addDays(30))
                    ->orderBy('tanggal_kedaluwarsa', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama_obat')
                    ->label('Nama Obat')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('kategoriObat.nama_kategori')
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->badge()
                    ->color(fn (Obat $record) => $record->stok <= $record->stok_minimum ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('tanggal_kedaluwarsa')
                    ->label('Kedaluwarsa')
                    ->date('d M Y')
                    ->badge()
                    ->color(fn (Obat $record) => $record->tanggal_kedaluwarsa->isPast() ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('tanggal_kedaluwarsa')
                    ->label('Sisa Hari')
                    ->formatStateUsing(fn (Obat $record) => $record->tanggal_kedaluwarsa->isPast()
                        ? 'Sudah Kedaluwarsa!'
                        : $record->tanggal_kedaluwarsa->diffInDays(today()) . ' hari lagi')
                    ->badge()
                    ->color(fn (Obat $record) => $record->tanggal_kedaluwarsa->isPast() ? 'danger' : 'warning'),
            ])
            ->emptyStateHeading('Tidak ada obat yang hampir kedaluwarsa')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }
}
