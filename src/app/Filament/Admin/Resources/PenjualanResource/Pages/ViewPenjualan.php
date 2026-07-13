<?php

namespace App\Filament\Admin\Resources\PenjualanResource\Pages;

use App\Filament\Admin\Resources\PenjualanResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPenjualan extends ViewRecord
{
    protected static string $resource = PenjualanResource::class;

    protected static string $view = 'filament.admin.resources.penjualan.view';

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $actions = [];

        $actions[] = Actions\DeleteAction::make();

        if ($record->metode_pembayaran === 'non-tunai' && $record->status_pembayaran === 'pending') {
            $actions[] = Actions\Action::make('bayar_midtrans')
                ->label('Bayar dengan Midtrans')
                ->icon('heroicon-o-credit-card')
                ->color('success')
                ->extraAttributes([
                    'id' => 'btn-bayar-midtrans',
                    'onclick' => 'openMidtransSnap()',
                ]);

            $actions[] = Actions\Action::make('refresh_status')
                ->label('Cek Status Pembayaran')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    try {
                        $service = app(\App\Services\MidtransService::class);
                        $midtransStatus = $service->checkStatus($this->getRecord()->kode_transaksi);
                        $service->handleCallback($midtransStatus);
                        $this->getRecord()->refresh();

                        Notification::make()
                            ->title('Status diperbarui: ' . strtoupper($this->getRecord()->status_pembayaran))
                            ->success()
                            ->send();

                        $this->redirect(PenjualanResource::getUrl('view', ['record' => $this->getRecord()]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal cek status: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }

        if ($record->status_pembayaran === 'berhasil') {
            $actions[] = Actions\Action::make('cetak')
                ->label('Cetak Struk')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('penjualan.struk', $this->getRecord()))
                ->openUrlInNewTab();
        }

        return $actions;
    }

    public function getSnapTokenUrl(): string
    {
        return route('penjualan.snap-token', $this->getRecord());
    }

    public function getMidtransClientKey(): string
    {
        return config('midtrans.client_key');
    }

    public function isMidtransProduction(): bool
    {
        return (bool) config('midtrans.is_production');
    }

    public function isPendingNonTunai(): bool
    {
        $record = $this->getRecord();
        return $record->metode_pembayaran === 'non-tunai' && $record->status_pembayaran === 'pending';
    }
}
