<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SupplierResource\Pages;
use App\Filament\Admin\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Traits\HasLockedNavigation;

class SupplierResource extends Resource
{
    use HasLockedNavigation;

    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $modelLabel = 'Supplier';

    protected static ?string $pluralModelLabel = 'Supplier';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Supplier')
                    ->description('Masukkan data supplier obat di bawah ini')
                    ->schema([
                        Forms\Components\TextInput::make('nama_supplier')
                            ->label('Nama Supplier')
                            ->required()
                            ->placeholder('PT. Contoh Jaya')
                            ->maxLength(255),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('no_telp')
                                    ->label('Nomor Telepon')
                                    ->required()
                                    ->numeric()
                                    ->placeholder('0812XXXXXXXX')
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('email')
                                    ->label('Email (Opsional)')
                                    ->email()
                                    ->placeholder('sales@contoh.com')
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->placeholder('Jl. Kebahagiaan No. 12, Kota...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_supplier')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('no_telp')
                    ->label('Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->default('-'),
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
            ->emptyStateHeading('Belum Ada Supplier')
            ->emptyStateDescription('Silakan tambahkan supplier pertama Anda.')
            ->emptyStateIcon('heroicon-o-building-office');
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
