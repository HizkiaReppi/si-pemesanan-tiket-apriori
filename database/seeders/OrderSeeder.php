<?php

namespace Database\Seeders;

use App\Enums\DayType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Addon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Seed sample orders for development and Apriori demo data.
     */
    public function run(): void
    {
        $customer = User::where('email', 'pelanggan@example.com')->first();
        $ticketDewasa = TicketType::where('slug', 'tiket-dewasa')->first();
        $ticketAnak = TicketType::where('slug', 'tiket-anak')->first();
        $addons = Addon::all()->keyBy('slug');

        if (! $customer || ! $ticketDewasa || ! $ticketAnak) {
            return;
        }

        // Generate 30 sample completed orders for Apriori analysis
        $orderPatterns = [
            // Pattern 1: Dewasa + Gazebo (most common combo)
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2]], 'addons' => ['sewa-gazebo']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2]], 'addons' => ['sewa-gazebo']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2]], 'addons' => ['sewa-gazebo']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 3]], 'addons' => ['sewa-gazebo', 'parkir-mobil']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2]], 'addons' => ['sewa-gazebo', 'voucher-makanan']],
            // Pattern 2: Dewasa + Anak (family)
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2], ['type' => $ticketAnak, 'qty' => 1]], 'addons' => ['sewa-gazebo']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2], ['type' => $ticketAnak, 'qty' => 2]], 'addons' => ['sewa-gazebo', 'sewa-pelampung']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2], ['type' => $ticketAnak, 'qty' => 1]], 'addons' => ['sewa-gazebo', 'parkir-mobil']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2], ['type' => $ticketAnak, 'qty' => 2]], 'addons' => ['sewa-gazebo', 'sewa-pelampung', 'voucher-makanan']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2], ['type' => $ticketAnak, 'qty' => 1]], 'addons' => ['sewa-pelampung']],
            // Pattern 3: Solo visitors
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 1]], 'addons' => ['sewa-loker']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 1]], 'addons' => ['sewa-loker', 'sewa-handuk']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 1]], 'addons' => ['sewa-handuk']],
            // Pattern 4: Anak + Pelampung
            ['tickets' => [['type' => $ticketAnak, 'qty' => 2]], 'addons' => ['sewa-pelampung']],
            ['tickets' => [['type' => $ticketAnak, 'qty' => 3]], 'addons' => ['sewa-pelampung', 'sewa-gazebo']],
            // Pattern 5: Large groups
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 4], ['type' => $ticketAnak, 'qty' => 2]], 'addons' => ['sewa-gazebo', 'parkir-mobil', 'voucher-makanan']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 3], ['type' => $ticketAnak, 'qty' => 3]], 'addons' => ['sewa-gazebo', 'sewa-pelampung', 'parkir-mobil']],
            // More varied patterns
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2]], 'addons' => ['parkir-mobil']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2]], 'addons' => ['sewa-gazebo', 'sewa-loker']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 1], ['type' => $ticketAnak, 'qty' => 1]], 'addons' => ['sewa-pelampung', 'sewa-handuk']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2]], 'addons' => ['voucher-makanan']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2], ['type' => $ticketAnak, 'qty' => 1]], 'addons' => ['sewa-gazebo', 'sewa-handuk']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 1]], 'addons' => []],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2]], 'addons' => ['sewa-gazebo', 'parkir-mobil', 'voucher-makanan']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2], ['type' => $ticketAnak, 'qty' => 1]], 'addons' => ['sewa-gazebo']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2]], 'addons' => ['sewa-gazebo']],
            ['tickets' => [['type' => $ticketAnak, 'qty' => 2]], 'addons' => ['sewa-pelampung', 'sewa-handuk']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 1]], 'addons' => ['sewa-loker', 'parkir-mobil']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2], ['type' => $ticketAnak, 'qty' => 2]], 'addons' => ['sewa-gazebo', 'sewa-pelampung']],
            ['tickets' => [['type' => $ticketDewasa, 'qty' => 2]], 'addons' => ['sewa-gazebo', 'sewa-handuk', 'parkir-mobil']],
        ];

        $customers = User::factory()->count(10)->create();

        foreach ($orderPatterns as $index => $pattern) {
            $dayType = $index % 3 === 0 ? DayType::Weekend : DayType::Weekday;
            $visitDate = now()->subDays(rand(1, 60));
            $selectedCustomer = $customers->random();

            $subtotal = 0;
            $itemsData = [];

            // Calculate ticket items
            foreach ($pattern['tickets'] as $ticket) {
                $price = $ticket['type']->prices()
                    ->where('day_type', $dayType)
                    ->first()?->price ?? 25000;

                $itemSubtotal = $price * $ticket['qty'];
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'itemable_type' => TicketType::class,
                    'itemable_id' => $ticket['type']->id,
                    'item_name' => $ticket['type']->name,
                    'item_price' => $price,
                    'quantity' => $ticket['qty'],
                    'subtotal' => $itemSubtotal,
                ];
            }

            // Calculate addon items
            foreach ($pattern['addons'] as $addonSlug) {
                $addon = $addons->get($addonSlug);
                if (! $addon) {
                    continue;
                }

                $subtotal += (float) $addon->price;

                $itemsData[] = [
                    'itemable_type' => Addon::class,
                    'itemable_id' => $addon->id,
                    'item_name' => $addon->name,
                    'item_price' => $addon->price,
                    'quantity' => 1,
                    'subtotal' => $addon->price,
                ];
            }

            $order = Order::create([
                'user_id' => $selectedCustomer->id,
                'visit_date' => $visitDate,
                'day_type' => $dayType,
                'customer_name' => $selectedCustomer->name,
                'customer_email' => $selectedCustomer->email,
                'customer_phone' => $selectedCustomer->phone ?? '08123456789',
                'subtotal' => $subtotal,
                'discount' => 0,
                'total' => $subtotal,
                'status' => OrderStatus::Completed,
                'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            ]);

            foreach ($itemsData as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
            }

            Payment::create([
                'order_id' => $order->id,
                'method' => $order->payment_method,
                'status' => PaymentStatus::Paid,
                'amount' => $order->total,
                'paid_at' => $order->created_at,
            ]);
        }
    }
}
