<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Barcode - {{ $obat->nama_obat }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background: #fff;
        }
        .barcode-container {
            text-align: center;
            padding: 20px;
            border: 1px dashed #ccc;
        }
        .btn-print {
            margin-top: 20px;
            padding: 10px 20px;
            background: #10b981;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        @media print {
            .btn-print {
                display: none;
            }
            body {
                height: auto;
            }
            .barcode-container {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="barcode-container">
        <h3>{{ $obat->nama_obat }}</h3>
        {!! $svg !!}
        <div>
            <button class="btn-print" onclick="window.print()">Cetak</button>
        </div>
    </div>
</body>
</html>
