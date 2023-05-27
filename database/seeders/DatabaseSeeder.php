<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Appointment;
use App\Models\Hairdresser;
use App\Models\HairdresserAvailability;
use App\Models\HairdresserService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminUserSeeder::class);
        // Hairdresser::factory()
        // ->count(5)
        // ->has(HairdresserService::factory()->count(3), 'services')
        // ->has(HairdresserAvailability::factory()->count(5), 'availability')
        // ->has(Appointment::factory()->count(10))
        // ->create();
    }
}
