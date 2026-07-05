<?php

namespace App\Filament\Admin\Resources\ObatResource\Pages;

use App\Filament\Admin\Resources\ObatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditObat extends EditRecord
{
    protected static string $resource = ObatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action) {
                    $record = $this->getRecord();
                    if ($record->detailPenjualans()->exists() || $record->obatMasuks()->exists()) {
                        \Filament\Notifications\Notification::make()
                            ->title('Tidak dapat menghapus obat')
                            ->body("Obat \"{$record->nama_obat}\" sudah memiliki riwayat transaksi masuk atau penjualan.")
                            ->danger()
                            ->send();
                        
                        $action->halt();
                    }
                }),
        ];
    }
}
