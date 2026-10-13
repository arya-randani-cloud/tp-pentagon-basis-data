<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up()
    {
        Schema::create('anggota', function (Blueprint $table) {
            $table->string('id_anggota')->nullable()->primary();
            $table->string('nama_anggota');
            $table->string('alamat')->nullable();
            $table->string('no_telepon')->nullable();
            $table->string('email')->nullable();
            $table->date('tanggal_daftar');

        });
    }

    public function down()
    {
        Schema::dropIfExists('anggota');
    }
};
