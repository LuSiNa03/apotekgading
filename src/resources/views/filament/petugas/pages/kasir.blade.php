<x-filament-panels::page>
    <style>
        @keyframes bounce-short {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
        .animate-bounce-short {
            animation: bounce-short 0.4s ease-out 1;
        }
        .active-click-scale:active {
            transform: scale(0.96);
        }
    </style>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kiri: Daftar Menu Obat -->
        <div class="lg:col-span-2 space-y-4">

            <!-- ═══ SEARCH BAR ═══ -->
            <div class="relative mb-6">
                <input
                    type="text"
                    placeholder="Cari nama obat..."
                    wire:model.live.debounce.300ms="search"
                    class="w-full pl-4 pr-4 py-3 rounded-2xl
                           border border-gray-200 dark:border-gray-700
                           bg-white dark:bg-gray-800
                           text-gray-800 dark:text-gray-100
                           placeholder-gray-400 dark:placeholder-gray-500
                           shadow-sm
                           caret-emerald-500 dark:caret-emerald-450
                           focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent
                           transition-all duration-200"
                />
            </div>

            <!-- ═══ FILTER KATEGORI ═══ -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-2">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                    Kategori Obat
                </p>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Semua --}}
                    <button
                        type="button"
                        wire:click="$set('kategoriId', '')"
                        @class([
                            'inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold border transition-all duration-200 shadow-sm text-gray-800 dark:text-gray-900',
                            'bg-gray-200 border-gray-300'        => $kategoriId === '',
                            'bg-gray-50 border-gray-200 hover:bg-gray-100' => $kategoriId !== '',
                        ])
                    >
                        Semua
                    </button>

                    @foreach ($this->kategoriList as $kat)
                        <button
                            type="button"
                            wire:click="$set('kategoriId', {{ $kat->id }})"
                            @class([
                                'inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold border transition-all duration-200 shadow-sm text-gray-800 dark:text-gray-900',
                                'bg-gray-200 border-gray-300'        => $kategoriId == $kat->id,
                                'bg-gray-50 border-gray-200 hover:bg-gray-100' => $kategoriId != $kat->id,
                            ])
                        >
                            {{ $kat->nama_kategori }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- ═══ FILTER PENYAKIT ═══ -->
            @if($this->penyakitList->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-2">
                <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                    Filter Penyakit
                </p>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Semua Penyakit --}}
                    <button
                        type="button"
                        wire:click="$set('penyakitId', '')"
                        @class([
                            'inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold border transition-all duration-200 shadow-sm text-gray-800 dark:text-gray-900',
                            'bg-gray-200 border-gray-300'                    => $penyakitId === '',
                            'bg-gray-50 border-gray-200 hover:bg-gray-100'   => $penyakitId !== '',
                        ])
                    >
                        Semua Penyakit
                    </button>

                    @foreach ($this->penyakitList as $peny)
                        <button
                            type="button"
                            wire:click="$set('penyakitId', {{ $peny->id }})"
                            @class([
                                'inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold border transition-all duration-200 shadow-sm text-gray-800 dark:text-gray-900',
                                'bg-gray-200 border-gray-300'                    => $penyakitId == $peny->id,
                                'bg-gray-50 border-gray-200 hover:bg-gray-100'   => $penyakitId != $peny->id,
                            ])
                        >
                            {{ $peny->nama_penyakit }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- ═══ GRID PRODUK ═══ -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @forelse ($this->obatList as $obat)
                    @php
                        $isExpiringSoon  = $obat->tanggal_kedaluwarsa
                                           && !$obat->tanggal_kedaluwarsa->isPast()
                                           && $obat->tanggal_kedaluwarsa->diffInDays(now()) < 30;
                        $isExpired       = $obat->tanggal_kedaluwarsa && $obat->tanggal_kedaluwarsa->isPast();
                        $isCriticalStock = $obat->stok <= $obat->stok_minimum;

                        /* Warna badge kategori dinamis berdasarkan nama */
                        $kategoriNama = strtolower($obat->kategoriObat?->nama_kategori ?? '');
                        $katBadgeClass = match(true) {
                            str_contains($kategoriNama, 'keras')        => 'bg-red-500 text-white',
                            str_contains($kategoriNama, 'narkotika')    => 'bg-rose-700 text-white',
                            str_contains($kategoriNama, 'psikotropika') => 'bg-purple-500 text-white',
                            str_contains($kategoriNama, 'terbatas')     => 'bg-orange-400 text-white',
                            str_contains($kategoriNama, 'bebas')        => 'bg-emerald-500 text-white',
                            str_contains($kategoriNama, 'vitamin')      => 'bg-amber-400 text-gray-900',
                            str_contains($kategoriNama, 'herbal')       => 'bg-lime-500 text-white',
                            default                                      => 'bg-sky-500 text-white',
                        };
                        /* Label singkat badge kategori */
                        $katLabel = match(true) {
                            str_contains($kategoriNama, 'keras')        => 'Keras',
                            str_contains($kategoriNama, 'narkotika')    => 'Narkotika',
                            str_contains($kategoriNama, 'psikotropika') => 'Psikotropika',
                            str_contains($kategoriNama, 'terbatas')     => 'Terbatas',
                            str_contains($kategoriNama, 'bebas')        => 'Bebas',
                            str_contains($kategoriNama, 'vitamin')      => 'Vitamin',
                            str_contains($kategoriNama, 'herbal')       => 'Herbal',
                            default                                      => $obat->kategoriObat?->nama_kategori ?? '—',
                        };
                    @endphp

                    {{-- ── KARTU MENU ── --}}
                    <div
                        wire:click="addToCart({{ $obat->id }})"
                        class="group flex flex-col h-full bg-white dark:bg-gray-800
                               rounded-3xl overflow-hidden
                               border border-gray-100 dark:border-gray-700
                               shadow-sm hover:shadow-md hover:-translate-y-0.5
                               transition-all duration-200 cursor-pointer select-none
                               active-click-scale"
                    >
                        {{-- ① GAMBAR (1:1 Ratio Cover) --}}
                        <div class="relative w-full aspect-square flex-shrink-0 overflow-hidden bg-gray-55 dark:bg-gray-900">

                            @if($obat->foto)
                                <img
                                    src="{{ asset('storage/' . $obat->foto) }}"
                                    alt="{{ $obat->nama_obat }}"
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                >
                            @else
                                <div class="w-full h-full flex items-center justify-center
                                            bg-gradient-to-br from-emerald-50 to-teal-100
                                            dark:from-gray-900 dark:to-gray-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke-width="1" stroke="currentColor"
                                         class="w-16 h-16 text-emerald-300 dark:text-emerald-700
                                                transition-transform duration-300 group-hover:scale-105">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9.75 3.104v1.242c0 .289.139.56.378.725l4.744 3.3c.24.166.378.437.378.726v1.242m-5.5 0a3 3 0 11-6 0 3 3 0 016 0zm9.5 0a3 3 0 11-6 0 3 3 0 016 0zm-9.5 7.5h.008v.008H7.5v-.008zm3 0h.008v.008h-.008v-.008zm3 0h.008v.008h-.008v-.008zm3 0h.008v.008h-.008v-.008zm-9 3h.008v.008H7.5v-.008zm3 0h.008v.008h-.008v-.008zm3 0h.008v.008h-.008v-.008zm3 0h.008v.008h-.008v-.008z"/>
                                    </svg>
                                </div>
                            @endif

                            {{-- Badge kategori — pojok KIRI ATAS --}}
                            @if($obat->kategoriObat)
                                <span class="absolute top-3 left-3
                                             {{ $katBadgeClass }}
                                             text-[10px] font-bold
                                             px-2.5 py-0.5 rounded-lg
                                             shadow-sm z-10 select-none">
                                    {{ $katLabel }}
                                </span>
                            @endif

                            {{-- Badge status — pojok KANAN ATAS --}}
                            <div class="absolute top-3 right-3 flex flex-col items-end gap-1.5 z-10">
                                @if($isCriticalStock)
                                    <span class="inline-flex items-center
                                                 bg-yellow-400 text-gray-900
                                                 text-[10px] font-bold
                                                 px-2.5 py-0.5 rounded-lg shadow-sm whitespace-nowrap">
                                        ⚠ Stok Menipis!
                                    </span>
                                @endif
                            </div>
                        </div>{{-- /gambar --}}

                        {{-- ② INFO BAWAH --}}
                        <style>
                            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap');
                            .font-poppins {
                                font-family: 'Poppins', sans-serif !important;
                            }
                        </style>
                        <div class="flex flex-col flex-1 p-4 gap-3 bg-white dark:bg-gray-800 font-poppins">

                            {{-- Wadah Info Obat (Nama, Kategori, Supplier, Exp) --}}
                            <div class="space-y-2">
                                {{-- Nama Obat --}}
                                <div class="flex items-center justify-between gap-2">
                                    <h3 class="font-bold text-gray-900 dark:text-white leading-snug line-clamp-2" style="font-size: 18px; color: #111827;">
                                        {{ $obat->nama_obat }}
                                    </h3>
                                    {{-- Lingkaran angka pesanan di samping nama menu --}}
                                    @if(isset($cart[$obat->id]))
                                        <span class="flex-shrink-0 w-7 h-7 rounded-full bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center shadow-md animate-bounce-short">
                                            {{ $cart[$obat->id]['jumlah'] }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Meta Info Detail --}}
                                <div class="space-y-1 font-bold leading-relaxed dark:text-gray-200" style="font-size: 14px; color: #1f2937;">
                                    <div>Kategori : {{ $obat->kategoriObat?->nama_kategori ?? '-' }}</div>
                                    <div>Supplier : {{ $obat->supplier?->nama_supplier ?? '-' }}</div>
                                    <div class="{{ $isExpiringSoon ? 'text-orange-600 font-extrabold' : '' }}" style="{{ !$isExpiringSoon ? 'color: #1f2937;' : '' }}">
                                        exp : {{ $obat->tanggal_kedaluwarsa?->format('d M Y') ?? '-' }}
                                    </div>
                                </div>

                                {{-- Chip Penyakit --}}
                                @if($obat->penyakits->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 pt-1">
                                        @foreach($obat->penyakits as $peny)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold
                                                         bg-emerald-100 text-emerald-700
                                                         dark:bg-emerald-950/40 dark:text-emerald-400
                                                         border border-emerald-200 dark:border-emerald-800">
                                                {{ $peny->nama_penyakit }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Divider --}}
                            <div class="border-t border-emerald-100 dark:border-gray-700"></div>

                            {{-- Wadah Deskripsi --}}
                            <div class="h-[90px] overflow-y-auto scrollbar-none pr-1">
                                @if($obat->deskripsi)
                                    <p class="leading-relaxed font-normal text-justify dark:text-gray-300" style="font-size: 12px; color: #4b5563;">
                                        {{ $obat->deskripsi }}
                                    </p>
                                @else
                                    <p class="leading-relaxed font-normal text-justify text-gray-400 dark:text-gray-500 italic" style="font-size: 12px;">
                                        Tidak ada deskripsi obat.
                                    </p>
                                @endif
                            </div>

                            {{-- FOOTER: Harga (kiri) & Stok (kanan) --}}
                            <div class="mt-auto pt-3 border-t border-emerald-100 dark:border-gray-700
                                        flex items-center justify-between font-bold" style="font-size: 13px;">
                                <span class="text-emerald-650 dark:text-emerald-400 font-extrabold" style="font-size: 16px;">
                                    Rp {{ number_format($obat->harga_jual, 0, ',', '.') }}
                                </span>
                                <span class="text-gray-550 dark:text-gray-300" style="color: #4b5563;">
                                    Stok : {{ $obat->stok }}
                                </span>
                            </div>

                        </div>{{-- /info bawah --}}
                    </div>{{-- /kartu --}}

                @empty
                    <div class="col-span-full bg-white dark:bg-gray-800 p-12 rounded-2xl
                                border border-gray-100 dark:border-gray-700 text-center">
                        @svg('heroicon-o-magnifying-glass', 'w-16 h-16 mx-auto mb-3 text-gray-300 dark:text-gray-600')
                        <h4 class="font-bold text-gray-800 dark:text-gray-200">Obat Tidak Ditemukan</h4>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            Coba ubah kata kunci pencarian atau pilih kategori / penyakit lain.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>


        <!-- Kanan: Keranjang & Pembayaran POS -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 space-y-6 self-start sticky top-6">
            <div class="flex items-center justify-between border-b pb-4 dark:border-gray-700">
                <h2 class="font-bold text-base flex items-center gap-2">
                    @svg('heroicon-o-shopping-cart', 'w-5 h-5 text-emerald-500')
                    <span>Keranjang Pesanan</span>
                </h2>
                <button type="button" wire:click="clearCart" class="text-xs font-bold text-red-500 hover:text-red-700 transition">
                    Kosongkan
                </button>
            </div>

            <!-- List Cart Items -->
            <div class="space-y-4 max-h-[320px] overflow-y-auto pr-1 scrollbar-thin">
                @forelse ($cart as $id => $item)
                    <div class="flex items-center justify-between gap-3 border-b pb-4 dark:border-gray-700">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-sm truncate dark:text-white">{{ $item['nama'] }}</h4>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
                                Rp {{ number_format($item['harga'], 0, ',', '.') }}
                            </p>
                        </div>
                        
                        <!-- Qty Selector -->
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="updateQuantity({{ $id }}, {{ $item['jumlah'] - 1 }})"
                                    class="w-7 h-7 rounded-lg bg-gray-150 dark:bg-gray-700 flex items-center justify-center hover:bg-gray-200 dark:hover:bg-gray-650 transition">
                                <span class="font-bold text-sm">-</span>
                            </button>
                            <span class="text-sm font-bold w-6 text-center dark:text-white">{{ $item['jumlah'] }}</span>
                            <button type="button" wire:click="updateQuantity({{ $id }}, {{ $item['jumlah'] + 1 }})"
                                    class="w-7 h-7 rounded-lg bg-gray-150 dark:bg-gray-700 flex items-center justify-center hover:bg-gray-200 dark:hover:bg-gray-650 transition">
                                <span class="font-bold text-sm">+</span>
                            </button>
                        </div>

                        <!-- Trash Button -->
                        <button type="button" wire:click="removeFromCart({{ $id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-red-650 transition">
                            @svg('heroicon-o-trash', 'w-4 h-4')
                        </button>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-400">
                        @svg('heroicon-o-shopping-bag', 'w-14 h-14 mx-auto mb-2 text-gray-300 dark:text-gray-600')
                        <p class="text-xs font-semibold">Pesanan masih kosong</p>
                    </div>
                @endforelse
            </div>

            <!-- Ringkasan & Form Pembayaran -->
            <div class="border-t pt-4 dark:border-gray-700 space-y-4">
                <div class="flex justify-between font-extrabold text-base">
                    <span>Subtotal</span>
                    <span class="text-emerald-650 dark:text-emerald-400">
                        Rp {{ number_format($totalHarga, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Metode Pembayaran -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                        Metode Pembayaran
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="metodePembayaran" class="w-full">
                            <option value="tunai">Tunai / Cash</option>
                            <option value="non-tunai">Non-Tunai (Midtrans QRIS/Snap)</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                @if ($metodePembayaran === 'tunai')
                    <!-- Nominal Bayar -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                            Nominal Bayar
                        </label>
                        <x-filament::input.wrapper prefix="Rp">
                            <x-filament::input
                                type="number"
                                wire:model.live="nominalBayar"
                                placeholder="Masukkan jumlah uang..."
                                min="{{ $totalHarga }}"
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <!-- Kembalian -->
                    <div class="flex justify-between items-center text-sm bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded-xl border border-gray-100 dark:border-gray-800">
                        <span class="text-gray-500">Uang Kembalian</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($kembalian, 0, ',', '.') }}
                        </span>
                    </div>
                @endif

                <!-- Submit Button -->
                <x-filament::button
                    type="button"
                    wire:click="submitOrder"
                    color="success"
                    size="lg"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold hover:shadow-lg hover:shadow-emerald-500/20 transition-all duration-300"
                    :disabled="empty($cart) || ($metodePembayaran === 'tunai' && $nominalBayar < $totalHarga)"
                >
                    Proses Pembayaran
                </x-filament::button>
            </div>
        </div>
    </div>

    <!-- Success Modal & Detail View -->
    @if ($showSuccessModal && $lastPenjualanId)
        @php
            $penjualan = \App\Models\Penjualan::find($lastPenjualanId);
            $isPending = $penjualan && $penjualan->status_pembayaran === 'pending';
        @endphp
        @if ($penjualan)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 w-full max-w-md p-6 space-y-6 transform transition-all duration-300 scale-100">
                    <div class="text-center space-y-2">
                        @if ($isPending)
                            <div class="w-14 h-14 bg-yellow-100 dark:bg-yellow-950/30 rounded-full flex items-center justify-center mx-auto text-yellow-600 dark:text-yellow-400">
                                @svg('heroicon-o-clock', 'w-8 h-8 animate-pulse')
                            </div>
                            <h3 class="text-lg font-extrabold text-gray-800 dark:text-gray-100">Menunggu Pembayaran...</h3>
                        @else
                            <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-950/30 rounded-full flex items-center justify-center mx-auto text-emerald-600 dark:text-emerald-400">
                                @svg('heroicon-o-check', 'w-8 h-8')
                            </div>
                            <h3 class="text-lg font-extrabold text-gray-800 dark:text-gray-100">Transaksi Berhasil!</h3>
                        @endif
                        <p class="text-xs text-gray-400 font-mono">{{ $penjualan->kode_transaksi }}</p>
                    </div>

                    <div class="space-y-2 border-y py-4 dark:border-gray-700 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Transaksi</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Metode Pembayaran</span>
                            <span class="font-bold uppercase text-emerald-600 dark:text-emerald-400">{{ $penjualan->metode_pembayaran }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status Pembayaran</span>
                            <span class="font-bold uppercase @if($penjualan->status_pembayaran === 'berhasil') text-emerald-600 dark:text-emerald-400 @else text-yellow-600 dark:text-yellow-400 @endif">{{ $penjualan->status_pembayaran }}</span>
                        </div>
                        @if ($penjualan->metode_pembayaran === 'tunai')
                            <div class="flex justify-between">
                                <span class="text-gray-500">Nominal Diterima</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">Rp {{ number_format($penjualan->nominal_bayar, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Uang Kembalian</span>
                                <span class="font-bold text-emerald-600">Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col gap-2">
                        @if ($isPending && $snapToken)
                            <button
                                type="button"
                                onclick="openMidtransSnapToken('{{ $snapToken }}')"
                                class="w-full py-2.5 rounded-xl bg-amber-500 text-white font-bold hover:bg-amber-600 transition flex items-center justify-center gap-2"
                            >
                                @svg('heroicon-o-credit-card', 'w-5 h-5')
                                Bayar Sekarang
                            </button>

                            <button
                                type="button"
                                wire:click="verifyPaymentStatus"
                                class="w-full py-2.5 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition flex items-center justify-center gap-2"
                            >
                                @svg('heroicon-o-arrow-path', 'w-5 h-5 animate-spin-slow')
                                Cek Status Pembayaran
                            </button>
                        @endif

                        <div class="flex gap-3 mt-2">
                            <x-filament::button
                                tag="a"
                                href="{{ route('penjualan.struk', $penjualan) }}"
                                target="_blank"
                                color="gray"
                                icon="heroicon-o-printer"
                                class="flex-1 rounded-xl"
                                :disabled="$isPending"
                            >
                                Cetak Struk
                            </x-filament::button>

                            <x-filament::button
                                type="button"
                                wire:click="$set('showSuccessModal', false)"
                                color="primary"
                                class="flex-1 rounded-xl"
                            >
                                Selesai
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    @php
        $snapUrl = $this->isMidtransProduction()
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    @endphp
    <script src="{{ $snapUrl }}" data-client-key="{{ $this->getMidtransClientKey() }}"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('trigger-snap-pay', (event) => {
                const token = event.snapToken;
                if (!token) return;
                
                window.snap.pay(token, {
                    onSuccess: function(result) {
                        @this.verifyPaymentStatus();
                    },
                    onPending: function(result) {
                        @this.verifyPaymentStatus();
                    },
                    onError: function(result) {
                        @this.verifyPaymentStatus();
                    },
                    onClose: function() {
                        // user closed popup
                    }
                });
            });
        });

        function openMidtransSnapToken(token) {
            if (!token) return;
            window.snap.pay(token, {
                onSuccess: function(result) {
                    @this.verifyPaymentStatus();
                },
                onPending: function(result) {
                    @this.verifyPaymentStatus();
                },
                onError: function(result) {
                    @this.verifyPaymentStatus();
                },
                onClose: function() {
                }
            });
        }
    </script>
</x-filament-panels::page>
