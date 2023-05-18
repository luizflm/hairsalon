<?php

namespace App\Http\Controllers;

use App\Models\Hairdresser;
use App\Models\HairdresserAvailability;

class AvailabilityController extends Controller
{
    public function index($id) 
    {
        $hairdresser = Hairdresser::find($id);
        if($hairdresser) {
            $hairdresserAvailability = HairdresserAvailability::where('hairdresser_id', $id)->get();

            $weekdays = [
                'Domingo',
                'Segunda-Feira',
                'Terça-Feira',
                'Quarta-Feira',
                'Quinta-Feira',
                'Sexta-Feira',
                'Sábado',
            ];

            foreach($hairdresserAvailability as $availability) {
                $availability['weekday'] = $weekdays[$availability['weekday']];

                $workTimes = explode(', ', $availability['hours']); 

                $availability['start_time'] = $workTimes[0]; 

                $availability['end_time'] = last($workTimes);
            }
            
            return view('admin.availabilities.hairdresser_availability', [
                'hairdresser' => $hairdresser,
                'availabilities' => $hairdresserAvailability,
            ]);
        }
        
        return redirect()->back();
    }
}
