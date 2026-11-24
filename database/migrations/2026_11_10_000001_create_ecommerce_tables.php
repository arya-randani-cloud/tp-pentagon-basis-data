<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Memenuhi kriteria Soal 2:
     * a. Kesesuaian kolom dengan atribut pada ER-Diagram
     * b. Kesesuaian tipe data tiap kolom
     * c. Kesesuaian primary key tiap tabel
     * d. Ketepatan penentuan konstrain unique untuk kolom tertentu
     * e. Pemilihan indeks yang sesuai untuk kolom tertentu
     * f. Ketepatan penentuan foreign key pada kolom tertentu
     */
    public function up(): void
    {
        // 1. Tabel Users
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // [2c] Primary Key (BIGINT UNSIGNED AUTO_INCREMENT)
            $table->string('name', 100); // [2a, 2b] Kolom & Tipe data VARCHAR(100)
            $table->string('email', 150)->unique(); // [2d] Konstrain UNIQUE pada email
            $table->string('password', 255);
            $table->string('phone', 20)->nullable()->unique(); // [2d] Konstrain UNIQUE pada no telepon
            $table->text('address')->nullable();
            $table->timestamps();

            $table->index('name', 'idx_users_name'); // [2e] Index pencarian nama user
        });

        // 2. Tabel Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // [2c] Primary Key
            $table->string('name', 100);
            $table->string('slug', 120)->unique(); // [2d] Konstrain UNIQUE pada slug URL
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('name', 'idx_categories_name'); // [2e] Index pada nama kategori
        });

        // 3. Tabel Products
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // [2c] Primary Key
            $table->foreignId('category_id') // [2f] Foreign Key mengacu ke categories(id)
                  ->constrained('categories')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->string('sku', 50)->unique(); // [2d] Konstrain UNIQUE pada kode SKU produk
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2); // [2b] Tipe data DECIMAL(12,2) untuk presisi harga
            $table->integer('stock')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            // [2e] Indeks pada kolom pencarian dan penyaringan produk
            $table->index('name', 'idx_products_name');
            $table->index(['category_id', 'status'], 'idx_products_category_status');
            $table->index('price', 'idx_products_price');
        });

        // 4. Tabel Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // [2c] Primary Key
            $table->foreignId('user_id') // [2f] Foreign Key mengacu ke users(id)
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->string('invoice_number', 50)->unique(); // [2d] Konstrain UNIQUE pada nomor invoice
            $table->decimal('total_price', 12, 2);
            $table->string('status', 30)->default('pending');
            $table->dateTime('order_date');
            $table->timestamps();

            // [2e] Indeks pada status dan tanggal transaksi
            $table->index('status', 'idx_orders_status');
            $table->index('order_date', 'idx_orders_date');
        });

        // 5. Tabel Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); // [2c] Primary Key
            $table->foreignId('order_id') // [2f] Foreign Key mengacu ke orders(id)
                  ->constrained('orders')
                  ->cascadeOnDelete();
            $table->foreignId('product_id') // [2f] Foreign Key mengacu ke products(id)
                  ->constrained('products')
                  ->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            // [2e] Composite Index untuk join order dan product
            $table->index(['order_id', 'product_id'], 'idx_order_items_order_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
    }
};

