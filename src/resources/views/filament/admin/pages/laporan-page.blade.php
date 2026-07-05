<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Form Filter --}}
        <x-filament::section>
            <x-slot name="heading">Filter Laporan</x-slot>

            <form wire:submit.prevent="generate">
                {{ $this->form }}

                <div class="mt-4">
                    <x-filament::button type="submit" icon="heroicon-m-funnel">
                        Generate Laporan
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        {{-- Tabel Hasil --}}
        @if ($sudah_generate)
            <x-filament::section>
                <x-slot name="heading">
                    Hasil Laporan: {{ ucfirst(str_replace('_', ' ', $jenis_laporan)) }}
                    @if ($jenis_laporan !== 'stok')
                        <span class="text-sm font-normal text-gray-500 ml-2">
                            ({{ \Illuminate\Support\Carbon::parse($tanggal_mulai)->format('d M Y') }} —
                             {{ \Illuminate\Support\Carbon::parse($tanggal_selesai)->format('d M Y') }})
                        </span>
                    @endif
                </x-slot>

                @if (count($data_laporan) === 0)
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mb-3"/>
                        <p class="text-lg font-medium">Tidak ada data untuk periode ini</p>
                        <p class="text-sm">Coba ubah filter tanggal atau jenis laporan</p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
                            <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                                <tr>
                                    @foreach (array_keys($data_laporan[0]) as $header)
                                        <th class="px-4 py-3 font-semibold">
                                            {{ ucwords(str_replace('_', ' ', $header)) }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data_laporan as $i => $row)
                                    <tr class="{{ $i % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800' }} border-b border-gray-100 dark:border-gray-700">
                                        @foreach ($row as $key => $value)
                                            <td class="px-4 py-3">
                                                @if (in_array($key, ['total', 'harga_jual', 'total_pendapatan', 'total_nominal']))
                                                    Rp {{ number_format((int)$value, 0, ',', '.') }}
                                                @elseif ($key === 'status_stok')
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold
                                                        {{ $value === 'KRITIS' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                                        {{ $value }}
                                                    </span>
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between mt-4 text-sm text-gray-500">
                        <span>Total: <strong>{{ count($data_laporan) }}</strong> record</span>
                        <div class="flex gap-2">
                            <x-filament::button
                                wire:click="downloadCsv"
                                color="success"
                                icon="heroicon-o-table-cells"
                                size="sm"
                            >
                                Export CSV
                            </x-filament::button>

                            <x-filament::button
                                wire:click="downloadPdf"
                                color="danger"
                                icon="heroicon-o-document-arrow-down"
                                size="sm"
                            >
                                Export PDF
                            </x-filament::button>
                        </div>
                    </div>
                @endif
            </x-filament::section>
        @endif

    </div>
</x-filament-panels::page>
