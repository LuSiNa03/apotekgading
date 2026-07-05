<?php

namespace App\Services;

use App\Models\Obat;
use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = config('midtrans.is_sanitized');
        Config::$is3ds        = config('midtrans.is_3ds');
        
        // Bypass SSL verification in cURL to prevent connection issues in Docker
        // Also define empty CURLOPT_HTTPHEADER to avoid PHP 8+ "Undefined array key 10023" bug in Midtrans SDK
        Config::$curlOptions = [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [],
        ];
    }

    public function createSnapToken(Penjualan $penjualan): string
    {
        $penjualan->load('detailPenjualans.obat', 'user');

        $itemDetails = $penjualan->detailPenjualans->map(function ($detail) {
            return [
                'id'       => (string) $detail->obat_id,
                'price'    => (int) $detail->harga,
                'quantity' => (int) $detail->jumlah,
                'name'     => mb_substr($detail->obat?->nama_obat ?? 'Obat', 0, 50),
            ];
        })->toArray();

        $calculatedTotal = collect($itemDetails)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $grossAmount = (int) $penjualan->total_harga;

        if ($calculatedTotal !== $grossAmount) {
            $itemDetails[] = [
                'id'       => 'ADJ',
                'price'    => $grossAmount - $calculatedTotal,
                'quantity' => 1,
                'name'     => 'Penyesuaian',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $penjualan->kode_transaksi,
                'gross_amount' => $grossAmount,
            ],
            'item_details'     => $itemDetails,
            'customer_details' => [
                'first_name' => $penjualan->user?->name ?? 'Kasir',
                'email'      => $penjualan->user?->email ?? 'kasir@apotek.com',
            ],
        ];

        Log::info('Midtrans: Creating snap token', ['order_id' => $penjualan->kode_transaksi]);

        return Snap::getSnapToken($params);
    }

    public function handleCallback(array $payload): void
    {
        $orderId           = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus       = $payload['fraud_status'] ?? null;

        Log::info('Midtrans: Callback received', [
            'order_id' => $orderId,
            'status'   => $transactionStatus,
            'fraud'    => $fraudStatus,
        ]);

        $signatureKey = hash('sha512',
            $orderId .
            ($payload['status_code'] ?? '') .
            ($payload['gross_amount'] ?? '') .
            config('midtrans.server_key')
        );

        if ($signatureKey !== ($payload['signature_key'] ?? '')) {
            Log::warning('Midtrans: Invalid signature', ['order_id' => $orderId]);
            abort(403, 'Invalid signature');
        }

        $penjualan = Penjualan::where('kode_transaksi', $orderId)->firstOrFail();
        $previousStatus = $penjualan->status_pembayaran;

        $status = $this->resolveStatus($transactionStatus, $fraudStatus);

        DB::transaction(function () use ($penjualan, $status, $previousStatus, $payload) {
            $penjualan->update(['status_pembayaran' => $status]);

            $penjualan->pembayaran()->updateOrCreate(
                ['penjualan_id' => $penjualan->id],
                [
                    'status_pembayaran' => $status,
                    'nomor_referensi'   => $payload['transaction_id'] ?? null,
                    'metode_pembayaran' => $payload['payment_type'] ?? 'non-tunai',
                ]
            );

            if ($status === 'berhasil' && $previousStatus !== 'berhasil') {
                foreach ($penjualan->detailPenjualans as $detail) {
                    Obat::where('id', $detail->obat_id)->decrement('stok', $detail->jumlah);
                }
                Log::info('Midtrans: Stock decremented', ['order_id' => $penjualan->kode_transaksi]);
            }
        });

        Log::info('Midtrans: Callback processed', [
            'order_id' => $orderId,
            'status'   => $status,
        ]);
    }

    public function checkStatus(string $orderId): array
    {
        Log::info('Midtrans: Checking status', ['order_id' => $orderId]);
        $response = Transaction::status($orderId);
        return (array) $response;
    }

    protected function resolveStatus(?string $transactionStatus, ?string $fraudStatus): string
    {
        if ($transactionStatus === 'capture') {
            return ($fraudStatus === 'accept') ? 'berhasil' : 'gagal';
        }

        if (in_array($transactionStatus, ['settlement', 'success'])) {
            return 'berhasil';
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'expire', 'failure'])) {
            return 'gagal';
        }

        return 'pending';
    }
}
