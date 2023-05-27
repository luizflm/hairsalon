<?php

namespace Database\Seeders;

use App\Models\Hairdresser;
use App\Models\HairdresserAvailability;
use App\Models\HairdresserService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HairdresserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Hairdresser::factory(1)  
        ->has(HairdresserService::factory()->count(3), 'services')
        ->has(HairdresserAvailability::factory()->count(5), 'availability')
        ->create();
    }
}
