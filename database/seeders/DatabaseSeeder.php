<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default users for each role
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@kolampoopo.test',
            'role' => UserRole::Admin,
            'phone' => '081234567890',
        ]);

        User::factory()->create([
            'name' => 'Pemilik Kolam',
            'email' => 'owner@kolampoopo.test',
            'role' => UserRole::Owner,
            'phone' => '081234567891',
        ]);

        User::factory()->create([
            'name' => 'Kasir',
            'email' => 'kasir@kolampoopo.test',
            'role' => UserRole::Cashier,
            'phone' => '081234567892',
        ]);

        User::factory()->create([
            'name' => 'Petugas Gerbang',
            'email' => 'gerbang@kolampoopo.test',
            'role' => UserRole::GateStaff,
            'phone' => '081234567893',
        ]);

        User::factory()->create([
            'name' => 'Pelanggan Demo',
            'email' => 'pelanggan@example.com',
            'role' => UserRole::Customer,
            'phone' => '081234567894',
        ]);

        $this->call([
            TicketTypeSeeder::class,
            AddonSeeder::class,
            VoucherSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
