<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->string('id_order')->primary();
            $table->string('id_pelanggan');
            $table->foreign('id_pelanggan')
                ->references('id_pelanggan')
                ->on('pelanggan')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('id_buku')->nullable();
            $table->string('nama_produk');
            $table->integer('jumlah')->default(1);
            $table->decimal('total_harga', 12, 2)->default(0);
            $table->timestamp('tanggal_order')->index();
            $table->string('status_order', 30)->default('selesai');

            $table->index(['id_pelanggan', 'tanggal_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
