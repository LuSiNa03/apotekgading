<?php

namespace App\Filament\Admin\Resources\ObatResource\Pages;

use App\Filament\Admin\Resources\ObatResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateObat extends CreateRecord
{
    protected static string $resource = ObatResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['barcode'])) {
            do {
                $barcode = '899' . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
            } while (\App\Models\Obat::where('barcode', $barcode)->exists());

            $data['barcode'] = $barcode;
        }

        return $data;
    }
}
