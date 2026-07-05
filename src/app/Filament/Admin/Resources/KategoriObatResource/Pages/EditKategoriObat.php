<?php

namespace App\Filament\Admin\Resources\KategoriObatResource\Pages;

use App\Filament\Admin\Resources\KategoriObatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKategoriObat extends EditRecord
{
    protected static string $resource = KategoriObatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
