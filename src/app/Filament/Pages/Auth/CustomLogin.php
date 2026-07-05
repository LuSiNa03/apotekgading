<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class CustomLogin extends BaseLogin
{
    /**
     * Custom heading yang menampilkan gambar login dinamis dari pengaturan identitas apotek,
     * nama apotek, tagline, dan badge role berwarna sesuai panel.
     */
    public function getHeading(): Htmlable
    {
        $identitas  = \App\Models\IdentitasApotek::getSingle();
        $namaApotek = $identitas?->nama_apotek ?? 'Apotek Gading';

        // Prioritas gambar: 1) upload dari admin, 2) foto gedung default
        if ($identitas && $identitas->login_image) {
            $imgUrl = asset('storage/' . $identitas->login_image);
        } else {
            $imgUrl = url('/images/apotek-storefront.jpg');
        }

        // Deteksi role dari path URL
        $path = request()->path();

        if (str_contains($path, 'admin')) {
            $roleName  = 'Super Admin';
            $roleColor = '#2563EB';
            $tagline   = 'Panel administrasi sistem apotek';
        } elseif (str_contains($path, 'pemilik')) {
            $roleName  = 'Pemilik Apotek';
            $roleColor = '#15803D';
            $tagline   = 'Pantau laporan & performa apotek';
        } else {
            $roleName  = 'Petugas Apotek';
            $roleColor = '#16A34A';
            $tagline   = 'Kasir & transaksi penjualan obat';
        }

        $html = '
        <div style="display:flex;flex-direction:column;align-items:center;gap:16px;text-align:center;padding-bottom:4px">

            <!-- Gambar login dinamis dari pengaturan identitas apotek -->
            <div style="width:100%;border-radius:16px;overflow:hidden;
                        box-shadow:0 6px 28px rgba(0,0,0,.14);
                        border:1px solid rgba(0,0,0,.07);
                        aspect-ratio:16/9;background:#f3f4f6;">
                <img src="' . $imgUrl . '"
                     alt="' . htmlspecialchars($namaApotek) . '"
                     style="width:100%;height:100%;object-fit:cover;display:block">
            </div>

            <!-- Nama apotek, tagline & badge role -->
            <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                <h1 style="margin:0;font-size:1.3rem;font-weight:800;
                            color:#111827;letter-spacing:-0.02em;line-height:1.25">
                    ' . htmlspecialchars($namaApotek) . '
                </h1>
                <p style="margin:0;font-size:.78rem;color:#6b7280;line-height:1.4">
                    ' . htmlspecialchars($tagline) . '
                </p>
                <span style="display:inline-block;margin-top:6px;
                             padding:4px 18px;border-radius:999px;
                             font-size:.7rem;font-weight:700;letter-spacing:.08em;
                             text-transform:uppercase;color:#fff;
                             background:' . $roleColor . ';
                             box-shadow:0 2px 10px ' . $roleColor . '66">
                    ' . htmlspecialchars($roleName) . '
                </span>
            </div>

        </div>';

        return new HtmlString($html);
    }
}
