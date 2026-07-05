<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentitasApotek extends Model
{
    protected $table = 'identitas_apoteks';

    protected $fillable = [
        'nama_apotek',
        'alamat',
        'no_telp',
        'email',
        'logo',
        'login_image',
    ];

    public static function getSingle(): self
    {
        $setting = self::first();

        if (!$setting) {
            $setting = self::create([
                'nama_apotek' => 'Apotek Gading',
                'alamat' => 'Jl. Gading Raya No. 1, Jakarta Utara',
                'no_telp' => '021-12345678',
                'email' => 'info@apotekgading.com',
                'logo' => null,
            ]);
        }

        return $setting;
    }
}
