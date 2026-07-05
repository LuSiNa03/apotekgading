<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Obat extends Model
{
    use HasFactory;

    protected $table = 'obats';

    protected $fillable = [
        'kategori_obat_id',
        'supplier_id',
        'nama_obat',
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
}
