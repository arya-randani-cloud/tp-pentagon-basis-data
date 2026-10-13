<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggotum extends Model
{
    use HasFactory;

    // @dbml-sync:table-property
    
    // @enddbml-sync:table-property

    // @dbml-sync:fillable
    protected $fillable = [
        'nama_anggota',
		'alamat',
		'no_telepon',
		'email',
		'tanggal_daftar'
    ];
    // @enddbml-sync:fillable

    // @dbml-sync:casts
    protected $casts = [
        'tanggal_daftar' => 'date'
    ];
    // @enddbml-sync:casts

    // @dbml-sync:relations
    public function peminjamen()
    {
        return $this->hasMany(Peminjaman::class, 'id_anggota', 'id_anggota');
    }

    // @enddbml-sync:relations
}