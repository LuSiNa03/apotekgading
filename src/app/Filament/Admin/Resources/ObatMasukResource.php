<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ObatMasukResource\Pages;
use App\Filament\Admin\Resources\ObatMasukResource\RelationManagers;
use App\Models\ObatMasuk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Traits\HasLockedNavigation;

class ObatMasukResource extends Resource
{
    use HasLockedNavigation;

    protected static ?string $model = ObatMasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $modelLabel = 'Obat Masuk';

    protected static ?string $pluralModelLabel = 'Obat Masuk';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pencatatan Obat Masuk')
                    ->description('Masukkan data obat yang diterima dari supplier')
                    ->schema([
                        Forms\Components\TextInput::make('nomor_transaksi')
                            ->label('Nomor Transaksi')
                            ->required()
                            ->readonly()
                            ->default(fn () => 'IN-' . date('Ymd') . '-' . rand(1000, 9999))
                            ->placeholder('Otomatis'),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'nama_supplier')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                        $supplierId = $get('supplier_id');
                                        $obatId = $get('obat_id');
                                        if ($supplierId && $obatId) {
                                            $obat = \App\Models\Obat::find($obatId);
                                            if ($obat && $obat->supplier_id == $supplierId) {
                                                $set('harga_beli', $obat->harga_beli);
                                            } else {
                                                $set('harga_beli', null);
                                            }
                                        }
                                    })
                                    ->placeholder('Pilih Supplier'),
                                Forms\Components\Select::make('obat_id')
                                    ->label('Obat')
                                    ->relationship('obat', 'nama_obat')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                        $supplierId = $get('supplier_id');
                                        $obatId = $get('obat_id');
                                        if ($supplierId && $obatId) {
                                            $obat = \App\Models\Obat::find($obatId);
                                            if ($obat && $obat->supplier_id == $supplierId) {
                                                $set('harga_beli', $obat->harga_beli);
                                            } else {
                                                $set('harga_beli', null);
                                            }
                                        }
                                    })
                                    ->placeholder('Pilih Obat'),
                            ]),
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('jumlah')
                                    ->label('Jumlah Masuk')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('100'),
                                Forms\Components\TextInput::make('harga_beli')
                                    ->label('Harga Beli Satuan')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(1)
                                    ->placeholder('5000'),
                                Forms\Components\DateTimePicker::make('tanggal_masuk')
                                    ->label('Tanggal & Waktu Masuk')
                                    ->required()
                                    ->default(now()),
                                Forms\Components\FileUpload::make('faktur')
                                    ->label('Faktur Pembelian')
                                    ->directory('faktur-pembelian')
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->maxSize(4096)
                                    ->required(),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nomor_batch')
                                    ->label('Nomor Batch (Opsional)')
                                    ->placeholder('Contoh: PCM24022029')
                                    ->helperText('Kosongkan untuk auto-generate dari Kode Produk + Tanggal Expired. Contoh: PCM24022029'),
                                Forms\Components\DatePicker::make('tanggal_kedaluwarsa')
                                    ->label('Tanggal Kedaluwarsa')
                                    ->required()
                                    ->minDate(now()->toDateString())
                                    ->placeholder('Pilih Tanggal Kedaluwarsa'),
                            ]),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_transaksi')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('obat.nama_obat')
                    ->label('Nama Obat')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('harga_beli')
                    ->label('Harga Beli')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_batch')
                    ->label('No. Batch')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('tanggal_kedaluwarsa')
                    ->label('Kedaluwarsa')
                    ->date('d M Y')
                    ->sortable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->label('Tanggal Masuk')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('faktur')
                    ->label('Faktur')
                    ->formatStateUsing(fn ($state) => $state ? 'Unduh Faktur' : '-')
                    ->url(fn ($record) => $record->faktur ? asset('storage/' . $record->faktur) : null)
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->weight('bold'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'nama_supplier'),
                Tables\Filters\SelectFilter::make('obat_id')
                    ->label('Obat')
                    ->relationship('obat', 'nama_obat'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // bulk delete disabled
            ])
            ->emptyStateHeading('Belum Ada Riwayat Obat Masuk')
            ->emptyStateDescription('Silakan catat obat masuk pertama Anda.')
            ->emptyStateIcon('heroicon-o-arrow-down-tray');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListObatMasuks::route('/'),
            'create' => Pages\CreateObatMasuk::route('/create'),
            'edit' => Pages\EditObatMasuk::route('/{record}/edit'),
        ];
    }
}
