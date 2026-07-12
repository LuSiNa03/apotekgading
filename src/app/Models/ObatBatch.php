<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObatBatch extends Model
{
    use HasFactory;

    protected $table = 'obat_batches';

    protected $fillable = [
        'obat_id',
        'nomor_batch',
        'tanggal_kedaluwarsa',
        'harga_beli',
        'quantity',
        'remaining_quantity',
    ];

    protected $casts = [
        'tanggal_kedaluwarsa' => 'date',
        'harga_beli' => 'integer',
        'quantity' => 'integer',
        'remaining_quantity' => 'integer',
    ];

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }
}
