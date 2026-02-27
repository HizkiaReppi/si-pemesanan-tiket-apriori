<?php

namespace Database\Seeders;

use App\Models\Addon;
use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addons = [
            [
                'name' => 'Sewa Gazebo',
                'slug' => 'sewa-gazebo',
                'description' => 'Gazebo pribadi untuk bersantai bersama keluarga atau rombongan.',
                'price' => 50000,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sewa Loker',
                'slug' => 'sewa-loker',
                'description' => 'Loker penyimpanan barang berharga selama berkunjung.',
                'price' => 10000,
                'sort_order' => 2,
            ],
            [
                'name' => 'Sewa Pelampung',
                'slug' => 'sewa-pelampung',
                'description' => 'Pelampung renang untuk keamanan dan kenyamanan.',
                'price' => 15000,
                'sort_order' => 3,
            ],
            [
                'name' => 'Sewa Handuk',
                'slug' => 'sewa-handuk',
                'description' => 'Handuk bersih untuk digunakan selama berkunjung.',
                'price' => 10000,
                'sort_order' => 4,
            ],
            [
                'name' => 'Parkir Mobil',
                'slug' => 'parkir-mobil',
                'description' => 'Area parkir yang aman untuk kendaraan roda empat.',
                'price' => 5000,
                'sort_order' => 5,
            ],
            [
                'name' => 'Voucher Makanan',
                'slug' => 'voucher-makanan',
                'description' => 'Voucher senilai Rp 25.000 untuk kantin Kolam Po\'opo.',
                'price' => 25000,
                'sort_order' => 6,
            ],
        ];

        foreach ($addons as $addon) {
            Addon::create($addon);
        }
    }
}
