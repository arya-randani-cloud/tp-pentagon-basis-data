<?php

use App\Models\Order;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('/pelanggan/order-3-hari-terakhir', function () {
    return response()->json([
        'permintaan' => 'Perlihatkan saya pelanggan yang mengorder 3 hari terakhir',
        'rentang_waktu_mulai' => now()->subDays(3)->toDateTimeString(),
        'rentang_waktu_akhir' => now()->toDateTimeString(),
        'explain_analyze' => Pelanggan::explainAnalyzeOrderTigaHariTerakhir(),
        'data' => Pelanggan::mengorderTigaHariTerakhir(),
    ]);
})->name('pelanggan.order-3-hari-terakhir');

Route::get('/orders/pelanggan/{id_pelanggan}', function (string $id_pelanggan) {
    return response()->json([
        'id_pelanggan' => $id_pelanggan,
        'orders' => Order::dariPelanggan($id_pelanggan),
    ]);
})->name('orders.by-pelanggan');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
