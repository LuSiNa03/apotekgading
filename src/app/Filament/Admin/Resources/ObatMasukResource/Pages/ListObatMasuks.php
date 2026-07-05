<?php

namespace App\Filament\Admin\Resources\ObatMasukResource\Pages;

use App\Filament\Admin\Resources\ObatMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListObatMasuks extends ListRecords
{
    protected static string $resource = ObatMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
