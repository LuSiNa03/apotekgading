<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ObatResource\Pages;
use App\Filament\Admin\Resources\ObatResource\RelationManagers;
use App\Models\Obat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Traits\HasLockedNavigation;

class ObatResource extends Resource
{
    use HasLockedNavigation;

    protected static ?string $model = Obat::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $modelLabel = 'Obat';

    protected static ?string $pluralModelLabel = 'Obat';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Obat')
                    ->description('Masukkan data detail obat di bawah ini')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('nama_obat')
                                    ->label('Nama Obat')
                                    ->required()
                                    ->placeholder('Paracetamol 500mg')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('kategori_obat_id')
                                    ->label('Kategori Obat')
                                    ->relationship('kategoriObat', 'nama_kategori')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih Kategori'),
                                Forms\Components\Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'nama_supplier')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih Supplier'),
                            ]),
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi / Kegunaan Obat')
                            ->placeholder('Contoh: Digunakan untuk meredakan nyeri kepala, demam, dan nyeri ringan...')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('barcode')
                                    ->label('Barcode / Kode Scan (Opsional)')
                                    ->placeholder('89999XXXXXXXX')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('tanggal_kedaluwarsa')
                                    ->label('Tanggal Kedaluwarsa')
                                    ->required()
                                    ->placeholder('Pilih Tanggal')
                                    ->minDate(now()->toDateString()),
                                Forms\Components\FileUpload::make('foto')
                                    ->label('Foto Obat (Opsional)')
                                    ->image()
                                    ->directory('foto-obat')
                                    ->maxSize(2048),
                            ]),
                    ]),

                Forms\Components\Section::make('Keuangan & Stok')
                    ->description('Atur harga beli, harga jual, dan stok obat')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('harga_beli')
                                    ->label('Harga Beli')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('8000'),
                                Forms\Components\TextInput::make('harga_jual')
                                    ->label('Harga Jual')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('10000')
                                    ->rules([
                                        fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $hargaBeli = (int) $get('harga_beli');
                                            if ($hargaBeli && (int) $value < $hargaBeli) {
                                                $fail('Harga jual tidak boleh lebih kecil dari harga beli.');
                                            }
                                        },
                                    ]),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('stok')
                                    ->label('Stok Awal')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->placeholder('0')
                                    ->minValue(0),
                                Forms\Components\TextInput::make('stok_minimum')
                                    ->label('Batas Stok Minimum')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->placeholder('0')
                                    ->minValue(0),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('nama_obat')
                    ->label('Nama Obat')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('kategoriObat.nama_kategori')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('barcode')
                    ->label('Barcode')
                    ->searchable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('harga_beli')
                    ->label('Harga Beli')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (Obat $record): string => $record->stok <= $record->stok_minimum ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('stok_minimum')
                    ->label('Min Stok')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tanggal_kedaluwarsa')
                    ->label('Kedaluwarsa')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color(fn (Obat $record): string => $record->tanggal_kedaluwarsa->isPast() ? 'danger' : ($record->tanggal_kedaluwarsa->diffInDays(now()) < 30 ? 'warning' : 'info')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_obat_id')
                    ->label('Kategori')
                    ->relationship('kategoriObat', 'nama_kategori'),
                Tables\Filters\Filter::make('stok_hampir_habis')
                    ->label('Stok Hampir Habis')
                    ->query(fn (Builder $query) => $query->whereColumn('stok', '<=', 'stok_minimum')),
                Tables\Filters\Filter::make('hampir_kedaluwarsa')
                    ->label('Hampir Kedaluwarsa (< 30 hari)')
                    ->query(fn (Builder $query) => $query->where('tanggal_kedaluwarsa', '<=', now()->addDays(30))),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Obat $record) {
                        if ($record->detailPenjualans()->exists() || $record->obatMasuks()->exists()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Tidak dapat menghapus obat')
                                ->body("Obat \"{$record->nama_obat}\" sudah memiliki riwayat transaksi masuk atau penjualan.")
                                ->danger()
                                ->send();
                            
                            $action->halt();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, Tables\Actions\DeleteBulkAction $action) {
                            $records->each(function (Obat $record) use ($action) {
                                if ($record->detailPenjualans()->exists() || $record->obatMasuks()->exists()) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Tidak dapat menghapus obat')
                                        ->body("Obat \"{$record->nama_obat}\" sudah memiliki riwayat transaksi masuk atau penjualan.")
                                        ->danger()
                                        ->send();
                                    return;
                                }
                                $record->delete();
                            });
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Obat')
            ->emptyStateDescription('Silakan tambahkan data obat pertama Anda.')
            ->emptyStateIcon('heroicon-o-beaker');
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
            'index' => Pages\ListObats::route('/'),
            'create' => Pages\CreateObat::route('/create'),
            'edit' => Pages\EditObat::route('/{record}/edit'),
        ];
    }
}
