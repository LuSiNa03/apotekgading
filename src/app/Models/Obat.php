<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Obat extends Model
{
    use HasFactory;

    protected $table = 'obats';

    protected $fillable = [
        'kategori_obat_id',
        'supplier_id',
        'nama_obat',
        'kode_produk',
        'deskripsi',
        'barcode',
        'harga_beli',
        'harga_jual',
        'stok',
        'stok_minimum',
        'tanggal_kedaluwarsa',
        'foto',
    ];

    protected $casts = [
        'tanggal_kedaluwarsa' => 'date',
        'kategori_obat_id' => 'integer',
        'supplier_id' => 'integer',
        'harga_beli' => 'integer',
        'harga_jual' => 'integer',
        'stok' => 'integer',
        'stok_minimum' => 'integer',
    ];

    protected static function booted()
    {
        static::created(function ($obat) {
            if ($obat->stok > 0) {
                $obat->batches()->create([
                    'nomor_batch' => 'BATCH-INITIAL',
                    'tanggal_kedaluwarsa' => $obat->tanggal_kedaluwarsa ?? now()->addYear(),
                    'harga_beli' => $obat->harga_beli,
                    'quantity' => $obat->stok,
                    'remaining_quantity' => $obat->stok,
                ]);
            }
        });
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ObatBatch::class, 'obat_id');
    }

    public function recalculateStockAndExpiry()
    {
        $batches = $this->batches()->where('remaining_quantity', '>', 0)->orderBy('tanggal_kedaluwarsa', 'asc')->get();
        
        if ($batches->isEmpty()) {
            $earliestBatch = $this->batches()->orderBy('tanggal_kedaluwarsa', 'asc')->first();
            $newExpiry = $earliestBatch ? $earliestBatch->tanggal_kedaluwarsa : $this->tanggal_kedaluwarsa;
            $newStock = 0;
        } else {
            $newExpiry = $batches->first()->tanggal_kedaluwarsa;
            $newStock = $batches->sum('remaining_quantity');
        }

        $this->update([
            'stok' => $newStock,
            'tanggal_kedaluwarsa' => $newExpiry,
        ]);
    }

    public function deductStockFEFO(int $jumlah)
    {
        $remainingToDeduct = $jumlah;
        
        $batches = $this->batches()
            ->where('remaining_quantity', '>', 0)
            ->orderBy('tanggal_kedaluwarsa', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($batches as $batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            if ($batch->remaining_quantity >= $remainingToDeduct) {
                $batch->decrement('remaining_quantity', $remainingToDeduct);
                $remainingToDeduct = 0;
            } else {
                $remainingToDeduct -= $batch->remaining_quantity;
                $batch->update(['remaining_quantity' => 0]);
            }
        }

        $this->recalculateStockAndExpiry();

        return $remainingToDeduct == 0;
    }

    public function kategoriObat(): BelongsTo
    {
        return $this->belongsTo(KategoriObat::class, 'kategori_obat_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function obatMasuks(): HasMany
    {
        return $this->hasMany(ObatMasuk::class, 'obat_id');
    }

    public function detailPenjualans(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'obat_id');
    }

    public function penyakits(): BelongsToMany
    {
        return $this->belongsToMany(Penyakit::class, 'obat_penyakit', 'obat_id', 'penyakit_id');
    }
}

