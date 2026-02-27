<?php

use App\Http\Controllers\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Indonesian URLs)
|--------------------------------------------------------------------------
*/
Route::livewire('/', 'pages::public.home')->name('home');
Route::livewire('tiket', 'pages::public.order-wizard')->name('tiket.index');
Route::livewire('pesanan/{order:code}', 'pages::public.order-detail')->name('pesanan.detail');

// Informational pages
Route::livewire('tentang', 'pages::public.about')->name('tentang');
Route::livewire('syarat-ketentuan', 'pages::public.terms')->name('syarat-ketentuan');
Route::livewire('kebijakan-privasi', 'pages::public.privacy')->name('kebijakan-privasi');
Route::livewire('kontak', 'pages::public.contact')->name('kontak');
Route::livewire('faq', 'pages::public.faq')->name('faq');

// Midtrans webhook (no CSRF)
Route::post('webhook/midtrans', [MidtransWebhookController::class, 'handle'])
    ->name('webhook.midtrans')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();

        return match (true) {
            $user->isAdmin() => redirect()->route('admin.dashboard'),
            $user->role->value === 'cashier' => redirect()->route('kasir.dashboard'),
            $user->role->value === 'owner' => redirect()->route('pemilik.dashboard'),
            $user->role->value === 'gate_staff' => redirect()->route('gerbang.scanner'),
            default => redirect()->route('pesanan-saya'),
        };
    })->name('dashboard');

    // Customer: My Orders
    Route::livewire('pesanan-saya', 'pages::customer.my-orders')->name('pesanan-saya');

    // Customer: E-Ticket
    Route::livewire('e-tiket/{order:code}', 'pages::customer.e-ticket')->name('e-tiket');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/', 'pages::admin.dashboard')->name('dashboard');
    Route::livewire('pesanan', 'pages::admin.orders')->name('pesanan');
    Route::livewire('pesanan/{order}', 'pages::admin.order-detail')->name('pesanan.detail');
    Route::livewire('verifikasi', 'pages::admin.payment-verification')->name('verifikasi');
    Route::livewire('tiket', 'pages::admin.ticket-types')->name('tiket');
    Route::livewire('addon', 'pages::admin.addons')->name('addon');
    Route::livewire('voucher', 'pages::admin.vouchers')->name('voucher');
    Route::livewire('pengguna', 'pages::admin.users')->name('pengguna');
    Route::livewire('apriori', 'pages::admin.apriori')->name('apriori');
    Route::livewire('laporan', 'pages::admin.reports')->name('laporan');
    Route::livewire('audit-log', 'pages::admin.audit-logs')->name('audit-log');
});

/*
|--------------------------------------------------------------------------
| Cashier Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:cashier'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::livewire('/', 'pages::cashier.dashboard')->name('dashboard');
    Route::livewire('penjualan', 'pages::cashier.pos')->name('penjualan');
    Route::livewire('pesanan', 'pages::cashier.orders')->name('pesanan');
});

/*
|--------------------------------------------------------------------------
| Gate Staff Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:gate_staff'])->prefix('gerbang')->name('gerbang.')->group(function () {
    Route::livewire('/', 'pages::gate.scanner')->name('scanner');
    Route::livewire('riwayat', 'pages::gate.check-in-history')->name('riwayat');
});

/*
|--------------------------------------------------------------------------
| Owner Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:owner'])->prefix('pemilik')->name('pemilik.')->group(function () {
    Route::livewire('/', 'pages::owner.dashboard')->name('dashboard');
    Route::livewire('laporan', 'pages::owner.reports')->name('laporan');
    Route::livewire('apriori', 'pages::owner.apriori')->name('apriori');
});

require __DIR__.'/settings.php';
