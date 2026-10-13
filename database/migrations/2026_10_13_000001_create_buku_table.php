<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up()
    {
        Schema::create('buku', function (Blueprint $table) {
            $table->string('id_buku')->nullable()->primary();
            $table->string('judul_buku');
            $table->string('pengarang')->nullable();
            $table->string('penerbit')->nullable();
            $table->smallInteger('tahun_terbit')->nullable();
            $table->string('kategori')->nullable();
            $table->integer('stok')->default(0);

        });
    }

    public function down()
    {
        Schema::dropIfExists('buku');
    }
};
