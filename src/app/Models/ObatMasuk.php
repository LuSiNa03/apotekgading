<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObatMasuk extends Model
{
    use HasFactory;

    protected $table = 'obat_masuks';

    protected static function booted()
    {
        static::created(function ($obatMasuk) {
            $obat = $obatMasuk->obat;
            if ($obat) {
                $obat->increment('stok', $obatMasuk->jumlah);
            }
        });
    }

    protected $fillable = [
        'nomor_transaksi',
        'obat_id',
        'supplier_id',
        'jumlah',
        'harga_beli',
        'tanggal_masuk',
        'faktur',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'jumlah' => 'integer',
        'harga_beli' => 'integer',
    ];

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
