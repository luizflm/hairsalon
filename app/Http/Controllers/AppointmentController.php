<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Hairdresser;
use App\Models\HairdresserAvailability;
use App\Models\HairdresserService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function getMyAppointments() {
        $idUser = Auth::user()->id; // pegando o id do usuário logado

        // pegando os agendamentos não concluídos com o id do usuário logado
        $appointments = Appointment::where('user_id', $idUser) 
        ->where('was_done', '0')
        ->get();
 
        // criando array onde ficarão os appointments 
        $data = [];
        foreach($appointments as $appointment) { // pra cada agendamento
            // encontrar o hairdresser responsável
            $hairdresser = Hairdresser::find($appointment->hairdresser_id);
            // $hairdresser->avatar = asset('storage/'.$hairdresser->avatar);

            // encontrar o serviço do agendamento
            $service = HairdresserService::find($appointment->hairdresser_service_id);

            // transformar a string de ap_datetime em um array separado pelo " " (espaço)
            $formatedApDatetime = explode(' ', $appointment->ap_datetime);
            // atribuir o primeiro valor do array à $apDate ex: (2023-04-28)
            $apDate = $formatedApDatetime[0];
            // separar a string em um array pelo "-" (traço)
            $apDate = explode('-', $apDate);
            // formatar a string com o novo formato (20/04/2023)
            $formatedApDate = $apDate[2].'/'.$apDate[1].'/'.$apDate[0];

            // atribuir o horário do agendamento à $apTime ex(09:00:00)
            $apTime = $formatedApDatetime[1];

            // criar o modelo de $appointment
            $appointment = [
                'id' => $appointment->id,
                'hairdresser' => $hairdresser,
                'service' => $service->name,
                'day' => $formatedApDate,
                'time' => $apTime,
            ];

            // inserir o appointment no array $data a cada iteração
            $data[] = $appointment;
        }
        if(count($data) != 0) { // se tem pelo menos um appointment em $data
            // renderiza a view
            return view('user_appointments', [
                'appointments' => $data,
            ]);
        }
        // se algo der errado, volta para a página anterior
        return redirect()->back();
    }
    
    public function setAppointmentView() {
        // pegando todos os hairdressers
        $hairdressers = Hairdresser::all();

        // pegando todos os serviços 
        $services = HairdresserService::all();

        // horário de funcionamento do salão
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

        return view('insert_appointment', [
            'times' => $times,
            'hairdressers' => $hairdressers,
            'services' => $services
        ]);
    }

    public function setAppointmentAction(Request $request) {
        $validator = $request->validate([
            'ap_day' => 'required|date_format:Y-m-d',
            'ap_time' => 'required|date_format:H:i',
            'hairdresser_id' => 'required',
            'service_id' => 'required'
        ]);
        if($validator) {
            // recebendo dados
            $apDay = $request->ap_day;
            $apTime = $request->ap_time;
            // criando uma string com a data e o horário do appointment ex: (2023-04-20 09:00)
            $apDatetime = $apDay.' '.$apTime;
            // recebendo mais dados
            $hairdresserId = $request->hairdresser_id;
            $serviceId = $request->service_id;
            // pegando o id do usuário logado
            $userId = Auth::user()->id;
            // pegando o hairdresser com o id do appointment
            $hdExists = Hairdresser::find($hairdresserId);
            if($hdExists) { // verificando se o(a) cabelereiro(a) existe
                // se sim, pega o serviço desejado com o id do hairdresser e o id do serviço enviado
                $hdService = HairdresserService::where('hairdresser_id', $hairdresserId)
                ->where('id', $serviceId)
                ->first();
                if($hdService) { // verifica se o cabelereiro(a) faz o serviço?
                    // se sim, pega a data atual ex: (2023-04-28 09:00:00)
                    $now = date('Y-m-d H:i');
                    // cria um objeto carbon com o $apDatetime
                    $apDate = Carbon::createFromFormat('Y-m-d H:i', $apDatetime);
                    // verifica se a data do appointment é no futuro
                    $isDateFuture = $apDate->greaterThan($now);
                    if($isDateFuture) { // usuário está marcando horário no futuro ou passado?
                        // se é no futuro, verifica se já existe um appointment naquele horário/data com aquele hairdresser
                        $hasAppointments = Appointment::where('ap_datetime', $apDatetime)
                        ->where('hairdresser_id', $hairdresserId)
                        ->count();
                        if($hasAppointments == 0) { // se não há um agendamento no horário/dia desejado
                            // transforma a string de data/horario em um array separados por " " (espaço)
                            $formatedDate = explode(' ', $apDatetime);
                            // pega a key do dia da semana do appointment a partir da data desejada
                            $ap_weekday = date('w', strtotime($formatedDate[0]));

                            // pega toda a disponibilidade do hairdresser do appointment
                            $hdAvailability = HairdresserAvailability::where('hairdresser_id', $hairdresserId)
                            ->get();
                            // cria um array onde ficarão os dias de disponibilidade do hairdresser
                            $hdWeekdays = [];
                            foreach($hdAvailability as $hdAvail) {
                                // atribui cada key de dia da semana em um indice do array
                                $hdWeekdays[] = $hdAvail->weekday;
                            }
                            // verifica se a key do dia do appointment desejado está no array de keys de dias da semana que o hairdresser trabalha
                            if(in_array($ap_weekday, $hdWeekdays)) { // se sim
                                // pega os horários que o hairdresser trabalha no dia desejado para o appointment
                                $isTimeAvail = HairdresserAvailability::select(['hours'])
                                ->where('hairdresser_id', $hairdresserId)
                                ->where('weekday', $ap_weekday)
                                ->first();
                                // atribui o valor a uma variavel
                                $availTimes = $isTimeAvail->hours;

                                // transforma a string de horários em um array
                                $hdTimes = explode(', ', $availTimes);
                                // remove o  ultimo horário para que o ultimo horário seja ex: 15:00 - 16:00
                                array_pop($hdTimes); 
                                // verifica se o horário desejado está no horário de trabalho do hairdresser
                                if(in_array($formatedDate[1], $hdTimes)) {      
                                    // se sim, formata a string de horário ex: (09:00) -> (09:00:00)
                                    $apDatetime .= ':00';            
                                    // cria o appointment
                                    Appointment::create([
                                        'hairdresser_id' => $hairdresserId,
                                        'user_id' => $userId,
                                        'hairdresser_service_id' => $serviceId,
                                        'ap_datetime' => $apDatetime,
                                    ]);
                                    // após todo o processamento, retorna para a home
                                    return redirect()->route('home');
                                } else { // os erros são autoexplicativos de acordo com as mensagens
                                    return back()->withErrors([
                                        'ap_day' => 'O(a) cabelereiro(a) não trabalha no horário desejado.',
                                    ])->onlyInput(['ap_day', 'ap_time', 'hairdresser_id', 'service_id']);
                                }
                            } else { // os erros são autoexplicativos de acordo com as mensagens
                                return back()->withErrors([
                                    'ap_day' => 'O(a) cabelereiro(a) não trabalha no dia desejado.',
                                ])->onlyInput(['ap_day', 'ap_time', 'hairdresser_id', 'service_id']);
                            }
                        } else { // os erros são autoexplicativos de acordo com as mensagens
                            return back()->withErrors([
                                'ap_day' => 'Já há um agendamento para este dia/hora, tente em outro dia/horário.',
                            ])->onlyInput(['ap_day', 'ap_time', 'hairdresser_id', 'service_id']);
                        } 
                    } else { // os erros são autoexplicativos de acordo com as mensagens
                        return back()->withErrors([
                            'ap_day' => 'Data e/ou hora inválida.',
                        ])->onlyInput(['ap_day', 'ap_time', 'hairdresser_id', 'service_id']);
                    }
                } else { // os erros são autoexplicativos de acordo com as mensagens
                    return back()->withErrors([
                        'ap_day' => 'O(a) cabelereiro não faz o serviço solicitado.',
                    ])->onlyInput(['ap_day', 'ap_time', 'hairdresser_id', 'service_id']);
                }
            } else { // os erros são autoexplicativos de acordo com as mensagens
               return back()->withErrors([
                'ap_day' => 'O(a) cabelereiro(a) não foi encontrado.',
               ])->onlyInput(['ap_day', 'ap_time', 'hairdresser_id', 'service_id']);
            }
        }
    }

    public function updateView($id) {
        // pegando todos os hairdressers
        $hairdressers = Hairdresser::all();

        // pegando todos os serviços 
        $services = HairdresserService::all();

        // horário de funcionamento do salão
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

        $loggedUserId = Auth::user()->id; // pegando o usuário logado

        $appointment = Appointment::find($id); // achando o appointment com o id desejado

        // verificando se quem quer editar o appointment é o dono do appointment
        if($appointment->user_id == $loggedUserId) {
            // transformando a string ap_datetime em um array 
            $formatedApDatetime = explode(' ', $appointment->ap_datetime);
            // atribuindo o primeiro valor do array à apDate ex(2023-04-20)
            $apDate = $formatedApDatetime[0];

            // colocando o valor de $apDate pra day no appointment
            $appointment['day'] = $apDate;
            // colocando o horário ex(09:00:00) à time no appointment
            $appointment['time'] = $formatedApDatetime[1];

            // pegando o serviço do appointment que deseja-se editar
            $service = HairdresserService::find($appointment->hairdresser_service_id);
            // atribuindo o nome do serviço à service no appointment
            $appointment['service'] = $service->name;

            // após o processamento, renderiza a view com os dados necessários
            return view('edit_appointment', [
                'appointment' => $appointment,
                'times' => $times,
                'services' => $services,
                'hairdressers' => $hairdressers,
            ]);
        }
        // caso algo dê errado, volta para a página anterior
        return redirect()->back();
    }

    public function updateAction($id, Request $request) {
        $validator = $request->validate([
            'ap_day' => 'required|date_format:Y-m-d',
            'ap_time' => 'required|date_format:H:i',
            'hairdresser_id' => 'required',
            'service_id' => 'required'
        ]);
        if($validator) {
            $apDay = $request->ap_day;
            $apTime = $request->ap_time;
            // cria uma string com a data/horario do appoitment ex(2023-04-28 09:00)
            $apDatetime = $apDay.' '.$apTime;
            $hairdresserId = $request->hairdresser_id;
            $serviceId = $request->service_id;
            $userId = Auth::user()->id; // pegando o id do usuário logado

            // pegando o appointment com o id do usuário logado e o id do appointment que será editado
            $appointment = Appointment::where('user_id', $userId)
            ->where('id', $id)
            ->first();
            if($appointment) { // verificando se o appointment foi encontrado
                // se sim, verifica se o hairdresser existe
                $hdExists = Hairdresser::find($hairdresserId);
                if($hdExists) {
                    // se existe, verifica se o cabelereiro faz o serviço desejado
                    $hdService = HairdresserService::where('hairdresser_id', $hairdresserId)
                    ->where('id', $serviceId)
                    ->first();
                    if($hdService) {
                        // se faz, pega a data atual ex(2023-04-28 09:00:00)
                        $now = date('Y-m-d H:i');
                        // cria um objeto carbon com o $apDatetime
                        $apDate = Carbon::createFromFormat('Y-m-d H:i', $apDatetime);
                        // verifica se o horário marcado é para o futuro
                        $isDateFuture = $apDate->greaterThan($now);
                        if($isDateFuture) {
                            // se sim, verifica se já existe algum appointment marcado com o hairdresser na data/horário desejado
                            $hasAppointments = Appointment::where('ap_datetime', $apDatetime)
                            ->where('hairdresser_id', $hairdresserId)
                            ->first();
                            // se não tem ou se foi encontrado um appointment na condição da query, mas esse appointment é o mesmo que está sendo editado
                            if(!$hasAppointments || $hasAppointments->id == $appointment->id) { // há um agendamento no horário/dia desejado?
                                // transforma a string de $apDatetime em um array
                                $formatedDate = explode(' ', $apDatetime);
                                // pega a key do dia da semana referente a data desejada para o appointment
                                $ap_weekday = date('w', strtotime($formatedDate[0]));
                                // pega todas as disponibilidades do hairdresser desejado
                                $hdAvailability = HairdresserAvailability::where('hairdresser_id', $hairdresserId)
                                ->get();
                                // cria um array onde ficarão as keys dos dias da semana que o hairdresser trabalha
                                $hdWeekdays = [];
                                foreach($hdAvailability as $hdAvail) {
                                    // atribui cada key para um indice no array
                                    $hdWeekdays[] = $hdAvail->weekday;
                                }
                                // verifica se o dia desejado para o appointment está nos dias de trabalho do hairdresser
                                if(in_array($ap_weekday, $hdWeekdays)) {
                                    // se sim, pega os horários que o hairdresser trabalha
                                    $isTimeAvail = HairdresserAvailability::select(['hours'])
                                    ->where('hairdresser_id', $hairdresserId)
                                    ->where('weekday', $ap_weekday)
                                    ->first();
                                    // atribui essa string à uma variável
                                    $availTimes = $isTimeAvail->hours;

                                    // "quebra" essa string em um array, separando por " " (espaço)
                                    $hdTimes = explode(', ', $availTimes);
                                    // remove o ultimo horário para que o ultimo horário seja ex: 15:00 - 16:00
                                    array_pop($hdTimes);
                                    // verifica se o horário desejado está no horário de trabalho do hairdresser
                                    if(in_array($formatedDate[1], $hdTimes)) {      
                                        // se sim, formata a string $apDatetime para ficar ex: (2023-04-28 09:00:00)
                                        $apDatetime .= ':00';          
                                        // atualiza o appointment  
                                        $appointment->update([
                                            'hairdresser_id' => $hairdresserId,
                                            'user_id' => $userId,
                                            'hairdresser_service_id' => $serviceId,
                                            'ap_datetime' => $apDatetime,
                                        ]);
                                        // após os processamentos, redireciona para a home
                                        return redirect()->route('home');
                                    } else { // os erros são auto-explicativos de acordo com as mensagens
                                        return back()->withErrors([
                                            'ap_day' => 'O(a) cabelereiro(a) não trabalha no horário desejado.',
                                        ]);
                                    }
                                } else { // os erros são auto-explicativos de acordo com as mensagens
                                    return back()->withErrors([
                                        'ap_day' => 'O(a) cabelereiro(a) não trabalha no dia desejado.',
                                    ]);
                                }
                            } else { // os erros são auto-explicativos de acordo com as mensagens
                                return back()->withErrors([
                                    'ap_day' => 'Já há um agendamento para este dia/hora, tente em outro dia/horário.',
                                ]);
                            }
                        } else { // os erros são auto-explicativos de acordo com as mensagens
                            return back()->withErrors([
                                'ap_day' => 'Data e/ou hora inválida.',
                            ]);
                        }
                    } else { // os erros são auto-explicativos de acordo com as mensagens
                        return back()->withErrors([
                            'ap_day' => 'O(a) cabelereiro não faz o serviço solicitado.',
                        ]);
                    }
                } else { // os erros são auto-explicativos de acordo com as mensagens
                    return back()->withErrors([
                        'ap_day' => 'O(a) cabelereiro(a) não foi encontrado.',
                    ]);
                }
            } else { // os erros são auto-explicativos de acordo com as mensagens
                return redirect()->route('home');
            }
        }  
    }

    public function delete($id) {
        $appointment = Appointment::find($id);
        $loggedUserId = Auth::user()->id;

        if($appointment && $appointment->user_id == $loggedUserId) {
            $appointment->delete();
        }

        return back();
    }

    public function getAllUndone(Request $request) {
        $page = $request->page;
        // pegando todos os agendamentos que não estão conclúidos
        $fullAppointments = Appointment::where('was_done', 0)->count();
        // pegando o número de páginas disponiveis, sendo 4 itens mostrados por página
        $pageCount = ceil($fullAppointments / 4);

        // pegando os agendamentos não conclúidos, ordenando de forma decrescente o horário/data de agendamento

        $appointments = Appointment::where('was_done', 0)->orderBy('ap_datetime', 'DESC')->paginate(4);
        if($appointments->items()) { // verificando se foi encontrado algum item de acordo com a query
            if($page != 0) { // verificando se a página enviada não é 0
                // verificando se a página desejada é igual ou menor ao  número de páginas disponíveis
                if($page <= $pageCount) {
                    foreach($appointments as $appointment) { // pra cada agendamento encontrado
                        // busca o hairdresser responsável
                        $hairdresser = Hairdresser::where('id', $appointment['hairdresser_id'])->first();
                        // busca o serviço do agendamento
                        $service = HairdresserService::where('id', $appointment['hairdresser_service_id'])->first();
                        // busca o usuário que fez o agendamento
                        $userName = User::where('id', $appointment['user_id'])->pluck('name');

                        // transforma a string de horário ex: "2023-04-28 09:00:00" em um array divido por espaço
                        $formatedDatetime = explode(' ', $appointment['ap_datetime']);
                        // "joga" o primeiro item (2022-04-28) para a $apDate
                        $apDate = $formatedDatetime[0];
                        // separa cada número de data em um array 
                        $apDate = explode('-', $apDate);
                        // cria a string de data formatada, ex: (2023-04-28) -> (28/04/2023)
                        $formatedApDate = $apDate[2].'/'.$apDate[1].'/'.$apDate[0];

                        // "joga" o segundo item (09:00:00) para a $apTime  
                        $apTime = $formatedDatetime[1];
                        // separa cada número de hora em um array
                        $apTime = explode(':', $apTime);
                        // cria a string de horário formatada, ex: (09:00:00) -> (09:00)
                        $formatedApTime = $apTime[0].':'.$apTime[1];

                        // após esses processamentos, cria-se o "modelo" de appointment que será enviado para a view
                        $appointment = [
                            'id' => $appointment['id'],
                            'ap_date' => $formatedApDate,
                            'ap_time' => $formatedApTime,
                            'user' => $userName[0],
                            'hairdresser' => $hairdresser,
                            'service' => $service,
                        ];

                        // adiciona cada um (de acordo com o foreach) em um array
                        $apList[] = $appointment;
                    }

                    // após os processamentos anteriores, renderiza a view enviando os dados necessários
                    return view('appointments', [
                        'appointments' => $apList,
                        'page' => $page,
                        'items' => $fullAppointments,
                    ]);
                }
            }
        }
        // caso alguma verificação dê errado, retorna para a página anterior
        return redirect()->back();
    }
    
    public function getAllDone(Request $request) {
        $page = $request->page;
        // pegando todos os agendamentos que estão conclúidos
        $fullAppointments = Appointment::where('was_done', 1)->count();
        // pegando o número de páginas disponiveis, sendo 4 itens mostrados por página
        $pageCount = ceil($fullAppointments / 4);
    
        // pegando os agendamentos conclúidos, ordenando de forma decrescente o horário/data de agendamento
        $appointments = Appointment::where('was_done', 1)->orderBy('ap_datetime', 'DESC')->paginate(4);
        if($appointments->items()) { // verificando se foi encontrado algum item de acordo com a query
            if($page != 0) { // verificando se a página enviada não é 0
                // verificando se a página desejada é igual ou menor ao  número de páginas disponíveis
                if($page <= $pageCount) {
                    foreach($appointments as $appointment) {  // pra cada agendamento encontrado
                        // busca o hairdresser responsável
                        $hairdresserName = Hairdresser::where('id', $appointment['hairdresser_id'])->pluck('name');
                        // busca o serviço do agendamento
                        $serviceName = HairdresserService::where('id', $appointment['hairdresser_service_id'])->pluck('name');
                        // busca o usuário que fez o agendamento
                        $userName = User::where('id', $appointment['user_id'])->pluck('name');
    
                        // transforma a string de horário ex: "2023-04-28 09:00:00" em um array divido por espaço
                        $formatedDatetime = explode(' ', $appointment['ap_datetime']);
                        // "joga" o primeiro item (2022-04-28) para a $apDate
                        $apDate = $formatedDatetime[0];
                        // separa cada número de data em um array 
                        $apDate = explode('-', $apDate);
                        // cria a string de data formatada, ex: (2023-04-28) -> (28/04/2023)
                        $formatedApDate = $apDate[2].'/'.$apDate[1].'/'.$apDate[0];
    
                        // "joga" o segundo item (09:00:00) para a $apTime  
                        $apTime = $formatedDatetime[1];
                        // separa cada número de hora em um array
                        $apTime = explode(':', $apTime);
                        // cria a string de horário formatada, ex: (09:00:00) -> (09:00)
                        $formatedApTime = $apTime[0].':'.$apTime[1];
    
                        // após esses processamentos, cria-se o "modelo" de appointment que será enviado para a view
                        $appointment = [
                            'id' => $appointment['id'],
                            'ap_date' => $formatedApDate,
                            'ap_time' => $formatedApTime,
                            'user' => $userName[0],
                            'hairdresser' => $hairdresserName[0],
                            'service' => $serviceName[0],
                        ];
    
                        // adiciona cada um (de acordo com o foreach) em um array
                        $apList[] = $appointment;
                    }
    
                    // após os processamentos anteriores, renderiza a view enviando os dados necessários
                    return view('appointments_done', [
                        'appointments' => $apList,
                        'page' => $page,
                        'items' => $fullAppointments,
                    ]);
                }
            }
        }
        // caso alguma verificação dê errado, retorna para a página anterior
        return redirect()->back();
    }
}

  
   