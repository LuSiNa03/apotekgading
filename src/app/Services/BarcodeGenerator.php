<?php

namespace App\Services;

class BarcodeGenerator
{
    /**
     * Generate nomor batch otomatis dari kode_produk + tanggal expired.
     * Format: {KODE_PRODUK}{ddmmyyyy}
     * Jika sudah ada pada produk yang sama, tambahkan suffix -01, -02, dst.
     *
     * @param  \App\Models\Obat  $obat
     * @param  string|\Carbon\Carbon  $tanggalExpired
     * @return string
     */
    public static function generateBatchNumber(\App\Models\Obat $obat, $tanggalExpired): string
    {
        $kode = strtoupper(trim($obat->kode_produk ?? 'BATCH'));
        $tgl  = \Carbon\Carbon::parse($tanggalExpired)->format('dmY');
        $base = $kode . $tgl;

        // Cek apakah nomor batch base sudah ada untuk obat ini
        $exists = \App\Models\ObatMasuk::where('obat_id', $obat->id)
            ->where('nomor_batch', $base)
            ->exists();

        if (! $exists) {
            return $base;
        }

        // Cari suffix tertinggi yang sudah ada
        $existing = \App\Models\ObatMasuk::where('obat_id', $obat->id)
            ->where('nomor_batch', 'like', $base . '-%')
            ->pluck('nomor_batch')
            ->map(function ($nb) use ($base) {
                // Ambil angka suffix setelah tanda "-"
                if (preg_match('/-(\d+)$/', $nb, $m)) {
                    return (int) $m[1];
                }
                return 0;
            })
            ->max();

        $next = str_pad(($existing ?? 0) + 1, 2, '0', STR_PAD_LEFT);

        return $base . '-' . $next;
    }

    /**
     * Generate Code 128 barcode SVG.
     */
    public static function generateSVG(string $code): string
    {
        $code = trim($code);
        if ($code === '') {
            return '';
        }

        // Code 128 Alphabet Table
        $charTable = [
            ' ' => [2, 1, 2, 2, 2, 2], '!' => [2, 2, 2, 1, 2, 2], '"' => [2, 2, 2, 2, 2, 1],
            '#' => [1, 2, 1, 2, 2, 3], '$' => [1, 2, 1, 3, 2, 2], '%' => [1, 3, 1, 2, 2, 2],
            '&' => [1, 2, 2, 2, 1, 3], '\'' => [1, 2, 2, 3, 1, 2], '(' => [1, 3, 2, 2, 1, 2],
            ')' => [2, 2, 1, 2, 1, 3], '*' => [2, 2, 1, 3, 1, 2], '+' => [2, 3, 1, 2, 1, 2],
            ',' => [1, 1, 2, 2, 3, 2], '-' => [1, 2, 2, 1, 3, 2], '.' => [1, 2, 2, 2, 3, 1],
            '/' => [1, 1, 3, 2, 2, 2], '0' => [1, 2, 3, 1, 2, 2], '1' => [1, 2, 3, 2, 2, 1],
            '2' => [2, 2, 3, 2, 1, 1], '3' => [2, 2, 1, 1, 3, 2], '4' => [2, 2, 1, 2, 3, 1],
            '5' => [2, 1, 3, 2, 1, 2], '6' => [2, 2, 3, 1, 1, 2], '7' => [3, 1, 2, 1, 3, 1],
            '8' => [3, 1, 1, 2, 2, 2], '9' => [3, 2, 1, 1, 2, 2], ':' => [3, 2, 1, 2, 2, 1],
            ';' => [3, 1, 2, 2, 1, 2], '<' => [3, 2, 2, 1, 1, 2], '=' => [3, 2, 2, 2, 1, 1],
            '>' => [2, 1, 2, 1, 2, 3], '?' => [2, 1, 2, 3, 2, 1], '@' => [2, 3, 2, 1, 2, 1],
            'A' => [1, 1, 1, 3, 2, 3], 'B' => [1, 3, 1, 1, 2, 3], 'C' => [1, 3, 1, 3, 2, 1],
            'D' => [1, 1, 2, 3, 1, 3], 'E' => [1, 3, 2, 1, 1, 3], 'F' => [1, 3, 2, 3, 1, 1],
            'G' => [2, 1, 1, 3, 1, 3], 'H' => [2, 3, 1, 1, 1, 3], 'I' => [2, 3, 1, 3, 1, 1],
            'J' => [1, 1, 2, 1, 3, 3], 'K' => [1, 1, 2, 3, 3, 1], 'L' => [1, 3, 2, 1, 3, 1],
            'M' => [1, 1, 3, 1, 2, 3], 'N' => [1, 1, 3, 3, 2, 1], 'O' => [1, 3, 3, 1, 2, 1],
            'P' => [3, 1, 3, 1, 2, 1], 'Q' => [2, 1, 1, 3, 3, 1], 'R' => [2, 3, 1, 1, 3, 1],
            'S' => [2, 1, 3, 1, 1, 3], 'T' => [2, 1, 3, 3, 1, 1], 'U' => [2, 1, 3, 1, 3, 1],
            'V' => [3, 1, 1, 1, 2, 3], 'W' => [3, 1, 1, 3, 2, 1], 'X' => [3, 3, 1, 1, 2, 1],
            'Y' => [3, 1, 2, 1, 1, 3], 'Z' => [3, 1, 2, 3, 1, 1], '[' => [3, 3, 2, 1, 1, 1],
            '\\' => [3, 1, 4, 1, 1, 1], ']' => [2, 2, 1, 4, 1, 1], '^' => [4, 3, 1, 1, 1, 1],
            '_' => [1, 1, 1, 2, 2, 4], '`' => [1, 1, 1, 4, 2, 2], 'a' => [1, 2, 1, 1, 2, 4],
            'b' => [1, 2, 1, 4, 2, 1], 'c' => [1, 4, 1, 1, 2, 2], 'd' => [1, 4, 1, 2, 2, 1],
            'e' => [1, 1, 2, 2, 1, 4], 'f' => [1, 1, 2, 4, 1, 2], 'g' => [1, 2, 2, 1, 1, 4],
            'h' => [1, 2, 2, 4, 1, 1], 'i' => [1, 4, 2, 1, 1, 2], 'j' => [1, 4, 2, 2, 1, 1],
            'k' => [2, 4, 1, 2, 1, 1], 'l' => [2, 2, 1, 1, 1, 4], 'm' => [4, 1, 3, 1, 1, 1],
            'n' => [2, 4, 1, 1, 1, 2], 'o' => [1, 3, 4, 1, 1, 1], 'p' => [1, 1, 1, 2, 4, 2],
            'q' => [1, 2, 1, 1, 4, 2], 'r' => [1, 2, 1, 2, 4, 1], 's' => [1, 1, 4, 2, 1, 2],
            't' => [1, 2, 4, 1, 1, 2], 'u' => [1, 2, 4, 2, 1, 1], 'v' => [4, 1, 1, 2, 1, 2],
            'w' => [4, 2, 1, 1, 1, 2], 'x' => [4, 2, 1, 2, 1, 1], 'y' => [2, 1, 2, 1, 4, 1],
            'z' => [2, 1, 4, 1, 2, 1], '{' => [2, 4, 1, 2, 1, 1], '|' => [2, 1, 1, 1, 4, 2],
            '}' => [2, 1, 1, 2, 4, 1], '~' => [2, 1, 1, 2, 3, 2], 'DEL'=> [2, 3, 3, 1, 1, 1],
            'FNC3'=> [2, 1, 1, 4, 1, 2], 'FNC2'=> [2, 1, 1, 2, 1, 4], 'SHIFT'=> [2, 1, 1, 2, 3, 2],
            'CODEC'=> [2, 3, 3, 1, 1, 1], 'CODEB'=> [2, 1, 1, 4, 1, 2], 'FNC1'=> [2, 1, 1, 2, 1, 4],
            'STARTA'=> [2, 1, 1, 2, 3, 2], 'STARTB'=> [2, 1, 1, 2, 2, 3], 'STARTC'=> [2, 1, 1, 4, 3, 1],
            'STOP'=> [2, 3, 3, 1, 1, 1, 2]
        ];

        // For simplicity, we use Code 128 Set B
        // We build the characters list
        $widths = [];
        
        // Start with STARTB (value 104)
        $startBPattern = $charTable['STARTB'];
        foreach ($startBPattern as $w) {
            $widths[] = $w;
        }

        $checksum = 104;
        
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            
            // Map character to pattern
            if (isset($charTable[$char])) {
                $pattern = $charTable[$char];
                // Calculate char value (ASCII value - 32)
                $val = ord($char) - 32;
                $checksum += $val * ($i + 1);
            } else {
                // Fallback for unmapped characters (use space)
                $pattern = $charTable[' '];
                $checksum += 0 * ($i + 1);
            }

            foreach ($pattern as $w) {
                $widths[] = $w;
            }
        }

        // Calculate Checksum Character
        $checkVal = $checksum % 103;
        
        // Find checksum character pattern
        // Values mapping to character table keys in order
        $keys = array_keys($charTable);
        $checkKey = $keys[$checkVal] ?? ' ';
        $checkPattern = $charTable[$checkKey];
        foreach ($checkPattern as $w) {
            $widths[] = $w;
        }

        // Add STOP pattern
        $stopPattern = $charTable['STOP'];
        foreach ($stopPattern as $w) {
            $widths[] = $w;
        }

        // Generate SVG string
        $barWidth = 2;
        $barHeight = 50;
        $totalWidth = array_sum($widths) * $barWidth;
        
        $svg = '<svg width="' . $totalWidth . '" height="' . ($barHeight + 20) . '" viewBox="0 0 ' . $totalWidth . ' ' . ($barHeight + 20) . '" xmlns="http://www.w3.org/2000/svg" style="background:#fff;padding:5px;border-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">';
        
        $x = 0;
        $drawBar = true;
        foreach ($widths as $w) {
            $wVal = $w * $barWidth;
            if ($drawBar) {
                $svg .= '<rect x="' . $x . '" y="5" width="' . $wVal . '" height="' . $barHeight . '" fill="#000" />';
            }
            $x += $wVal;
            $drawBar = !$drawBar;
        }
        
        // Add code text below the barcode
        $svg .= '<text x="' . ($totalWidth / 2) . '" y="' . ($barHeight + 15) . '" font-family="monospace" font-size="12" fill="#000" text-anchor="middle">' . htmlspecialchars($code) . '</text>';
        $svg .= '</svg>';

        return $svg;
    }
}
