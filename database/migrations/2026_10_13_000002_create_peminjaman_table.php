<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up()
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->string('id_peminjaman')->nullable()->primary();
            $table->string('id_anggota');
            $table->foreign('id_anggota')->references('id_anggota')->on('anggota')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('id_buku');
            $table->foreign('id_buku')->references('id_buku')->on('buku')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('id_petugas')->nullable();
            $table->timestamp('waktu_peminjaman');

        });
    }

    public function down()
    {
        Schema::dropIfExists('peminjaman');
    }
};
