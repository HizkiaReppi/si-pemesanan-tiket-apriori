<?php

namespace Database\Factories;

use App\Models\Addon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Addon>
 */
class AddonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Sewa Gazebo', 'Sewa Loker', 'Sewa Pelampung',
            'Sewa Handuk', 'Parkir Mobil', 'Voucher Makanan',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price' => fake()->randomElement([10000, 15000, 25000, 50000]),
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
