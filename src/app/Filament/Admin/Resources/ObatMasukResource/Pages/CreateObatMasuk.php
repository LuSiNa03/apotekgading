<?php

namespace App\Filament\Admin\Resources\ObatMasukResource\Pages;

use App\Filament\Admin\Resources\ObatMasukResource;
use App\Models\Obat;
use App\Services\BarcodeGenerator;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateObatMasuk extends CreateRecord
{
    protected static string $resource = ObatMasukResource::class;

    /**
     * Auto-generate nomor batch jika dikosongkan oleh pengguna.
     * Format: {KODE_PRODUK}{ddmmyyyy}
     * Jika obat tidak memiliki kode_produk, gunakan prefix "BATCH".
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['nomor_batch']) && ! empty($data['obat_id']) && ! empty($data['tanggal_kedaluwarsa'])) {
            $obat = Obat::find($data['obat_id']);

            if ($obat) {
                $data['nomor_batch'] = BarcodeGenerator::generateBatchNumber(
                    $obat,
                    $data['tanggal_kedaluwarsa']
                );
            }
        }

        return $data;
    }
}
