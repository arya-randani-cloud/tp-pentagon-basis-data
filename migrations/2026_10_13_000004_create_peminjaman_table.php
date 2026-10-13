<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 30)->unique();
            $table->foreignId('anggota_id')->constrained('anggota')->restrictOnDelete();
            $table->foreignId('buku_id')->constrained('buku')->restrictOnDelete();
            $table->foreignId('petugas_id')->constrained('petugas')->restrictOnDelete();
            $table->dateTime('tanggal_pinjam')->index();
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->string('status', 20)->default('dipinjam')->index(); // dipinjam / dikembalikan
            $table->timestamps();

            $table->index(['anggota_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
