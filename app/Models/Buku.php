<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    use HasFactory;

    // @dbml-sync:table-property
    protected $table = 'buku';
    // @enddbml-sync:table-property

    // @dbml-sync:fillable
    protected $fillable = [
        'judul_buku',
		'pengarang',
		'penerbit',
		'tahun_terbit',
		'kategori',
		'stok'
    ];
    // @enddbml-sync:fillable

    // @dbml-sync:casts
    protected $casts = [
        
    ];
    // @enddbml-sync:casts

    // @dbml-sync:relations
    public function peminjamen()
    {
        return $this->hasMany(Peminjaman::class, 'id_buku', 'id_buku');
    }

    // @enddbml-sync:relations
}