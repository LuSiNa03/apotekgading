<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PenjualanResource\Pages;
use App\Models\Obat;
use App\Models\Penjualan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasLockedNavigation;

class PenjualanResource extends Resource
{
    use HasLockedNavigation;

    protected static ?string $model = Penjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $modelLabel = 'Transaksi Penjualan';

    protected static ?string $pluralModelLabel = 'Transaksi Penjualan';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return !auth()->user()?->hasRole('petugas_apotek');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('kode_transaksi')
                                    ->label('Kode Transaksi')
                                    ->required()
                                    ->readonly()
                                    ->default(fn () => 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5))),
                                Forms\Components\Hidden::make('user_id')
                                    ->default(fn () => auth()->id()),
                            ]),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Keranjang Belanja')
                    ->description('Pilih obat dan jumlah yang dibeli')
                    ->schema([
                        Forms\Components\Repeater::make('detailPenjualans')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('obat_id')
                                    ->label('Nama Obat')
                                    ->options(
                                        Obat::where('stok', '>', 0)
                                            ->get()
                                            ->mapWithKeys(fn ($obat) => [
                                                $obat->id => "{$obat->nama_obat} (Stok: {$obat->stok})"
                                            ])
                                    )
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $obat = Obat::find($state);
                                        if ($obat) {
                                            $set('harga', $obat->harga_jual);
                                            $set('stok_tersedia', $obat->stok);
                                            $set('subtotal', $obat->harga_jual);
                                            $set('jumlah', 1);
                                        }
                                    }),
                                Forms\Components\Hidden::make('stok_tersedia'),
                                Forms\Components\TextInput::make('harga')
                                    ->label('Harga Satuan')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->readonly()
                                    ->live(),
                                Forms\Components\TextInput::make('jumlah')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->live()
                                    ->rules([
                                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $stok = (int) $get('stok_tersedia');
                                            if ($stok > 0 && (int) $value > $stok) {
                                                $fail("Stok tidak mencukupi. Tersedia: {$stok}");
                                            }
                                        },
                                    ])
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $harga = (int) $get('harga');
                                        $set('subtotal', $harga * (int) $state);
                                    }),
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->readonly(),
                            ])
                            ->columns(4)
                            ->addActionLabel('+ Tambah Obat')
                            ->minItems(1)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::hitungTotal($get, $set);
                            })
                            ->deleteAction(fn ($action) => $action->after(
                                fn (Get $get, Set $set) => self::hitungTotal($get, $set)
                            )),
                    ]),

                Forms\Components\Section::make('Pembayaran')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('total_harga')
                                    ->label('Total Harga')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->readonly()
                                    ->default(0),
                                Forms\Components\Select::make('metode_pembayaran')
                                    ->label('Metode Pembayaran')
                                    ->options([
                                        'tunai' => 'Tunai',
                                        'non-tunai' => 'Non-Tunai (Midtrans)',
                                    ])
                                    ->required()
                                    ->live()
                                    ->default('tunai'),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nominal_bayar')
                                    ->label('Nominal Bayar')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->default(0)
                                    ->visible(fn (Get $get) => $get('metode_pembayaran') === 'tunai')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                        $total = (int) $get('total_harga');
                                        $bayar = (int) $state;
                                        $set('kembalian', max(0, $bayar - $total));
                                    }),
                                Forms\Components\TextInput::make('kembalian')
                                    ->label('Kembalian')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->readonly()
                                    ->default(0)
                                    ->visible(fn (Get $get) => $get('metode_pembayaran') === 'tunai'),
                            ]),
                        Forms\Components\Hidden::make('status_pembayaran')
                            ->default(fn (Get $get) => $get('metode_pembayaran') === 'tunai' ? 'berhasil' : 'pending'),
                    ]),
            ]);
    }

    protected static function hitungTotal(Get $get, Set $set): void
    {
        $items = $get('detailPenjualans') ?? [];
        $total = collect($items)->sum(fn ($item) => (int) ($item['subtotal'] ?? 0));
        $set('total_harga', $total);
        $set('kembalian', max(0, (int) $get('nominal_bayar') - $total));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('kode_transaksi')
                    ->label('Kode Transaksi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->colors([
                        'success' => 'tunai',
                        'info' => 'non-tunai',
                    ]),
                Tables\Columns\BadgeColumn::make('status_pembayaran')
                    ->label('Status')
                    ->colors([
                        'success' => 'berhasil',
                        'warning' => 'pending',
                        'danger' => 'gagal',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->options([
                        'tunai' => 'Tunai',
                        'non-tunai' => 'Non-Tunai',
                    ]),
                Tables\Filters\SelectFilter::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->options([
                        'berhasil' => 'Berhasil',
                        'pending' => 'Pending',
                        'gagal' => 'Gagal',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query) => $query->whereDate('created_at', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('bayar')
                    ->label('Bayar')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->url(fn (Penjualan $record) => static::getUrl('view', ['record' => $record]))
                    ->visible(fn (Penjualan $record) => $record->metode_pembayaran === 'non-tunai' && $record->status_pembayaran === 'pending'),
                Tables\Actions\Action::make('cetak')
                    ->label('Cetak Struk')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Penjualan $record) => route('penjualan.struk', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Penjualan $record) => $record->status_pembayaran === 'berhasil'),
            ])
            ->bulkActions([
                // disabled
            ])
            ->emptyStateHeading('Belum Ada Transaksi Penjualan')
            ->emptyStateDescription('Silakan buat transaksi baru.')
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Transaksi Baru'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjualans::route('/'),
            'create' => Pages\CreatePenjualan::route('/create'),
            'view' => Pages\ViewPenjualan::route('/{record}'),
        ];
    }
}
