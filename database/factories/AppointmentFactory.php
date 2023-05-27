<?php

namespace Database\Factories;

use App\Models\Hairdresser;
use App\Models\HairdresserService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $user = User::inRandomOrder()->first();
        $hairdresser = Hairdresser::inRandomOrder()->first();
        $hairdresserService = HairdresserService::inRandomOrder()->first();
        while($hairdresserService->hairdresser_id != $hairdresser->id) {
            $hairdresserService = HairdresserService::inRandomOrder()->first();
        }

        return [
            'ap_datetime' => now(),
            'was_done' => 0,
            'user_id' => $user->id,
            'hairdresser_id' => $hairdresser->id,
            'hairdresser_service_id' => $hairdresserService->id,
        ];
    }
}
 