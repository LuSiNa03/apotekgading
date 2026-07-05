<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function __construct(protected MidtransService $midtransService) {}

    public function callback(Request $request): JsonResponse
    {
        try {
            $this->midtransService->handleCallback($request->all());
            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            Log::error('Midtrans callback error', [
                'message' => $e->getMessage(),
                'payload' => $request->all(),
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getSnapToken(Penjualan $penjualan): JsonResponse
    {
        try {
            if ($penjualan->status_pembayaran === 'berhasil') {
                return response()->json(['error' => 'Transaksi sudah berhasil dibayar'], 400);
            }

            if ($penjualan->metode_pembayaran !== 'non-tunai') {
                return response()->json(['error' => 'Transaksi bukan non-tunai'], 400);
            }

            $token = $this->midtransService->createSnapToken($penjualan);
            return response()->json([
                'snap_token' => $token,
                'client_key' => config('midtrans.client_key'),
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans snap token error', [
                'message'  => $e->getMessage(),
                'order_id' => $penjualan->kode_transaksi,
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkStatus(Penjualan $penjualan): JsonResponse
    {
        try {
            $status = $this->midtransService->checkStatus($penjualan->kode_transaksi);
            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Penjualan $penjualan): JsonResponse
    {
        try {
            $midtransStatus = $this->midtransService->checkStatus($penjualan->kode_transaksi);
            $this->midtransService->handleCallback($midtransStatus);

            $penjualan->refresh();
            return response()->json([
                'status'            => 'ok',
                'status_pembayaran' => $penjualan->status_pembayaran,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
