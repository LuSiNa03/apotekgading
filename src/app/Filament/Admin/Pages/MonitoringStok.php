<?php

namespace App\Filament\Admin\Pages;

use App\Models\Obat;
use App\Traits\HasLockedPageNavigation;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MonitoringStok extends Page implements HasTable
{
    use InteractsWithTable, HasLockedPageNavigation;

    public static function canAccess(): bool
    {
        return auth()->user() && auth()->user()->can('view_any_obat');
    }

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Monitoring Stok';

    protected static ?string $title = 'Monitoring Stok Real-Time';

    protected static string $view = 'filament.admin.pages.monitoring-stok';

    public static function table(Table $table): Table
    {
        return $table
            ->query(Obat::query())
            ->columns([
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular(),
                TextColumn::make('nama_obat')
                    ->label('Nama Obat')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('kategoriObat.nama_kategori')
                    ->label('Kategori')
                    ->sortable(),
                TextColumn::make('stok')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (Obat $record): string => 
                        $record->stok <= 0 ? 'danger' : 
                        ($record->stok <= $record->stok_minimum ? 'warning' : 'success')
                    ),
                TextColumn::make('stok_minimum')
                    ->label('Batas Minimum')
                    ->numeric(),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn (Obat $record): string => 
                        $record->stok <= 0 ? 'HABIS / KOSONG' : 
                        ($record->stok <= $record->stok_minimum ? 'KRITIS (RE-STOCK)' : 'AMAN')
                    )
                    ->badge()
                    ->color(fn (string $state): string => 
                        $state === 'HABIS / KOSONG' ? 'danger' : 
                        ($state === 'KRITIS (RE-STOCK)' ? 'warning' : 'success')
                    ),
                TextColumn::make('tanggal_kedaluwarsa')
                    ->label('Kedaluwarsa')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color(fn (Obat $record): string => 
                        $record->tanggal_kedaluwarsa->isPast() ? 'danger' : 
                        ($record->tanggal_kedaluwarsa->diffInDays(now()) < 30 ? 'warning' : 'info')
                    ),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'habis' => 'Stok Habis / Kosong',
                        'kritis' => 'Stok Kritis (<= Minimum)',
                        'aman' => 'Stok Aman (> Minimum)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === 'habis') {
                            return $query->where('stok', '<=', 0);
                        }
                        if ($data['value'] === 'kritis') {
                            return $query->where('stok', '>', 0)->whereColumn('stok', '<=', 'stok_minimum');
                        }
                        if ($data['value'] === 'aman') {
                            return $query->whereColumn('stok', '>', 'stok_minimum');
                        }
                        return $query;
                    }),
            ])
            ->defaultSort('stok', 'asc');
    }
}
