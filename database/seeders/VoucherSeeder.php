<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Voucher::create([
            'code' => 'WELCOME10',
            'name' => 'Diskon Selamat Datang 10%',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_order' => 50000,
            'max_discount' => 25000,
            'usage_limit' => 100,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(3),
        ]);

        Voucher::create([
            'code' => 'HEMAT5K',
            'name' => 'Potongan Rp 5.000',
            'discount_type' => 'fixed',
            'discount_value' => 5000,
            'min_order' => 30000,
            'usage_limit' => 200,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(6),
        ]);
    }
}
