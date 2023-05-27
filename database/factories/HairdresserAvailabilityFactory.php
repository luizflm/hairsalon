<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HairdresserAvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'weekday' => fake()->unique()->numberBetween(0, 6),
            'hours' => '08:00, 09:00, 10:00, 11:00, 12:00, 13:00, 14:00, 15:00, 16:00, 17:00',
        ];
    }
}
