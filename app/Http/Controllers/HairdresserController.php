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

            $startTime = $request->start_time;
            $endTime = $request->end_time;
            $carbonStartTime = Carbon::createFromFormat('H:i', $startTime);
            $carbonEndTime = Carbon::createFromFormat('H:i', $endTime);

            if($carbonEndTime->greaterThan($carbonStartTime)) {
                $newHairdresser = Hairdresser::create([
                    'name' => $formatedName,
                    'avatar' => $avatar,
                    'specialties' => $formatedSpecialty,
                ]);

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
                // $weekdays = "";
                // $weekdays = explode(', ', $weekdays);
                // $weekdays = array_filter($weekdays);

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

        $workHours = $hdAvailability[0]['hours'];
        $workHours = explode(', ', $workHours);

        $workDays = [];
        foreach($hdAvailability as $availability) {
            $workDays[] = $availability['weekday'];
        }

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

        return view('edit_hairdresser', [
            'hairdresser' => $hairdresser,
            'workDays' => $workDays,
            'days' => $days,
            'times' => $times,
            'workHours' => $workHours,
        ]);
    }

    public function updateAction($id, Request $request) {
        $validator = $request->validate([
            'name' => 'required|min:2',
            'specialties' => 'required',
            'days' => 'required',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'avatar' => 'file|mimes:jpg,png',
        ]); // validando os dados do form
        if($validator) { // Se não deu nenhum erro
            $hairdresser = Hairdresser::find($id); // pega o hairdresser

            if($request->avatar) { // se foi enviado algum avatar para edição
                $avatar = $request->file('avatar')->store('public'); // salva o avatar na public\storage
                $avatar = last(explode('/', $avatar)); // formata o nome pra salvar no banco de dados
            }
       
            $name = $request->name; 
            // retirando os espaços desnecessários da string
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
            
            $startTime = $request->start_time;
            $endTime = $request->end_time;
            $carbonStartTime = Carbon::createFromFormat('H:i', $startTime);
            $carbonEndTime = Carbon::createFromFormat('H:i', $endTime);

            // verificando se o horário final é menor que o inicial, ex: 09:00 < 10:00?
            if($carbonEndTime->greaterThan($carbonStartTime)) {
                if(!empty($avatar)) {  // verificando se foi enviado algum avatar
                    $hairdresser->update([
                        'name' => $formatedName,
                        'specialties' => $formatedSpecialty,
                        'avatar' => $avatar,
                    ]);
                } else { // senão, atualizar apenas os dados obrigatórios.
                    $hairdresser->update([
                        'name' => $formatedName,
                        'specialties' => $formatedSpecialty,
                    ]);
                }

                // pegando os availabilities antigos e excluindo eles
                $hdAvailabilities = HairdresserAvailability::where('hairdresser_id', $id)->get(); 
                foreach($hdAvailabilities as $availability) {
                    $availability->delete();
                }

                // diferença de minutos entre o tempo inicial e tempo final, ex: t.i 08:00 t.f 17:00
                $interval = $carbonEndTime->diffInMinutes($carbonStartTime);

                $times = []; // criando array que terá os horários de trabalho do hairdresser
                
                // enquanto $i for menor Ou igual ao intervalo, a cada iteração acrescentar 60.
                // logo, $i fica se fosse minutos, ou seja, começa em 0, dps vai pra 60, dps pra 120
                // até ficar igual ao intervalo entre o t.i e t.f
                for ($i = 0; $i <= $interval; $i += 60) {  
                    $time = $carbonStartTime->copy()->addMinutes($i)->format('H:i');
                    $times[] = $time;
                }
                // pra tirar o ultimo horario, já que o ultimo agendamento é por ex 15:00 a 16:00
                // logo, se o hairdresser parar de trabalhar 16, o ultimo horario dele é 15h.
                array_pop($times);

                // criando a string com os horários de trabalho, que será salva no banco de dados
                $workTime = implode(', ', $times);

                // pegando os dias de trabalhos escolhidos para o hairdresser
                $days = $request->days;

                // $weekdays = "";
                // $weekdays = explode(', ', $weekdays); 
                // $weekdays = array_filter($weekdays); 

                // para cada dia escolhido, criar um registro com a key do dia, string dos horários e o
                // id do hairdresser
                foreach($days as $day) { 
                    HairdresserAvailability::create([
                        'weekday' => $day,
                        'hours' => $workTime,
                        'hairdresser_id' => $id,
                    ]);
                }   

                // Não atualizei os dados de availability pois é bem mais fácil deletar os antigos e 
                // inserir os novos, não teria falhas e não alteraria os agendamentos que foram feitos
                // antes do hairdresser ser atualizado.

                return redirect()->back(); // após a saída, redirecionar pra rota anterior
            } else {
                return redirect()->back()->withErrors([
                    'name' => 'O horário inicial deve ser antes que o final.',
                ]);
            }
        }

        return redirect()->back(); // se não entrar no validator, volta pra tela de edit com os erros
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
