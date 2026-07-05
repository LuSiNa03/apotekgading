<?php

namespace App\Filament\Petugas\Pages;

use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\Penjualan;
use App\Services\MidtransService;
use App\Traits\HasLockedPageNavigation;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class Kasir extends Page
{
    use HasLockedPageNavigation;

    public static function canAccess(): bool
    {
        return auth()->user() && auth()->user()->can('view_any_penjualan');
    }
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Kasir POS';

    protected static ?string $title = 'Kasir POS';

    protected static string $view = 'filament.petugas.pages.kasir';

    // Search and filter state
    #[Url]
    public $search = '';

    #[Url]
    public $kategoriId = '';

    // Cart state
    public $cart = [];
    public $totalHarga = 0;

    // Payment state
    public $metodePembayaran = 'tunai';
    public $nominalBayar = 0;
    public $kembalian = 0;
    public $statusPembayaran = 'berhasil';

    // Invoice created state
    public $lastPenjualanId = null;
    public $showSuccessModal = false;

    public function mount(): void
    {
        $this->resetPayment();
        $this->cart = cache()->get('pos_cart_' . auth()->id(), []);
        $this->hitungTotal();
    }

    public function resetPayment(): void
    {
        $this->nominalBayar = 0;
        $this->kembalian = 0;
        $this->metodePembayaran = 'tunai';
    }

    public function updatedNominalBayar($value): void
    {
        $bayar = (int) $value;
        $this->kembalian = max(0, $bayar - $this->totalHarga);
    }

    public function updatedMetodePembayaran($value): void
    {
        if ($value === 'non-tunai') {
            $this->nominalBayar = $this->totalHarga;
            $this->kembalian = 0;
            $this->statusPembayaran = 'pending';
        } else {
            $this->nominalBayar = 0;
            $this->statusPembayaran = 'berhasil';
        }
    }

    public function addToCart(int $obatId): void
    {
        $obat = Obat::find($obatId);

        if (!$obat) {
            Notification::make()->title('Obat tidak ditemukan')->danger()->send();
            return;
        }

        if ($obat->stok <= 0) {
            Notification::make()->title('Stok obat habis')->danger()->send();
            return;
        }

        $currentQty = isset($this->cart[$obatId]) ? $this->cart[$obatId]['jumlah'] : 0;

        if ($currentQty + 1 > $obat->stok) {
            Notification::make()->title('Stok tidak mencukupi')->danger()->send();
            return;
        }

        if (isset($this->cart[$obatId])) {
            $this->cart[$obatId]['jumlah']++;
            $this->cart[$obatId]['subtotal'] = $this->cart[$obatId]['jumlah'] * $this->cart[$obatId]['harga'];
        } else {
            $this->cart[$obatId] = [
                'id' => $obat->id,
                'nama' => $obat->nama_obat,
                'harga' => $obat->harga_jual,
                'jumlah' => 1,
                'subtotal' => $obat->harga_jual,
                'foto' => $obat->foto ?? null,
                'stok' => $obat->stok,
            ];
        }

        $this->hitungTotal();
        Notification::make()->title('Obat ditambahkan ke keranjang')->success()->send();
    }

    public function updateQuantity(int $obatId, int $qty): void
    {
        if ($qty <= 0) {
            $this->removeFromCart($obatId);
            return;
        }

        $obat = Obat::find($obatId);
        if ($obat && $qty > $obat->stok) {
            Notification::make()->title('Stok tidak mencukupi')->danger()->send();
            return;
        }

        if (isset($this->cart[$obatId])) {
            $this->cart[$obatId]['jumlah'] = $qty;
            $this->cart[$obatId]['subtotal'] = $qty * $this->cart[$obatId]['harga'];
        }

        $this->hitungTotal();
    }

    public function removeFromCart(int $obatId): void
    {
        if (isset($this->cart[$obatId])) {
            unset($this->cart[$obatId]);
        }
        $this->hitungTotal();
    }

    public function hitungTotal(): void
    {
        $this->totalHarga = collect($this->cart)->sum('subtotal');
        cache()->put('pos_cart_' . auth()->id(), $this->cart, now()->addDays(30));

        if ($this->metodePembayaran === 'non-tunai') {
            $this->nominalBayar = $this->totalHarga;
            $this->kembalian = 0;
        } else {
            $this->updatedNominalBayar($this->nominalBayar);
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->totalHarga = 0;
        cache()->forget('pos_cart_' . auth()->id());
        $this->resetPayment();
    }

    public function submitOrder()
    {
        if (empty($this->cart)) {
            Notification::make()->title('Keranjang belanja kosong')->danger()->send();
            return;
        }

        if ($this->metodePembayaran === 'tunai' && $this->nominalBayar < $this->totalHarga) {
            Notification::make()->title('Nominal bayar kurang dari total harga')->danger()->send();
            return;
        }

        return DB::transaction(function () {
            // Validasi stok ulang untuk menghindari race condition
            foreach ($this->cart as $item) {
                $obat = Obat::lockForUpdate()->find($item['id']);
                if (!$obat || $obat->stok < $item['jumlah']) {
                    Notification::make()->title('Stok tidak cukup untuk ' . ($obat?->nama_obat ?? 'obat'))->danger()->send();
                    return;
                }
            }

            $kodeTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            $status = $this->metodePembayaran === 'tunai' ? 'berhasil' : 'pending';

            $penjualan = Penjualan::create([
                'kode_transaksi' => $kodeTransaksi,
                'user_id' => auth()->id(),
                'total_harga' => $this->totalHarga,
                'metode_pembayaran' => $this->metodePembayaran,
                'status_pembayaran' => $status,
                'nominal_bayar' => $this->metodePembayaran === 'tunai' ? $this->nominalBayar : null,
                'kembalian' => $this->metodePembayaran === 'tunai' ? $this->kembalian : null,
            ]);

            foreach ($this->cart as $item) {
                $penjualan->detailPenjualans()->create([
                    'obat_id' => $item['id'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                ]);

                if ($this->metodePembayaran === 'tunai') {
                    Obat::where('id', $item['id'])->decrement('stok', $item['jumlah']);
                }
            }

            $penjualan->pembayaran()->create([
                'metode_pembayaran' => $this->metodePembayaran,
                'status_pembayaran' => $status,
                'nomor_referensi' => $this->metodePembayaran === 'tunai' ? $kodeTransaksi : null,
            ]);

            $this->lastPenjualanId = $penjualan->id;
            $this->showSuccessModal = true;

            $this->cart = [];
            $this->totalHarga = 0;
            cache()->forget('pos_cart_' . auth()->id());
            $this->resetPayment();

            Notification::make()->title('Transaksi berhasil dicatat')->success()->send();

            return $penjualan;
        });
    }

    public function getObatListProperty()
    {
        // Hanya tampilkan obat dengan stok > 0 DAN tanggal kedaluwarsa lebih dari h-1 (misal, minimal lusa / > besok)
        // Jadi, H-1 kedaluwarsa tidak akan muncul lagi
        $query = Obat::with(['kategoriObat', 'supplier'])
            ->where('stok', '>', 0)
            ->where('tanggal_kedaluwarsa', '>', now()->addDay()->endOfDay());

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nama_obat', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->kategoriId)) {
            $query->where('kategori_obat_id', $this->kategoriId);
        }

        return $query->latest()->get();
    }

    public function getKategoriListProperty()
    {
        return KategoriObat::all();
    }
}
