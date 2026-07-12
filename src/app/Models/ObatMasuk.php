<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObatMasuk extends Model
{
    use HasFactory;

    protected $table = 'obat_masuks';

    public static bool $skipBatchCreation = false;

    protected static function booted()
    {
        static::created(function ($obatMasuk) {
            if (static::$skipBatchCreation) {
                return;
            }
            $obat = $obatMasuk->obat;
            if ($obat) {
                $obat->batches()->create([
                    'nomor_batch' => $obatMasuk->nomor_batch,
                    'tanggal_kedaluwarsa' => $obatMasuk->tanggal_kedaluwarsa ?? $obat->tanggal_kedaluwarsa ?? now()->addYear(),
                    'harga_beli' => $obatMasuk->harga_beli,
                    'quantity' => $obatMasuk->jumlah,
                    'remaining_quantity' => $obatMasuk->jumlah,
                ]);
                $obat->recalculateStockAndExpiry();
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
        'nomor_batch',
        'tanggal_kedaluwarsa',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'jumlah' => 'integer',
        'harga_beli' => 'integer',
        'tanggal_kedaluwarsa' => 'date',
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
