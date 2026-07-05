<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\KategoriObatResource\Pages;
use App\Filament\Admin\Resources\KategoriObatResource\RelationManagers;
use App\Models\KategoriObat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Traits\HasLockedNavigation;

class KategoriObatResource extends Resource
{
    use HasLockedNavigation;

    protected static ?string $model = KategoriObat::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $modelLabel = 'Kategori Obat';

    protected static ?string $pluralModelLabel = 'Kategori Obat';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kategori')
                    ->description('Masukkan data kategori obat di bawah ini')
                    ->schema([
                        Forms\Components\TextInput::make('nama_kategori')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->label('Nama Kategori')
                            ->placeholder('Contoh: Obat Bebas, Obat Keras, dll.')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi Kategori')
                            ->placeholder('Masukkan penjelasan singkat mengenai kategori obat ini...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->deskripsi)
                    ->searchable(),
                Tables\Columns\TextColumn::make('obats_count')
                    ->label('Jumlah Obat')
                    ->counts('obats')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Kategori Obat')
            ->emptyStateDescription('Silakan tambahkan kategori obat pertama Anda.')
            ->emptyStateIcon('heroicon-o-tag');
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
            'index' => Pages\ListKategoriObats::route('/'),
            'create' => Pages\CreateKategoriObat::route('/create'),
            'edit' => Pages\EditKategoriObat::route('/{record}/edit'),
        ];
    }
}
