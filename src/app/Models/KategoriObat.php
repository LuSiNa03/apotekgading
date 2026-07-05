<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriObat extends Model
{
    use HasFactory;

    protected $table = 'kategori_obats';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    public function obats(): HasMany
    {
        return $this->hasMany(Obat::class, 'kategori_obat_id');
    }
}
