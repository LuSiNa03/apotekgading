<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'nama_supplier',
        'alamat',
        'no_telp',
        'email',
    ];

    public function obats(): HasMany
    {
        return $this->hasMany(Obat::class, 'supplier_id');
    }

    public function obatMasuks(): HasMany
    {
        return $this->hasMany(ObatMasuk::class, 'supplier_id');
    }
}
