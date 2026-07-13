<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filament::section>
                <x-slot name="heading">Total Produk</x-slot>
                <p class="text-3xl font-extrabold text-primary-600">
                    {{ \App\Models\Obat::count() }}
                </p>
            </x-filament::section>
            
            <x-filament::section>
                <x-slot name="heading">Stok Kritis / Habis</x-slot>
                <p class="text-3xl font-extrabold text-amber-600">
                    {{ \App\Models\Obat::whereColumn('stok', '<=', 'stok_minimum')->count() }}
                </p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Kedaluwarsa Kurang Dari 30 Hari</x-slot>
                <p class="text-3xl font-extrabold text-red-600">
                    {{ \App\Models\Obat::where('tanggal_kedaluwarsa', '<=', now()->addDays(30))->count() }}
                </p>
            </x-filament::section>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
