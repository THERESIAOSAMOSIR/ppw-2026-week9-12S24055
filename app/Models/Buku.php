<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Buku extends Model
{
    protected $fillable = [
        'kategori_id',
        'isbn',
        'judul',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'stok',
        'sinopsis',
    ];

    protected function casts(): array
    {
        return [
            'tahun_terbit' => 'integer',
            'stok'         => 'integer',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function peminjamans(): HasMany
    {
        return $this->hasMany(Peminjaman::class);
    }
}
