<?php

namespace App\Filament\Admin\Resources\ObatResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    protected static ?string $title = 'Daftar Batch Obat';

    protected static ?string $modelLabel = 'Batch';

    protected static ?string $pluralModelLabel = 'Batch';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomor_batch')
                    ->label('Nomor Batch (Opsional)')
                    ->placeholder('Contoh: BCH-12345'),
                Forms\Components\DatePicker::make('tanggal_kedaluwarsa')
                    ->label('Tanggal Kedaluwarsa')
                    ->required()
                    ->minDate(now()->toDateString())
                    ->placeholder('Pilih Tanggal Kedaluwarsa'),
                Forms\Components\TextInput::make('harga_beli')
                    ->label('Harga Beli')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(fn () => $this->getOwnerRecord()->harga_beli)
                    ->placeholder('8000'),
                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah Stok Masuk')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->placeholder('100'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor_batch')
            ->columns([
                Tables\Columns\TextColumn::make('nomor_batch')
                    ->label('No. Batch')
                    ->searchable()
                    ->default('-')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('tanggal_kedaluwarsa')
                    ->label('Tanggal Kedaluwarsa')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record): string => $record->tanggal_kedaluwarsa->isPast() ? 'danger' : ($record->tanggal_kedaluwarsa->diffInDays(now()) < 30 ? 'warning' : 'info')),
                Tables\Columns\TextColumn::make('harga_beli')
                    ->label('Harga Beli')
                    ->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Stok Awal')
                    ->numeric()
                    ->badge(),
                Tables\Columns\TextColumn::make('remaining_quantity')
                    ->label('Sisa Stok')
                    ->numeric()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Batch')
                    ->modalHeading('Tambah Batch Baru')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['remaining_quantity'] = $data['quantity'];
                        return $data;
                    })
                    ->after(function (Model $record) {
                        $obat = $this->getOwnerRecord();
                        
                        // Log to ObatMasuk without trigger duplicate batch creation
                        \App\Models\ObatMasuk::$skipBatchCreation = true;
                        \App\Models\ObatMasuk::create([
                            'nomor_transaksi' => 'IN-' . date('Ymd') . '-' . rand(1000, 9999),
                            'obat_id' => $obat->id,
                            'supplier_id' => $obat->supplier_id ?? \App\Models\Supplier::first()?->id ?? 1,
                            'jumlah' => $record->quantity,
                            'harga_beli' => $record->harga_beli,
                            'tanggal_masuk' => now(),
                            'nomor_batch' => $record->nomor_batch,
                            'tanggal_kedaluwarsa' => $record->tanggal_kedaluwarsa,
                        ]);
                        \App\Models\ObatMasuk::$skipBatchCreation = false;

                        // Recalculate stock & expiry on the owner obat record
                        $obat->recalculateStockAndExpiry();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function () {
                        $this->getOwnerRecord()->recalculateStockAndExpiry();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function () {
                        $this->getOwnerRecord()->recalculateStockAndExpiry();
                    }),
            ])
            ->bulkActions([
                // Disable bulk delete to avoid unintended stock calculation issues
            ]);
    }
}
