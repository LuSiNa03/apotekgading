<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;

class StrukController extends Controller
{
    /**
     * Tampilkan halaman cetak struk 58mm untuk transaksi tertentu.
     */
    public function show(Penjualan $penjualan)
    {
        $penjualan->load(['user', 'detailPenjualans.obat', 'pembayaran']);
        return view('struk.show', compact('penjualan'));
    }
}
