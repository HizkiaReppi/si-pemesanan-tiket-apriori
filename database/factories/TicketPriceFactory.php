<?php

namespace Database\Factories;

use App\Enums\DayType;
use App\Models\TicketPrice;
use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketPrice>
 */
class TicketPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_type_id' => TicketType::factory(),
            'day_type' => fake()->randomElement(DayType::cases()),
            'price' => fake()->randomElement([25000, 30000, 35000, 40000, 50000]),
        ];
    }

    public function weekday(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_type' => DayType::Weekday,
        ]);
    }

    public function weekend(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_type' => DayType::Weekend,
        ]);
    }
}
