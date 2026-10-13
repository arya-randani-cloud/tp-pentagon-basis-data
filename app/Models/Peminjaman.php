<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    use HasFactory;

    // @dbml-sync:table-property
    protected $table = 'peminjaman';
    // @enddbml-sync:table-property

    // @dbml-sync:fillable
    protected $fillable = [
        'id_anggota',
		'id_buku',
		'id_petugas',
		'waktu_peminjaman'
    ];
    // @enddbml-sync:fillable

    // @dbml-sync:casts
    protected $casts = [
        'waktu_peminjaman' => 'datetime'
    ];
    // @enddbml-sync:casts

    // @dbml-sync:relations
    public function anggotum()
    {
        return $this->belongsTo(Anggotum::class, 'id_anggota', 'id_anggota');
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class, 'id_buku', 'id_buku');
    }

    // @enddbml-sync:relations
}