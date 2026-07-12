<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Penyakit extends Model
{
    use HasFactory;

    protected $table = 'penyakits';

    protected $fillable = [
        'nama_penyakit',
    ];

    public function obats(): BelongsToMany
    {
        return $this->belongsToMany(Obat::class, 'obat_penyakit', 'penyakit_id', 'obat_id');
    }
}
