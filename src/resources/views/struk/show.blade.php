<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $penjualan->kode_transaksi }}</title>
    <style>
        /* Generic / Text Only — 54mm thermal printer */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            line-height: 1.4;
            width: 54mm;
            color: #000;
            background: #fff;
        }

        .wrap {
            width: 100%;
            padding: 2px 0;
        }

        /* Alignment helpers */
        .c  { text-align: center; }
        .r  { text-align: right; }
        .b  { font-weight: bold; }

        /* Divider lines (Text-only compatible) */
        .div-eq::before  { content: "================================"; }
        .div-dash::before{ content: "--------------------------------"; }
        .div-eq, .div-dash { text-align: center; overflow: hidden; white-space: nowrap; margin: 2px 0; }

        /* Row: label + value sejajar kanan */
        .row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            font-size: 10px;
            line-height: 1.45;
        }
        .row .lbl { flex: 1; white-space: nowrap; }
        .row .val { text-align: right; white-space: nowrap; padding-left: 4px; }

        /* Item baris */
        .item-name  { font-size: 10px; word-break: break-word; }
        .item-sub   { display: flex; justify-content: space-between; font-size: 10px; padding-left: 2px; }

        /* Total baris */
        .row-total  { display: flex; justify-content: space-between; font-weight: bold; font-size: 11px; }

        /* Footer */
        .footer { margin-top: 4px; font-size: 9px; text-align: center; line-height: 1.5; }

        /* Sembunyikan tombol saat print */
        @media print {
            .no-print { display: none !important; }
            body { width: 54mm; }
        }

        /* Pratinjau di layar */
        @media screen {
            body {
                margin: 20px auto;
                border: 1px dashed #aaa;
                padding: 8px;
                box-shadow: 0 2px 12px rgba(0,0,0,.1);
            }
            .no-print {
                margin-bottom: 10px;
                text-align: center;
            }
            .no-print button {
                background: #1e40af;
                color: #fff;
                border: none;
                padding: 7px 18px;
                border-radius: 6px;
                font-size: 12px;
                cursor: pointer;
                margin-right: 5px;
            }
            .no-print button:hover { background: #1e3a8a; }
            .no-print .btn-close   { background: #6b7280; }
            .no-print .btn-close:hover { background: #374151; }
        }
    </style>
</head>
<body>

    <!-- Tombol (hanya layar) -->
    <div class="no-print">
        <button onclick="window.print()">🖨️ Cetak Struk</button>
        <button class="btn-close" onclick="window.close()">✕ Tutup</button>
    </div>

    @php
        $identitas = \App\Models\IdentitasApotek::getSingle();
    @endphp

    <div class="wrap">

        {{-- ===== HEADER ===== --}}
        <div class="c b" style="font-size:12px; letter-spacing:0.5px;">
            {{ strtoupper($identitas->nama_apotek) }}
        </div>
        <div class="c" style="font-size:9px; margin-top:2px;">
            {{ $identitas->alamat }}
        </div>
        @if($identitas->no_telp)
        <div class="c" style="font-size:9px;">Telp: {{ $identitas->no_telp }}</div>
        @endif
        @if($identitas->email)
        <div class="c" style="font-size:9px;">{{ $identitas->email }}</div>
        @endif

        <div class="div-eq"></div>

        {{-- ===== INFO TRANSAKSI ===== --}}
        <div class="row">
            <span class="lbl">No. Transaksi</span>
            <span class="val">{{ $penjualan->kode_transaksi }}</span>
        </div>
        <div class="row">
            <span class="lbl">Tanggal</span>
            <span class="val">{{ $penjualan->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span class="lbl">Kasir</span>
            <span class="val">{{ $penjualan->user?->name ?? '-' }}</span>
        </div>

        <div class="div-dash"></div>

        {{-- ===== DETAIL ITEM ===== --}}
        <div class="row b" style="font-size:9px;">
            <span class="lbl">Nama Obat</span>
            <span class="val">Subtotal</span>
        </div>
        <div class="div-dash"></div>

        @foreach ($penjualan->detailPenjualans as $detail)
            <div class="item-name">{{ $detail->obat?->nama_obat ?? '-' }}</div>
            <div class="item-sub">
                <span>{{ $detail->jumlah }} x {{ number_format($detail->harga, 0, ',', '.') }}</span>
                <span>{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
            </div>
        @endforeach

        <div class="div-eq"></div>

        {{-- ===== RINGKASAN PEMBAYARAN ===== --}}
        <div class="row-total">
            <span>TOTAL</span>
            <span>Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</span>
        </div>

        <div class="div-dash"></div>

        <div class="row">
            <span class="lbl">Metode</span>
            <span class="val">{{ strtoupper($penjualan->metode_pembayaran) }}</span>
        </div>

        @if ($penjualan->metode_pembayaran === 'tunai')
        <div class="row">
            <span class="lbl">Bayar</span>
            <span class="val">Rp {{ number_format($penjualan->nominal_bayar ?? 0, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span class="lbl">Kembali</span>
            <span class="val">Rp {{ number_format($penjualan->kembalian ?? 0, 0, ',', '.') }}</span>
        </div>
        @endif

        @if ($penjualan->pembayaran && $penjualan->pembayaran->nomor_referensi)
        <div class="row" style="font-size:9px;">
            <span class="lbl">No. Ref</span>
            <span class="val">{{ $penjualan->pembayaran->nomor_referensi }}</span>
        </div>
        @endif

        <div class="row">
            <span class="lbl">Status</span>
            <span class="val b">{{ strtoupper($penjualan->status_pembayaran) }}</span>
        </div>

        <div class="div-eq"></div>

        {{-- ===== FOOTER ===== --}}
        <div class="footer">
            <div>Terima kasih atas kunjungan Anda!</div>
            <div>Simpan struk ini sebagai bukti pembayaran.</div>
            <div style="margin-top:2px;">*** {{ strtoupper($identitas->nama_apotek) }} ***</div>
        </div>

    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 500);
        });
    </script>
</body>
</html>
