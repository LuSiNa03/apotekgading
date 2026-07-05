<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Illuminate\Support\Facades\Response;

/* NOTE: Do Not Remove
/ Livewire asset handling if using sub folder in domain
*/

Livewire::setUpdateRoute(function ($handle) {
    return Route::post(config('app.asset_prefix') . '/livewire/update', $handle);
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get(config('app.asset_prefix') . '/livewire/livewire.js', $handle);
});
/*
/ END
*/
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole('super_admin')) {
            return redirect('/admin');
        } elseif ($user->hasRole('pemilik_apotek')) {
            return redirect('/pemilik');
        } elseif ($user->hasRole('petugas_apotek')) {
            return redirect('/petugas');
        }
    }
    return redirect('/petugas/login');
});

// Cetak Struk (perlu autentikasi Filament)
Route::middleware(['auth'])->group(function () {
    Route::get('/penjualan/{penjualan}/struk', [\App\Http\Controllers\StrukController::class, 'show'])
        ->name('penjualan.struk');
    Route::get('/penjualan/{penjualan}/snap-token', [\App\Http\Controllers\MidtransController::class, 'getSnapToken'])
        ->name('penjualan.snap-token');
    Route::get('/penjualan/{penjualan}/midtrans-status', [\App\Http\Controllers\MidtransController::class, 'checkStatus'])
        ->name('penjualan.midtrans-status');
    Route::post('/penjualan/{penjualan}/midtrans-update', [\App\Http\Controllers\MidtransController::class, 'updateStatus'])
        ->name('penjualan.midtrans-update');
});

// Midtrans Webhook (tidak perlu autentikasi - CSRF dikecualikan)
Route::post('/api/midtrans/callback', [\App\Http\Controllers\MidtransController::class, 'callback'])
    ->name('midtrans.callback');

