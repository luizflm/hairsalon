<?php

namespace App\Http\Controllers;

use App\Models\Hairdresser;
use App\Models\HairdresserAvailability;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function getHairdresserAvailability($id) {
        $hairdresser = Hairdresser::find($id); // pegando o hairdresser
        if($hairdresser) { // verificando se existe/foi encontrado
            // pegando as disponibilidades do hairdresser
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

            

            // pra cada dia disponivel
            foreach($hairdresserAvailability as $availability) {
                // transformar a key do dia no nome real, ex: 1 = Segunda-Feira, etc.
                $availability['weekday'] = $weekdays[$availability['weekday']];

                // transformando a string de hours em um array com cada horário sendo um indice
                $workTimes = explode(', ', $availability['hours']); 

                // pegando o horário inicial (primeiro item do array)
                $availability['start_time'] = $workTimes[0]; 

                // pegando o horário final (último item do array)
                $availability['end_time'] = last($workTimes);
            }
    
            return view('hairdresser_availability', [
                'hairdresser' => $hairdresser,
                'availabilities' => $hairdresserAvailability,
            ]);
        }

        return redirect()->back();
    }
}
