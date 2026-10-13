<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku', function (Blueprint $table) {
            $table->id();
            $table->string('kode_buku', 20)->unique();
            $table->string('judul', 200)->index();
            $table->string('penulis', 100)->nullable();
            $table->string('penerbit', 100)->nullable();
            $table->unsignedSmallInteger('tahun_terbit')->nullable();
            $table->unsignedInteger('stok')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku');
    }
};
