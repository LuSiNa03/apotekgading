<x-filament-panels::page>
    @if ($this->isPendingNonTunai())
        <div id="midtrans-payment-section" class="mb-6">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        @svg('heroicon-o-credit-card', 'w-5 h-5 text-warning-500')
                        <span>Pembayaran Midtrans</span>
                    </div>
                </x-slot>
                <x-slot name="description">
                    Klik tombol "Bayar dengan Midtrans" di atas atau tombol di bawah untuk membuka popup pembayaran.
                </x-slot>

                <div class="flex flex-col items-center gap-4 py-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($this->getRecord()->total_harga, 0, ',', '.') }}
                        </p>
                        <p class="text-sm text-gray-500 mt-1">Total yang harus dibayar</p>
                    </div>

                    <x-filament::button
                        color="success"
                        size="lg"
                        icon="heroicon-o-credit-card"
                        onclick="openMidtransSnap()"
                    >
                        Bayar Sekarang
                    </x-filament::button>

                    <div id="midtrans-status-msg" class="hidden text-center p-3 rounded-lg w-full max-w-md"></div>
                </div>
            </x-filament::section>
        </div>
    @endif

    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Detail Transaksi</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Kode Transaksi</p>
                    <p class="font-semibold">{{ $this->getRecord()->kode_transaksi }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tanggal</p>
                    <p class="font-semibold">{{ $this->getRecord()->created_at->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Kasir</p>
                    <p class="font-semibold">{{ $this->getRecord()->user?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Metode Pembayaran</p>
                    <p class="font-semibold">{{ strtoupper($this->getRecord()->metode_pembayaran) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status Pembayaran</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($this->getRecord()->status_pembayaran === 'berhasil') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($this->getRecord()->status_pembayaran === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        @endif">
                        {{ strtoupper($this->getRecord()->status_pembayaran) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Harga</p>
                    <p class="font-semibold text-lg">Rp {{ number_format($this->getRecord()->total_harga, 0, ',', '.') }}</p>
                </div>

                @if ($this->getRecord()->metode_pembayaran === 'tunai')
                    <div>
                        <p class="text-sm text-gray-500">Nominal Bayar</p>
                        <p class="font-semibold">Rp {{ number_format($this->getRecord()->nominal_bayar ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Kembalian</p>
                        <p class="font-semibold">Rp {{ number_format($this->getRecord()->kembalian ?? 0, 0, ',', '.') }}</p>
                    </div>
                @endif

                @if ($this->getRecord()->pembayaran && $this->getRecord()->pembayaran->nomor_referensi)
                    <div>
                        <p class="text-sm text-gray-500">Nomor Referensi Midtrans</p>
                        <p class="font-semibold font-mono text-sm">{{ $this->getRecord()->pembayaran->nomor_referensi }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Metode Pembayaran (Midtrans)</p>
                        <p class="font-semibold">{{ strtoupper($this->getRecord()->pembayaran->metode_pembayaran) }}</p>
                    </div>
                @endif
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Detail Obat</x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Nama Obat</th>
                            <th class="px-4 py-3 text-center">Jumlah</th>
                            <th class="px-4 py-3 text-right">Harga</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->getRecord()->detailPenjualans as $i => $detail)
                            <tr class="{{ $i % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }}">
                                <td class="px-4 py-3">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-medium">{{ $detail->obat?->nama_obat ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $detail->jumlah }}</td>
                                <td class="px-4 py-3 text-right">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-gray-300 dark:border-gray-600">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right font-bold">TOTAL</td>
                            <td class="px-4 py-3 text-right font-bold text-lg">
                                Rp {{ number_format($this->getRecord()->total_harga, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-filament::section>
    </div>

    @if ($this->isPendingNonTunai())
        @php
            $snapUrl = $this->isMidtransProduction()
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js';
        @endphp

        <script src="{{ $snapUrl }}" data-client-key="{{ $this->getMidtransClientKey() }}"></script>
        <script>
            function openMidtransSnap() {
                const statusMsg = document.getElementById('midtrans-status-msg');

                fetch("{{ $this->getSnapTokenUrl() }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        showStatus('error', data.error);
                        return;
                    }

                    window.snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            showStatus('success', 'Pembayaran berhasil! Halaman akan dimuat ulang...');
                            setTimeout(() => location.reload(), 2000);
                        },
                        onPending: function(result) {
                            showStatus('warning', 'Pembayaran pending. Silakan selesaikan pembayaran Anda.');
                        },
                        onError: function(result) {
                            showStatus('error', 'Pembayaran gagal. Silakan coba lagi.');
                        },
                        onClose: function() {
                            showStatus('info', 'Popup pembayaran ditutup. Klik "Bayar Sekarang" untuk mencoba lagi.');
                        }
                    });
                })
                .catch(error => {
                    showStatus('error', 'Gagal memuat pembayaran: ' + error.message);
                });
            }

            function showStatus(type, message) {
                const statusMsg = document.getElementById('midtrans-status-msg');
                statusMsg.classList.remove('hidden');

                const colors = {
                    success: 'bg-green-100 text-green-800 border border-green-300',
                    warning: 'bg-yellow-100 text-yellow-800 border border-yellow-300',
                    error: 'bg-red-100 text-red-800 border border-red-300',
                    info: 'bg-blue-100 text-blue-800 border border-blue-300',
                };

                statusMsg.className = 'text-center p-3 rounded-lg w-full max-w-md ' + (colors[type] || colors.info);
                statusMsg.textContent = message;
            }
        </script>
    @endif
</x-filament-panels::page>
