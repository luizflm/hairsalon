<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HairdresserService>
 */
class HairdresserServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $price = rand(14, 99) + 0.99;

        return [
            'name' => fake()->text(20),
            'price' => $price,
        ];
    }
}
