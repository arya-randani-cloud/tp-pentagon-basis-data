<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Melengkapi tabel Petugas dan Pengembalian (Tabel ke-4 dan ke-5 dari ERD)
     */
    public function up(): void
    {
        // 1. Tabel Petugas
        Schema::create('petugas', function (Blueprint $table) {
            $table->string('id_petugas', 10)->primary()->comment('Primary Key Petugas');
            $table->string('nama_petugas', 100);
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->string('hak_akses', 20);
            $table->string('no_telepon', 15)->nullable();
            $table->timestamps();
        });

        // 2. Hubungkan peminjaman.id_petugas -> petugas.id_petugas
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->foreign('id_petugas')
                  ->references('id_petugas')
                  ->on('petugas')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
        });

        // 3. Tabel Pengembalian
        Schema::create('pengembalian', function (Blueprint $table) {
            $table->string('id_pengembalian', 15)->primary()->comment('Primary Key Pengembalian');
            $table->string('id_peminjaman', 15)->unique()->comment('FK ke tabel peminjaman (1:1 / 1:0..1)');
            $table->string('id_petugas', 10)->comment('FK ke tabel petugas');
            $table->dateTime('waktu_pengembalian');
            $table->timestamps();

            $table->foreign('id_peminjaman')
                  ->references('id_peminjaman')
                  ->on('peminjaman')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreign('id_petugas')
                  ->references('id_petugas')
                  ->on('petugas')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengembalian');

        if (Schema::hasColumn('peminjaman', 'id_petugas')) {
            Schema::table('peminjaman', function (Blueprint $table) {
                $table->dropForeign(['id_petugas']);
            });
        }

        Schema::dropIfExists('petugas');
    }
};
