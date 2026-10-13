<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('anggota', function (Blueprint $table) {
            $table->string('id_anggota', 10)->primary()->comment('Primary Key Anggota');
            $table->string('nama_anggota', 100);
            $table->string('alamat', 255)->nullable();
            $table->string('no_telepon', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->date('tanggal_daftar');
            $table->timestamps();
        });

        Schema::create('buku', function (Blueprint $table) {
            $table->string('id_buku', 10)->primary()->comment('Primary Key Buku');
            $table->string('judul_buku', 150);
            $table->string('pengarang', 100)->nullable();
            $table->string('penerbit', 100)->nullable();
            $table->smallInteger('tahun_terbit')->nullable();
            $table->string('kategori', 50)->nullable();
            $table->integer('stok')->default(0);
            $table->timestamps();
        });

        Schema::create('peminjaman', function (Blueprint $table) {
            $table->string('id_peminjaman', 15)->primary()->comment('Primary Key Peminjaman');
            $table->string('id_anggota', 10)->comment('FK ke tabel anggota');
            $table->string('id_buku', 10)->comment('FK ke tabel buku');
            $table->string('id_petugas', 10)->nullable()->comment('ID Petugas yang mencatat peminjaman');
            $table->dateTime('waktu_peminjaman');
            $table->timestamps();

            $table->foreign('id_anggota')
                  ->references('id_anggota')
                  ->on('anggota')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->foreign('id_buku')
                  ->references('id_buku')
                  ->on('buku')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
        Schema::dropIfExists('buku');
        Schema::dropIfExists('anggota');
    }
};
