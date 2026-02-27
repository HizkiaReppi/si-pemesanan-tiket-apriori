<?php

namespace Database\Factories;

use App\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TicketType>
 */
class TicketTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Tiket Dewasa', 'Tiket Anak', 'Tiket Lansia', 'Tiket Rombongan']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
