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

    public function getAll(Request $request) {
        $page = $request->page;
        $fullAppointments = Appointment::where('was_done', 0)->count();
        $pageCount = ceil($fullAppointments / 4);

        $appointments = Appointment::where('was_done', 0)->orderBy('ap_datetime', 'DESC')->paginate(4);
        if($appointments->items()) {
            if($page != 0) {
                if($page <= $pageCount) {
                    foreach($appointments as $appointment) {
                        $hairdresser = Hairdresser::where('id', $appointment['hairdresser_id'])->first();
                        $service = HairdresserService::where('id', $appointment['hairdresser_service_id'])->first();
                        $userName = User::where('id', $appointment['user_id'])->pluck('name');

                        $formatedDatetime = explode(' ', $appointment['ap_datetime']);
                        $apDate = $formatedDatetime[0];
                        $apDate = explode('-', $apDate);
                        $formatedApDate = $apDate[2].'/'.$apDate[1].'/'.$apDate[0];

                        $apTime = $formatedDatetime[1];
                        $apTime = explode(':', $apTime);
                        $formatedApTime = $apTime[0].':'.$apTime[1];

                        $appointment = [
                            'id' => $appointment['id'],
                            'ap_date' => $formatedApDate,
                            'ap_time' => $formatedApTime,
                            'user' => $userName[0],
                            'hairdresser' => $hairdresser,
                            'service' => $service,
                        ];

                        $apList[] = $appointment;
                    }
                    return view('appointments', [
                        'appointments' => $apList,
                        'page' => $page,
                        'items' => $fullAppointments,
                    ]);
                }
            }
        }

        return redirect()->back();
    }

    public function getAllDone(Request $request) {
        $page = $request->page;
        $fullAppointments = Appointment::where('was_done', 1)->count();
        $pageCount = ceil($fullAppointments / 4);

        $appointments = Appointment::where('was_done', 1)->orderBy('ap_datetime', 'DESC')->paginate(4);
        if($appointments->items()) {
            if($page != 0) {
                if($page <= $pageCount) {
                    foreach($appointments as $appointment) {
                        $hairdresserName = Hairdresser::where('id', $appointment['hairdresser_id'])->pluck('name');
                        $serviceName = HairdresserService::where('id', $appointment['hairdresser_service_id'])->pluck('name');
                        $userName = User::where('id', $appointment['user_id'])->pluck('name');

                        $formatedDatetime = explode(' ', $appointment['ap_datetime']);
                        $apDate = $formatedDatetime[0];
                        $apDate = explode('-', $apDate);
                        $formatedApDate = $apDate[2].'/'.$apDate[1].'/'.$apDate[0];

                        $apTime = $formatedDatetime[1];
                        $apTime = explode(':', $apTime);
                        $formatedApTime = $apTime[0].':'.$apTime[1];

                        $appointment = [
                            'id' => $appointment['id'],
                            'ap_date' => $formatedApDate,
                            'ap_time' => $formatedApTime,
                            'user' => $userName[0],
                            'hairdresser' => $hairdresserName[0],
                            'service' => $serviceName[0],
                        ];

                        $apList[] = $appointment;
                    }
                    return view('appointments_done', [
                        'appointments' => $apList,
                        'page' => $page,
                        'items' => $fullAppointments,
                    ]);
                }
            }
        }

        return redirect()->back();
    }
}

  
   