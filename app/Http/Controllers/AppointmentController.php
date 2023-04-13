<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Appointment;
use App\Models\Hairdresser;
use App\Models\HairdresserAvailability;
use App\Models\HairdresserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function getMyAppointments() {
        $array = ['error' => '', 'list' => []];

        $idUser = Auth::user()->id;

        $appointments = Appointment::where('id_user', $idUser)
        ->where('was_done', '0')
        ->get();
        foreach($appointments as $appointment) {
            $hairdresser = Hairdresser::find($appointment->id_hairdresser);
            $hairdresser->avatar = asset('storage/'.$hairdresser->avatar);

            $service = HairdresserService::find($appointment->id_service);

            $appointment = [
                'id' => $appointment->id,
                'hairdresser' => $hairdresser,
                'service' => $service,
                'ap_datetime' => $appointment->ap_datetime,
            ];

            $array['list'][] = $appointment;
        }

        return $array;
    }
    
    public function setAppointmentView() {
        if(!Auth::check()) {
            return redirect()->route('home');
        }
    
        return view('appointment', ['formTitle' => 'Agendamento']);
    }

    public function setAppointmentAction(Request $request) {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'id_hairdresser' => 'required',
            'service' => 'required',
            'ap_datetime' => 'required|date_format:Y-m-d H:i',
        ]);
        if(!$validator->fails()) {
            $idHairdresser = $request->id_hairdresser;
            $idUser = Auth::user()->id;
            $service = $request->service;
            $apDatetime = $request->ap_datetime;

            $hdExists = Hairdresser::find($idHairdresser);
            if($hdExists) { 
                $hdService = HairdresserService::where('id_hairdresser', $idHairdresser)
                ->where('name', $service)
                ->first();
                if($hdService) { 
                    $now = date('Y-m-d H:i');
                    $apDate = Carbon::createFromFormat('Y-m-d H:i', $apDatetime);
                    $isDateFuture = $apDate->greaterThan($now);
                    if($isDateFuture === true) { 
                        $hasAppointments = Appointment::where('ap_datetime', $apDatetime)
                        ->where('id_hairdresser', $idHairdresser)
                        ->count();
                        if($hasAppointments === 0) {
                            $formatedDate = explode(' ', $apDatetime);
                            $ap_weekday = date('w', strtotime($formatedDate[0]));

                            $hdAvailability = HairdresserAvailability::where('id_hairdresser', $idHairdresser)
                            ->get();
                            $hdWeekdays = [];
                            foreach($hdAvailability as $hdAvail) {
                                $hdWeekdays[] = $hdAvail->weekday;
                            }
                            if(in_array($ap_weekday, $hdWeekdays)) {
                                $isTimeAvail = HairdresserAvailability::select(['hours'])
                                ->where('id_hairdresser', $idHairdresser)
                                ->where('weekday', $ap_weekday)
                                ->first();
                                $availTimes = $isTimeAvail->hours;

                                $hdTimes = explode(', ', $availTimes);
                                array_pop($hdTimes);
                                if(in_array($formatedDate[1], $hdTimes)) {      
                                    $apDatetime .= ':00';            
                                    $newAppointment = Appointment::create([
                                        'id_hairdresser' => $idHairdresser,
                                        'id_user' => $idUser,
                                        'id_service' => $hdService->id,
                                        'ap_datetime' => $apDatetime,
                                    ]);

                                    $array['data'] = [
                                        'appointment' => $newAppointment,
                                        'service' => $hdService,
                                    ];
                                } else {
                                    $array['error'] = 'O(a) cabelereiro(a) não trabalha no horário desejado.';
                                    return $array;
                                }
                            } else {
                                $array['error'] = 'O(a) cabelereiro(a) não trabalha no dia desejado.';
                                return $array;
                            }
                        } else {
                            $array['error'] = 'Já há um agendamento para este dia/hora, tente em outro dia/horário.';
                            return $array;
                        }
                    } else {
                        $array['error'] = 'Data e/ou hora inválida.';
                        return $array;
                    }
                } else {
                    $array['error'] = 'O(a) cabelereiro não faz o serviço solicitado.';
                    return $array;
                }

            } else {
                $array['error'] = 'O(a) cabelereiro(a) não foi encontrado.';
                return $array;
            }
        } else {
            $array['error'] = $validator->messages()->first();
            return $array;
        }

        return $array;
    }

    public function delete($id) {
        $array = ['error' => ''];

        $appointment = Appointment::find($id);
        if($appointment) {
            $appointment->delete();
        } else {
            $array['error'] = 'Agendamento não encontrado.';
            return $array;
        }

        return $array;
    }

    public function update($id, Request $request) {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'id_hairdresser' => 'required',
            'service' => 'required',
            'ap_datetime' => 'required|date_format:Y-m-d H:i',
        ]);
        if(!$validator->fails()) {
            $idHairdresser = $request->id_hairdresser;
            $idUser = Auth::user()->id;
            $service = $request->service;
            $apDatetime = $request->ap_datetime;

            $appointment = Appointment::where('id_user', $idUser)
            ->where('id', $id)
            ->first();
            if($appointment) {
                $hdExists = Hairdresser::find($idHairdresser);
                if($hdExists) { 
                    $hdService = HairdresserService::where('id_hairdresser', $idHairdresser)
                    ->where('name', $service)
                    ->first();
                    if($hdService) { 
                        $now = date('Y-m-d H:i');
                        $apDate = Carbon::createFromFormat('Y-m-d H:i', $apDatetime);
                        $isDateFuture = $apDate->greaterThan($now);
                        if($isDateFuture === true) { 
                            $hasAppointments = Appointment::where('ap_datetime', $apDatetime)
                            ->where('id_hairdresser', $idHairdresser)
                            ->count();
                            if($hasAppointments === 0) {
                                $formatedDate = explode(' ', $apDatetime);
                                $ap_weekday = date('w', strtotime($formatedDate[0]));

                                $hdAvailability = HairdresserAvailability::where('id_hairdresser', $idHairdresser)
                                ->get();
                                $hdWeekdays = [];
                                foreach($hdAvailability as $hdAvail) {
                                    $hdWeekdays[] = $hdAvail->weekday;
                                }
                                if(in_array($ap_weekday, $hdWeekdays)) {
                                    $isTimeAvail = HairdresserAvailability::select(['hours'])
                                    ->where('id_hairdresser', $idHairdresser)
                                    ->where('weekday', $ap_weekday)
                                    ->first();
                                    $availTimes = $isTimeAvail->hours;
                                    
                                    $hdTimes = explode(', ', $availTimes);
                                    array_pop($hdTimes);
                                    if(in_array($formatedDate[1], $hdTimes)) {
                                        $apDatetime .= ':00';
                                        $appointment->update([
                                            'id_hairdresser' => $idHairdresser,
                                            'id_user' => $idUser,
                                            'id_service' => $hdService->id,
                                            'ap_datetime' => $apDatetime,
                                        ]);
    
                                        $array['data'] = [
                                            'updated_appointment' => $appointment,
                                            'service' => $hdService,
                                        ];
                                    } else {
                                        $array['error'] = 'O(a) cabelereiro(a) não trabalha no horário desejado.';
                                        return $array;
                                    }
                                } else {
                                    $array['error'] = 'O(a) cabelereiro(a) não trabalha no dia desejado.';
                                    return $array;
                                }
                            } else {
                                $array['error'] = 'Já há um agendamento para este dia/hora, tente em outro dia/horário.';
                                return $array;
                            }
                        } else {
                            $array['error'] = 'Data e/ou hora inválida.';
                            return $array;
                        }
                    } else {
                        $array['error'] = 'O(a) cabelereiro não faz o serviço solicitado.';
                        return $array;
                    }
    
                } else {
                    $array['error'] = 'O(a) cabelereiro(a) não foi encontrado.';
                    return $array;
                }
            } else {
                $array['error'] = 'O agendamento pertence à outra pessoa.';
                return $array;
            }
        } else {
            $array['error'] = $validator->messages()->first();
            return $array;
        }

        return $array;
    }
}

  
   