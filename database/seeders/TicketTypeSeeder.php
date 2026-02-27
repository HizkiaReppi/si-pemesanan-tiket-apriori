<?php

namespace Database\Seeders;

use App\Enums\DayType;
use App\Models\TicketPrice;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Tiket Dewasa',
                'slug' => 'tiket-dewasa',
                'description' => 'Tiket masuk untuk pengunjung dewasa (usia 12 tahun ke atas).',
                'sort_order' => 1,
                'prices' => [
                    DayType::Weekday->value => 25000,
                    DayType::Weekend->value => 35000,
                ],
            ],
            [
                'name' => 'Tiket Anak',
                'slug' => 'tiket-anak',
                'description' => 'Tiket masuk untuk anak-anak (usia 3-11 tahun). Anak di bawah 3 tahun gratis.',
                'sort_order' => 2,
                'prices' => [
                    DayType::Weekday->value => 15000,
                    DayType::Weekend->value => 25000,
                ],
            ],
        ];

        foreach ($types as $typeData) {
            $prices = $typeData['prices'];
            unset($typeData['prices']);

            $ticketType = TicketType::create($typeData);

            foreach ($prices as $dayType => $price) {
                TicketPrice::create([
                    'ticket_type_id' => $ticketType->id,
                    'day_type' => $dayType,
                    'price' => $price,
                ]);
            }
        }
    }
}
