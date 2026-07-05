<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan {{ ucfirst(str_replace('_', ' ', $jenis)) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }

        .header-container { display: table; width: 100%; border-bottom: 2px solid #10b981; padding-bottom: 10px; margin-bottom: 16px; }
        .logo-cell { display: table-cell; width: 60px; vertical-align: middle; }
        .info-cell { display: table-cell; vertical-align: middle; padding-left: 10px; }

        .header h1 { font-size: 16px; color: #10b981; font-weight: bold; }
        .header p { font-size: 9px; color: #555; margin-top: 2px; }
        .header .sub-desc { font-size: 8px; color: #777; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th {
            background: #10b981;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        tbody tr:nth-child(even) { background: #f9fbf9; }
        tbody td { padding: 5px 8px; border-bottom: 1px solid #e0f2fe; font-size: 9px; }

        .badge { padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: bold; }
        .badge-danger  { background: #fee2e2; color: #b91c1c; }
        .badge-success { background: #dcfce7; color: #166534; }

        .footer { margin-top: 20px; font-size: 8px; color: #888; border-top: 1px solid #ddd; padding-top: 8px; }
        .summary { margin-top: 10px; font-size: 10px; background: #f0fdf4; padding: 6px 10px; border-radius: 4px; }
    </style>
</head>
<body>
    @php
        $identitas = \App\Models\IdentitasApotek::getSingle();
        $logoPath = $identitas->logo ? public_path('storage/' . $identitas->logo) : null;
    @endphp

    <div class="header-container">
        @if ($logoPath && file_exists($logoPath))
            <div class="logo-cell">
                <img src="{{ $logoPath }}" alt="Logo" style="max-height: 50px; max-width: 50px;">
            </div>
        @endif
        <div class="info-cell">
            <div class="header">
                <h1>{{ strtoupper($identitas->nama_apotek) }}</h1>
                <p>{{ $identitas->alamat }} | Telp: {{ $identitas->no_telp }} @if($identitas->email) | Email: {{ $identitas->email }} @endif</p>
                <p class="sub-desc">
                    Laporan {{ ucwords(str_replace('_', ' ', $jenis)) }} 
                    @if ($jenis !== 'stok')
                        (Periode: {{ \Illuminate\Support\Carbon::parse($dari)->format('d M Y') }} s/d {{ \Illuminate\Support\Carbon::parse($sampai)->format('d M Y') }})
                    @endif
                    &nbsp;|&nbsp; Dicetak pada: {{ now()->format('d M Y H:i') }}
                </p>
            </div>
        </div>
    </div>

    @if (count($data) > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    @foreach (array_keys($data[0]) as $header)
                        <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        @foreach ($row as $key => $value)
                            <td>
                                @if (in_array($key, ['total', 'harga_jual', 'total_pendapatan', 'total_nominal']))
                                    Rp {{ number_format((int)$value, 0, ',', '.') }}
                                @elseif ($key === 'status_stok')
                                    <span class="badge {{ $value === 'KRITIS' ? 'badge-danger' : 'badge-success' }}">
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

        <div class="summary">
            Total record: <strong>{{ count($data) }}</strong>
            @if ($jenis === 'penjualan')
                &nbsp;|&nbsp; Total Pendapatan:
                <strong>Rp {{ number_format(collect($data)->sum('total'), 0, ',', '.') }}</strong>
            @elseif ($jenis === 'pendapatan')
                &nbsp;|&nbsp; Total Pendapatan:
                <strong>Rp {{ number_format(collect($data)->sum('total_pendapatan'), 0, ',', '.') }}</strong>
            @elseif ($jenis === 'metode_pembayaran')
                &nbsp;|&nbsp; Total Nominal:
                <strong>Rp {{ number_format(collect($data)->sum('total_nominal'), 0, ',', '.') }}</strong>
            @endif
        </div>
    @else
        <p style="margin-top:20px; color:#888;">Tidak ada data untuk periode ini.</p>
    @endif

    <div class="footer">
        Laporan ini digenerate secara otomatis oleh Sistem Manajemen {{ $identitas->nama_apotek }}
    </div>
</body>
</html>
