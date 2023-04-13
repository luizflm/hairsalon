<?php

namespace Database\Seeders;

use App\Models\HairdresserService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HairdresserServiceSeeder extends Seeder
{
    public function run()
    {
        HairdresserService::create([
            'name' => 'Unhas',
            'price' => 14.99,
            'hairdresser_id' => 1,
        ]);
    }
}
