<?php

namespace App\Filament\Admin\Pages;

use App\Models\Obat;
use App\Models\ObatMasuk;
use App\Models\Penjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use App\Traits\HasLockedPageNavigation;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;

class LaporanPage extends Page implements HasForms
{
    use InteractsWithForms, HasLockedPageNavigation;

    public static function canAccess(): bool
    {
        return auth()->user() && auth()->user()->can('view_any_penjualan');
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan & Export';

    protected static ?string $title = 'Laporan & Export';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.admin.pages.laporan-page';

    // Form state
    public ?array $data = [];
    public ?string $jenis_laporan = 'penjualan';
    public ?string $tanggal_mulai = null;
    public ?string $tanggal_selesai = null;

    // Data hasil query
    public array $data_laporan = [];
    public bool $sudah_generate = false;

    public function mount(): void
    {
        $this->tanggal_mulai   = Carbon::now()->startOfMonth()->toDateString();
        $this->tanggal_selesai = Carbon::now()->toDateString();
        $this->form->fill([
            'jenis_laporan'   => 'penjualan',
            'tanggal_mulai'   => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('jenis_laporan')
                    ->label('Jenis Laporan')
                    ->options([
                        'penjualan' => 'Laporan Penjualan',
                        'obat_masuk' => 'Laporan Obat Masuk',
                        'stok' => 'Laporan Stok Obat',
                        'pendapatan' => 'Laporan Pendapatan',
                        'metode_pembayaran' => 'Laporan Metode Pembayaran',
                        'supplier' => 'Laporan Supplier',
                        'aktivitas_pengguna' => 'Laporan Aktivitas Pengguna',
                    ])
                    ->required()
                    ->default('penjualan')
                    ->live(),
                DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->required()
                    ->hidden(fn (\Filament\Forms\Get $get) => in_array($get('jenis_laporan'), ['stok', 'supplier'])),
                DatePicker::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->required()
                    ->hidden(fn (\Filament\Forms\Get $get) => in_array($get('jenis_laporan'), ['stok', 'supplier'])),
            ])
            ->statePath('data')
            ->columns(3);
    }

    public function generate(): void
    {
        $formData = $this->form->getState();
        $this->jenis_laporan   = $formData['jenis_laporan'];
        $this->tanggal_mulai   = $formData['tanggal_mulai'] ?? null;
        $this->tanggal_selesai = $formData['tanggal_selesai'] ?? null;

        $this->data_laporan = match ($this->jenis_laporan) {
            'penjualan'          => $this->queryPenjualan(),
            'obat_masuk'         => $this->queryObatMasuk(),
            'stok'               => $this->queryStok(),
            'pendapatan'         => $this->queryPendapatan(),
            'metode_pembayaran'  => $this->queryMetodePembayaran(),
            'supplier'           => $this->querySupplier(),
            'aktivitas_pengguna' => $this->queryAktivitasPengguna(),
            default              => [],
        };

        $this->sudah_generate = true;
    }

    private function queryPenjualan(): array
    {
        return Penjualan::with(['user', 'detailPenjualans'])
            ->whereBetween('created_at', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_selesai)->endOfDay(),
            ])
            ->where('status_pembayaran', 'berhasil')
            ->get()
            ->map(fn ($p) => [
                'kode'          => $p->kode_transaksi,
                'kasir'         => $p->user?->name,
                'total'         => $p->total_harga,
                'metode'        => $p->metode_pembayaran,
                'status'        => $p->status_pembayaran,
                'tanggal'       => $p->created_at->format('d/m/Y H:i'),
            ])
            ->toArray();
    }

    private function queryObatMasuk(): array
    {
        return ObatMasuk::with(['obat', 'supplier'])
            ->whereBetween('tanggal_masuk', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_selesai)->endOfDay(),
            ])
            ->get()
            ->map(fn ($m) => [
                'nomor'    => $m->nomor_transaksi,
                'obat'     => $m->obat?->nama_obat,
                'supplier' => $m->supplier?->nama_supplier,
                'jumlah'   => $m->jumlah,
                'tanggal'  => Carbon::parse($m->tanggal_masuk)->format('d/m/Y H:i'),
            ])
            ->toArray();
    }

    private function queryStok(): array
    {
        return Obat::with('kategoriObat')
            ->get()
            ->map(fn ($o) => [
                'nama_obat'          => $o->nama_obat,
                'kategori'           => $o->kategoriObat?->nama_kategori,
                'stok'               => $o->stok,
                'stok_minimum'       => $o->stok_minimum,
                'status_stok'        => $o->stok <= $o->stok_minimum ? 'KRITIS' : 'AMAN',
                'harga_jual'         => $o->harga_jual,
                'tanggal_kedaluwarsa' => $o->tanggal_kedaluwarsa?->format('d/m/Y'),
            ])
            ->toArray();
    }

    private function queryPendapatan(): array
    {
        return Penjualan::whereBetween('created_at', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_selesai)->endOfDay(),
            ])
            ->where('status_pembayaran', 'berhasil')
            ->selectRaw('DATE(created_at) as tanggal_trx, COUNT(id) as total_transaksi, SUM(total_harga) as total_pendapatan')
            ->groupBy('tanggal_trx')
            ->orderBy('tanggal_trx', 'desc')
            ->get()
            ->map(fn ($p) => [
                'tanggal'          => Carbon::parse($p->tanggal_trx)->format('d/m/Y'),
                'total_transaksi'  => $p->total_transaksi,
                'total_pendapatan' => (int) $p->total_pendapatan,
            ])
            ->toArray();
    }

    private function queryMetodePembayaran(): array
    {
        return Penjualan::whereBetween('created_at', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_selesai)->endOfDay(),
            ])
            ->where('status_pembayaran', 'berhasil')
            ->selectRaw('metode_pembayaran, COUNT(id) as total_transaksi, SUM(total_harga) as total_nominal')
            ->groupBy('metode_pembayaran')
            ->get()
            ->map(fn ($p) => [
                'metode_pembayaran' => strtoupper($p->metode_pembayaran),
                'total_transaksi'   => $p->total_transaksi,
                'total_nominal'     => (int) $p->total_nominal,
            ])
            ->toArray();
    }

    private function querySupplier(): array
    {
        return \App\Models\Supplier::withCount('obats')
            ->get()
            ->map(fn ($s) => [
                'nama_supplier'         => $s->nama_supplier,
                'alamat'                => $s->alamat,
                'no_telp'               => $s->no_telp,
                'email'                 => $s->email ?? '-',
                'total_obat_disediakan' => $s->obats_count,
            ])
            ->toArray();
    }

    private function queryAktivitasPengguna(): array
    {
        if (!class_exists('\Spatie\Activitylog\Models\Activity')) {
            return [];
        }
        return \Spatie\Activitylog\Models\Activity::with('causer')
            ->whereBetween('created_at', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_selesai)->endOfDay(),
            ])
            ->latest()
            ->get()
            ->map(fn ($a) => [
                'waktu'     => $a->created_at->format('d/m/Y H:i:s'),
                'pengguna'  => $a->causer?->name ?? 'System',
                'log_nama'  => $a->log_name,
                'deskripsi' => $a->description,
            ])
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->visible(fn () => $this->sudah_generate && count($this->data_laporan) > 0)
                ->action(fn () => $this->downloadCsv()),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->visible(fn () => $this->sudah_generate && count($this->data_laporan) > 0)
                ->action(fn () => $this->downloadPdf()),
        ];
    }

    public function downloadCsv()
    {
        if (empty($this->data_laporan)) return;

        $headers = array_keys($this->data_laporan[0]);
        $filename = 'laporan_' . $this->jenis_laporan . '_' . date('Ymd') . '.csv';

        $csv = implode(',', $headers) . "\n";
        foreach ($this->data_laporan as $row) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $row)) . "\n";
        }

        return Response::streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function downloadPdf()
    {
        if (empty($this->data_laporan)) return;

        $pdf = Pdf::loadView('laporan.pdf', [
            'data'        => $this->data_laporan,
            'jenis'       => $this->jenis_laporan,
            'dari'        => $this->tanggal_mulai,
            'sampai'      => $this->tanggal_selesai,
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan_' . $this->jenis_laporan . '_' . date('Ymd') . '.pdf';
        return Response::streamDownload(fn () => print($pdf->output()), $filename);
    }
}
