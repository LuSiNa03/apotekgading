<?php

namespace App\Filament\Admin\Resources\ObatResource\Pages;

use App\Filament\Admin\Resources\ObatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListObats extends ListRecords
{
    protected static string $resource = ObatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
