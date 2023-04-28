<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $idUser = Auth::user()->id;

        $appointments = Appointment::where('user_id', $idUser)
        ->where('was_done', '0')
        ->get();
        foreach($appointments as $appointment) {
            $hairdresser = Hairdresser::find($appointment->hairdresser_id);
            $hairdresser->avatar = asset('storage/'.$hairdresser->avatar);

            $service = HairdresserService::find($appointment->hairdresser_service_id);

            $formatedApDatetime = explode(' ', $appointment->ap_datetime);
            $apDate = $formatedApDatetime[0];
            $apDate = explode('-', $apDate);
            $formatedApDate = $apDate[2].'/'.$apDate[1].'/'.$apDate[0];

            $apTime = $formatedApDatetime[1];

            $appointment = [
                'id' => $appointment->id,
                'hairdresser' => $hairdresser,
                'service' => $service->name,
                'day' => $formatedApDate,
                'time' => $apTime,
            ];

            $data['appointments'][] = $appointment;
        }

        return view('user_appointments', $data);
    }
    
    public function setAppointmentView() {
        return view('insert_appointment');
    }

    public function setAppointmentAction(Request $request) {
        $validator = $request->validate([
            'ap_day' => 'required|date_format:Y-m-d',
            'ap_time' => 'required|date_format:H:i',
            'hairdresser_id' => 'required',
            'service_id' => 'required'
        ]);
        if($validator) {
            $apDay = $request->ap_day;
            $apTime = $request->ap_time;
            $apDatetime = $apDay.' '.$apTime;
            $hairdresserId = $request->hairdresser_id;
            $serviceId = $request->service_id;
            $userId = Auth::user()->id;
            $hdExists = Hairdresser::find($hairdresserId);
            if($hdExists) { // cabelereiro(a) existe
                $hdService = HairdresserService::where('hairdresser_id', $hairdresserId)
                ->where('id', $serviceId)
                ->first();
                if($hdService) { // cabelereiro(a) faz o serviço?
                    $now = date('Y-m-d H:i');
                    $apDate = Carbon::createFromFormat('Y-m-d H:i', $apDatetime);
                    $isDateFuture = $apDate->greaterThan($now);
                    if($isDateFuture) { // usuário está marcando horário no futuro ou passado?
                        $hasAppointments = Appointment::where('ap_datetime', $apDatetime)
                        ->where('hairdresser_id', $hairdresserId)
                        ->count();
                        if($hasAppointments == 0) { // há um agendamento no horário/dia desejado?
                            $formatedDate = explode(' ', $apDatetime);
                            $ap_weekday = date('w', strtotime($formatedDate[0]));

                            $hdAvailability = HairdresserAvailability::where('hairdresser_id', $hairdresserId)
                            ->get();
                            $hdWeekdays = [];
                            foreach($hdAvailability as $hdAvail) {
                                $hdWeekdays[] = $hdAvail->weekday;
                            }
                            if(in_array($ap_weekday, $hdWeekdays)) { // cabelereiro(a) trabalha no dia desejado?
                                $isTimeAvail = HairdresserAvailability::select(['hours'])
                                ->where('hairdresser_id', $hairdresserId)
                                ->where('weekday', $ap_weekday)
                                ->first();
                                $availTimes = $isTimeAvail->hours;

                                $hdTimes = explode(', ', $availTimes);
                                array_pop($hdTimes); // removendo ultimo horário para que o ultimo horário seja ex: 15:00 - 16:00
                                if(in_array($formatedDate[1], $hdTimes)) {      
                                    $apDatetime .= ':00';            
                                    Appointment::create([
                                        'hairdresser_id' => $hairdresserId,
                                        'user_id' => $userId,
                                        'hairdresser_service_id' => $serviceId,
                                        'ap_datetime' => $apDatetime,
                                    ]);

                                    return redirect()->route('home');
                                } else {
                                    return back()->withErrors([
                                        'ap_day' => 'O(a) cabelereiro(a) não trabalha no horário desejado.',
                                    ])->onlyInput(['ap_day', 'ap_time', 'hairdresser_id', 'service_id']);
                                }
                            } else {
                                return back()->withErrors([
                                    'ap_day' => 'O(a) cabelereiro(a) não trabalha no dia desejado.',
                                   ])->onlyInput(['ap_day', 'ap_time', 'hairdresser_id', 'service_id']);
                            }
                        } else {
                            return back()->withErrors([
                                'ap_day' => 'Já há um agendamento para este dia/hora, tente em outro dia/horário.',
                               ])->onlyInput(['ap_day', 'ap_time', 'hairdresser_id', 'service_id']);
                        }
                    } else {
                        return back()->withErrors([
                            'ap_day' => 'Data e/ou hora inválida.',
                           ])->onlyInput(['ap_day', 'ap_time', 'hairdresser_id', 'service_id']);
                    }
                } else {
                    return back()->withErrors([
                        'ap_day' => 'O(a) cabelereiro não faz o serviço solicitado.',
                       ])->onlyInput(['ap_day', 'ap_time', 'hairdresser_id', 'service_id']);
                }
            } else {
               return back()->withErrors([
                'ap_day' => 'O(a) cabelereiro(a) não foi encontrado.',
               ])->onlyInput(['ap_day', 'ap_time', 'hairdresser_id', 'service_id']);
            }
        }
    }

    public function updateView($id) {
        $loggedUserId = Auth::user()->id;

        $appointment = Appointment::find($id);

        if($appointment->user_id == $loggedUserId) {
            $formatedApDatetime = explode(' ', $appointment->ap_datetime);
            $apDate = $formatedApDatetime[0];

            $appointment['day'] = $apDate;
            $appointment['time'] = $formatedApDatetime[1];

            $service = HairdresserService::find($appointment->hairdresser_service_id);
            $appointment['service'] = $service->name;

            return view('edit_appointment', ['appointment' => $appointment]);
        }

        return back();
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
            $apDatetime = $apDay.' '.$apTime;
            $hairdresserId = $request->hairdresser_id;
            $serviceId = $request->service_id;
            $userId = Auth::user()->id;

            $appointment = Appointment::where('user_id', $userId)
            ->where('id', $id)
            ->first();
            if($appointment) {
                $hdExists = Hairdresser::find($hairdresserId);
                if($hdExists) { // cabelereiro(a) existe
                    $hdService = HairdresserService::where('hairdresser_id', $hairdresserId)
                    ->where('id', $serviceId)
                    ->first();
                    if($hdService) { // cabelereiro(a) faz o serviço?
                        $now = date('Y-m-d H:i');
                        $apDate = Carbon::createFromFormat('Y-m-d H:i', $apDatetime);
                        $isDateFuture = $apDate->greaterThan($now);
                        if($isDateFuture) { // usuário está marcando horário no futuro ou passado?
                            $hasAppointments = Appointment::where('ap_datetime', $apDatetime)
                            ->where('hairdresser_id', $hairdresserId)
                            ->first();
                            if(!$hasAppointments || $hasAppointments->id == $appointment->id) { // há um agendamento no horário/dia desejado?
                                $formatedDate = explode(' ', $apDatetime);
                                $ap_weekday = date('w', strtotime($formatedDate[0]));

                                $hdAvailability = HairdresserAvailability::where('hairdresser_id', $hairdresserId)
                                ->get();
                                $hdWeekdays = [];
                                foreach($hdAvailability as $hdAvail) {
                                    $hdWeekdays[] = $hdAvail->weekday;
                                }
                                if(in_array($ap_weekday, $hdWeekdays)) { // cabelereiro(a) trabalha no dia desejado?
                                    $isTimeAvail = HairdresserAvailability::select(['hours'])
                                    ->where('hairdresser_id', $hairdresserId)
                                    ->where('weekday', $ap_weekday)
                                    ->first();
                                    $availTimes = $isTimeAvail->hours;

                                    $hdTimes = explode(', ', $availTimes);
                                    array_pop($hdTimes); // removendo ultimo horário para que o ultimo horário seja ex: 15:00 - 16:00
                                    if(in_array($formatedDate[1], $hdTimes)) {      
                                        $apDatetime .= ':00';            
                                        $appointment->update([
                                            'hairdresser_id' => $hairdresserId,
                                            'user_id' => $userId,
                                            'hairdresser_service_id' => $serviceId,
                                            'ap_datetime' => $apDatetime,
                                        ]);

                                        return redirect()->route('home');
                                    } else {
                                        return back()->withErrors([
                                            'ap_day' => 'O(a) cabelereiro(a) não trabalha no horário desejado.',
                                        ]);
                                    }
                                } else {
                                    return back()->withErrors([
                                        'ap_day' => 'O(a) cabelereiro(a) não trabalha no dia desejado.',
                                    ]);
                                }
                            } else {
                                return back()->withErrors([
                                    'ap_day' => 'Já há um agendamento para este dia/hora, tente em outro dia/horário.',
                                ]);
                            }
                        } else {
                            return back()->withErrors([
                                'ap_day' => 'Data e/ou hora inválida.',
                            ]);
                        }
                    } else {
                        return back()->withErrors([
                            'ap_day' => 'O(a) cabelereiro não faz o serviço solicitado.',
                        ]);
                    }
                } else {
                    return back()->withErrors([
                        'ap_day' => 'O(a) cabelereiro(a) não foi encontrado.',
                    ]);
                }
            } else {
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

  
   