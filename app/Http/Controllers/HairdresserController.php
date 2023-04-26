<?php

namespace App\Http\Controllers;

use App\Models\Hairdresser;
use App\Models\HairdresserAvailability;
use App\Models\HairdresserEvaluation;
use App\Models\HairdresserService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HairdresserController extends Controller
{
    public function insertView() {
        $times = [
            '08:00',
            '09:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00',
            '18:00',
        ];

        $days = [
          '0' => 'Domingo',
          '1' => 'Segunda',
          '2' => 'Terça',
          '3' => 'Quarta',
          '4' => 'Quinta',
          '5' => 'Sexta',
          '6' => 'Sábado',  
        ];


        return view('insert_hairdresser', [
            'times' => $times,
            'days' => $days
        ]);
    }

    public function insertAction(Request $request) {
        $validator = $request->validate([
            'name' => 'required|min:2',
            'specialties' => 'required',
            'days' => 'required',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'avatar' => 'required|file|mimes:jpg,png',
        ]);
        if($validator) {
            // salvando a foto
            $avatar = $request->file('avatar')->store('public'); 
            // pegando o nome da foto "a123124a.jpg"
            $avatar = last(explode('/', $avatar)); 

            $name = $request->name; 
            $name = trim($name," ");
            // separando os nomes, "Luiz Felipe" nome[0] = "Luiz" nome[1] = "Felipe"
            $name = explode(' ', $name); 
            if(count($name) > 1) {
                // se for nome composto ou c sobrenome "Luiz Felipe de Lima Martins" -> "Luiz Martins"
                $formatedName = $name[0].' '.last($name);
            } else {
                // se for apenas o nome "Luiz", "Pedro"
                $formatedName = $name[0]; 
            }

            $specialties = $request->specialties; 
            // separando cada especialidade
            $specialties = explode(',', $specialties);
            $formatedSpecialty = '';
            foreach($specialties as $spKey => $spValue) {
                // tirando espaços desnecessários de cada especialidade
                $specialties[$spKey] = trim($specialties[$spKey]," "); 
                // colocando as especialidades formatadas como string
                $formatedSpecialty .= $specialties[$spKey].', '; 
            }
            // removendo o ", " da string, no ultimo item das especialidades
            $formatedSpecialty = substr($formatedSpecialty, 0, strlen($formatedSpecialty) - 2); 

            $newHairdresser = Hairdresser::create([
                'name' => $formatedName,
                'avatar' => $avatar,
                'specialties' => $formatedSpecialty,
            ]);

            $startTime = $request->start_time;
            $endTime = $request->end_time;
            $carbonStartTime = Carbon::createFromFormat('H:i', $startTime);
            $carbonEndTime = Carbon::createFromFormat('H:i', $endTime);

            if($carbonEndTime->greaterThan($carbonStartTime)) {
                $interval = $carbonEndTime->diffInMinutes($carbonStartTime);

                $times = [];
                
                for ($i = 0; $i <= $interval; $i += 60) {
                    $time = $carbonStartTime->copy()->addMinutes($i)->format('H:i');
                    $times[] = $time;
                }
                // pra tirar o ultimo horario, já que o ultimo agendamento é por ex 15:00 a 16:00
                // logo, se o hairdresser parar de trabalhar 16, o ultimo horario dele é 15h.
                array_pop($times);

                $workTime = implode(', ', $times);

                $days = $request->days;
                $weekdays = "";
                $weekdays = explode(', ', $weekdays);
                $weekdays = array_filter($weekdays);

                foreach($days as $day) {
                    HairdresserAvailability::create([
                        'weekday' => $day,
                        'hours' => $workTime,
                        'hairdresser_id' => $newHairdresser['id'],
                    ]);
                }   

                return redirect()->back();
            } else {
                return redirect()->back()->withErrors([
                    'name' => 'O horário inicial deve ser antes que o final.',
                ]);
            }
        }

        return redirect()->back()->withInput($request->all());
    }

    public function getAll() {
        $hairdressers = Hairdresser::all();
        foreach($hairdressers as $hairdresser) {
            // arrumando o link da imagem
            $avatar = asset('storage/'.$hairdresser->avatar);

            $specialties = $hairdresser->specialties;
            
            // $availability = HairdresserAvailability::where('id_hairdresser', $hairdresser->id)->get();
            // $hdAvail = [];
            // foreach($availability as $avail) {
            //     $weekday = $avail->weekday;
            //     $days = [
            //         1 => 'Segunda-Feira',
            //         2 => 'Terça-Feira',
            //         3 => 'Quarta-Feira',
            //         4 => 'Quinta-Feira',
            //         5 => 'Sexta-Feira',
            //         6 => 'Sábado',
            //         7 => 'Domingo'
            //     ];

            //     $hours = explode(', ', $avail->hours);

            //     $hdAvail[] = [
            //         'weekday' => $days[$weekday],
            //         'hours' => $hours,
            //     ];
            // }

            // services
            // $services = HairdresserService::where('id_hairdresser', $hairdresser->id)->get();
            // $hdServices = [];
            // foreach($services as $service) {
            //     $hdServices[] = [
            //         'name' => $service->name,
            //         'price' => $service->price,
            //     ];
            // }
        }
        return view('hairdressers', ['hairdressers' => $hairdressers]);
    }

    public function updateView($id) {
        $hairdresser = Hairdresser::find($id);

        $hdAvailability = HairdresserAvailability::where('hairdresser_id', $id)->get();

        // pegar toda a string "hours", dar um explode na ", " e pegar o primeiro e o ultimo valor
        // e a partir desses valores, verificar em cada option se alguma se enquadra com o valor desejado
        // no horario inicial tem q verificar se alguma option tem o value igual ao do primeiro item do array
        // no horario final tem q verificar se alguma option tem o value igual ao do ultimo item do array

        $times = [
            '08:00',
            '09:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00',
            '18:00',
        ];

        $days = [
            '0' => 'Domingo',
            '1' => 'Segunda',
            '2' => 'Terça',
            '3' => 'Quarta',
            '4' => 'Quinta',
            '5' => 'Sexta',
            '6' => 'Sábado',
        ];

        $workDays = [];
        foreach($days as $day) {
            $dayKey = array_search($day, $days); // 0, 1, 2, 3, 4, 5, 6
            
            $availability = HairdresserAvailability::where('hairdresser_id', $id) // 7
            ->where('weekday', $dayKey) // 0, 1, 2, 3, 4, 5, 6
            ->first();
            if($availability) {
                $workDays[] = $availability['weekday'];
            }
        }

        return view('edit_hairdresser', [
            'hairdresser' => $hairdresser,
            'workDays' => $workDays,
            'days' => $days,
            'times' => $times
        ]);
    }

    public function updateAction($id, Request $request) {
        $validator = $request->validate([
            'name' => 'required|min:2',
            'avatar' => 'file|mimes:jpg,png',
            'specialties' => 'required'
        ]);
        if($validator) {
            $hairdresser = Hairdresser::find($id);

            if($request->avatar) {
                $avatar = $request->file('avatar')->store('public');
                $avatar = last(explode('/', $avatar));
            }
       
            $name = $request->name; 
            $name = trim($name," ");
            // separando os nomes, "Luiz Felipe" nome[0] = "Luiz" nome[1] = "Felipe"
            $name = explode(' ', $name); 
            if(count($name) > 1) {
                // se for nome composto ou c sobrenome "Luiz Felipe de Lima Martins" -> "Luiz Martins"
                $formatedName = $name[0].' '.last($name);
            } else {
                // se for apenas o nome "Luiz", "Pedro"
                $formatedName = $name[0]; 
            }

            $specialties = $request->specialties; 
            // separando cada especialidade
            $specialties = explode(',', $specialties);
            $formatedSpecialty = '';
            foreach($specialties as $spKey => $spValue) {
                // tirando espaços desnecessários de cada especialidade
                $specialties[$spKey] = trim($specialties[$spKey]," "); 
                // colocando as especialidades formatadas como string
                $formatedSpecialty .= $specialties[$spKey].', '; 
            }
            // removendo o ", " da string, no ultimo item das especialidades
            $formatedSpecialty = substr($formatedSpecialty, 0, strlen($formatedSpecialty) - 2); 

            if(!empty($avatar)) { 
                $hairdresser->update([
                    'name' => $formatedName,
                    'avatar' => $avatar,
                    'specialties' => $formatedSpecialty,
                ]);
            } else {
                $hairdresser->update([
                    'name' => $formatedName,
                    'specialties' => $formatedSpecialty,
                ]);
            }
            
            return redirect()->back();
        }

        return redirect()->back()->withInput($request->input());
    }

    public function delete($id) {
        $hairdresser = Hairdresser::find($id);
        if($hairdresser) {
            $hairdresser->delete();
        }

        return redirect()->back();
    }

    // public function getInfo($id) {
    //     $array = ['error' => ''];

    //     $hairdresser = Hairdresser::find($id);
    //     if($hairdresser) {
    //         $avatar = asset('storage/'.$hairdresser->avatar);

    //         $specialties = $hairdresser->specialties;
    //         $specialties = explode(',', $specialties);
    //         foreach($specialties as $spKey => $spValue) {
    //             $specialties[$spKey] = trim($specialties[$spKey]," ");
    //         }
           
    //         $availability = HairdresserAvailability::where('id_hairdresser', $id)->get();
    //         foreach($availability as $avail) {
    //             $weekday = $avail->weekday;
    //             $days = [
    //                 1 => 'Segunda-Feira',
    //                 2 => 'Terça-Feira',
    //                 3 => 'Quarta-Feira',
    //                 4 => 'Quinta-Feira',
    //                 5 => 'Sexta-Feira',
    //                 6 => 'Sábado',
    //                 7 => 'Domingo'
    //             ];

    //             $hours = explode(', ', $avail->hours);

    //             $hdAvail[] = [
    //                 'weekday' => $days[$weekday],
    //                 'hours' => $hours
    //             ];
    //         }

    //         // services
    //         $services = HairdresserService::where('id_hairdresser', $id)->get();
    //         $hdServices = [];
    //         foreach($services as $service) {
    //             $hdServices[] = [
    //                 'name' => $service->name,
    //                 'price' => $service->price,
    //             ];
    //         }

    //         // evaluations
    //         $evaluations = HairdresserEvaluation::where('id_hairdresser', $id)
    //         ->orderBy('stars', 'DESC')
    //         ->get();
    //         $hdEvaluations = [];
    //         foreach($evaluations as $evaluation) {
    //             $evaluationOwner = User::find($evaluation->id_user);

    //             $hdEvaluations[] = [
    //                 'evaluation_owner' => $evaluationOwner,
    //                 'stars' => $evaluation->stars,
    //                 'comment' => $evaluation->comment,
    //             ];
    //         }
            
    //         $array['data'] = [
    //             'id' => $id,
    //             'name' => $hairdresser->name,
    //             'avatar' => $avatar,
    //             'specialties' => $specialties,
    //             'availability' => $hdAvail,
    //             'services' => $hdServices,
    //             'hd_evaluations' => $hdEvaluations,
    //         ];
    //     } else {
    //         $array['error'] = 'Cabelereiro(a) não encontrado(a).';
    //         return $array;
    //     }

    //     return $array;
    // }

    
}
