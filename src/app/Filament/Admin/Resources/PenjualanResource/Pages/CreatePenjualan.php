<?php

namespace App\Filament\Admin\Resources\PenjualanResource\Pages;

use App\Filament\Admin\Resources\PenjualanResource;
use App\Models\Obat;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreatePenjualan extends CreateRecord
{
    protected static string $resource = PenjualanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Set status berhasil for cash payments
            if ($data['metode_pembayaran'] === 'tunai') {
                $data['status_pembayaran'] = 'berhasil';
            } else {
                $data['status_pembayaran'] = 'pending';
            }

            // Validate stock availability for all items
            $details = $data['detailPenjualans'] ?? [];
            foreach ($details as $item) {
                $obat = Obat::lockForUpdate()->find($item['obat_id']);
                if (!$obat || $obat->stok < $item['jumlah']) {
                    Notification::make()
                        ->title('Stok tidak mencukupi untuk ' . ($obat?->nama_obat ?? 'obat yang dipilih'))
                        ->danger()
                        ->send();
                    $this->halt();
                }
            }

            // Create penjualan record
            $penjualan = static::getModel()::create([
                'kode_transaksi' => $data['kode_transaksi'],
                'user_id'        => $data['user_id'],
                'total_harga'    => $data['total_harga'],
                'metode_pembayaran' => $data['metode_pembayaran'],
                'status_pembayaran' => $data['status_pembayaran'],
                'nominal_bayar'  => $data['nominal_bayar'] ?? null,
                'kembalian'      => $data['kembalian'] ?? null,
            ]);

            // For cash payment: create details and decrement stock immediately
            if ($data['metode_pembayaran'] === 'tunai') {
                foreach ($details as $item) {
                    $penjualan->detailPenjualans()->create([
                        'obat_id'  => $item['obat_id'],
                        'jumlah'   => $item['jumlah'],
                        'harga'    => $item['harga'],
                        'subtotal' => $item['subtotal'],
                    ]);
                    $obat = Obat::find($item['obat_id']);
                    if ($obat) {
                        $obat->deductStockFEFO($item['jumlah']);
                    }
                }

                // Create pembayaran record
                $penjualan->pembayaran()->create([
                    'metode_pembayaran' => 'tunai',
                    'status_pembayaran' => 'berhasil',
                    'nomor_referensi'   => $penjualan->kode_transaksi,
                ]);
            } else {
                // For non-cash: create details without decrement (wait for callback)
                foreach ($details as $item) {
                    $penjualan->detailPenjualans()->create([
                        'obat_id'  => $item['obat_id'],
                        'jumlah'   => $item['jumlah'],
                        'harga'    => $item['harga'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                $penjualan->pembayaran()->create([
                    'metode_pembayaran' => 'non-tunai',
                    'status_pembayaran' => 'pending',
                    'nomor_referensi'   => null,
                ]);
            }

            return $penjualan;
        });
    }
}
