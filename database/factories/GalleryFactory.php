<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gallery>
 */
class GalleryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'image_path' => 'galleries/'.fake()->uuid().'.jpg',
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
            'uploaded_by' => \App\Models\User::factory(),
        ];
    }
}
