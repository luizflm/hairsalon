<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
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
    public function index()
    {
        $idUser = Auth::id();

        $appointments = Appointment::where('user_id', $idUser) 
        ->where('was_done', '0')
        ->get();
        $data = [];
        foreach($appointments as $appointment) {
            $hairdresser = Hairdresser::find($appointment->hairdresser_id);
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

            $data[] = $appointment;
        }

        if(count($data) != 0) {
            return view('appointments.index', [
                'appointments' => $data,
            ]);
        }

        return redirect()->back();
    }

    public function create()
    { 
        $hairdressers = Hairdresser::all();

        $services = HairdresserService::all();
        foreach($services as $service) {
            $price = str_replace(".", ",", $service['price']);
            $service['name'] = $service['name'].' - R$ '.$price;
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

        return view('appointments.create', [
            'times' => $times,
            'hairdressers' => $hairdressers,
            'services' => $services
        ]);
    }

    public function store(StoreAppointmentRequest $request)
    {
        if($request->validated()) {
            $apDay = $request->ap_day;
            $apTime = $request->ap_time;
            $apDatetime = $apDay.' '.$apTime;

            $hairdresserId = $request->hairdresser_id;
            $serviceId = $request->service_id;
            $userId = Auth::id();

            $hdExists = Hairdresser::find($hairdresserId);
            if($hdExists) {
                $hdService = HairdresserService::where('hairdresser_id', $hairdresserId)
                ->where('id', $serviceId)
                ->first();
                if($hdService) {
                    $now = date('Y-m-d H:i');
                    $apDate = Carbon::createFromFormat('Y-m-d H:i', $apDatetime);
                    $isDateFuture = $apDate->greaterThan($now);
                    if($isDateFuture) {
                        $hasAppointments = Appointment::where('ap_datetime', $apDatetime)
                        ->where('hairdresser_id', $hairdresserId)
                        ->count();
                        if($hasAppointments == 0) {
                            $formatedDate = explode(' ', $apDatetime);
                            $ap_weekday = date('w', strtotime($formatedDate[0]));

                            $hdAvailability = HairdresserAvailability::where('hairdresser_id', $hairdresserId)
                            ->get();
                            $hdWeekdays = [];
                            foreach($hdAvailability as $hdAvail) {
                                $hdWeekdays[] = $hdAvail->weekday;
                            }
                            if(in_array($ap_weekday, $hdWeekdays)) {
                                $isTimeAvail = HairdresserAvailability::select(['hours'])
                                ->where('hairdresser_id', $hairdresserId)
                                ->where('weekday', $ap_weekday)
                                ->first();
                                $availTimes = $isTimeAvail->hours;

                                $hdTimes = explode(', ', $availTimes);
                                array_pop($hdTimes); 
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

    public function edit($id)
    {
        $hairdressers = Hairdresser::all();

        $services = HairdresserService::all();
        foreach($services as $service) {
            $price = str_replace(".", ",", $service['price']);
            $service['name'] = $service['name'].' - R$ '.$price;
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

        $loggedUserId = Auth::id();

        $appointment = Appointment::find($id);

        if($appointment->user_id == $loggedUserId) {
            $formatedApDatetime = explode(' ', $appointment->ap_datetime);
            $apDate = $formatedApDatetime[0];

   
            $appointment['day'] = $apDate;
            $appointment['time'] = $formatedApDatetime[1];

            $service = HairdresserService::find($appointment->hairdresser_service_id);
            $servicePrice = str_replace('.', ',', $service['price']);
            $service['name'] = $service['name'].' - R$ '.$servicePrice;
            $appointment['service'] = $service['name'];

            return view('appointments.edit', [
                'appointment' => $appointment,
                'times' => $times,
                'services' => $services,
                'hairdressers' => $hairdressers,
            ]);
        }

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
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
            $userId = Auth::id();

            $appointment = Appointment::where('user_id', $userId)
            ->where('id', $id)
            ->first();
            if($appointment) {
                $hdExists = Hairdresser::find($hairdresserId);
                if($hdExists) {
                    $hdService = HairdresserService::where('hairdresser_id', $hairdresserId)
                    ->where('id', $serviceId)
                    ->first();
                    if($hdService) {
                        $now = date('Y-m-d H:i');
                        $apDate = Carbon::createFromFormat('Y-m-d H:i', $apDatetime);
                        $isDateFuture = $apDate->greaterThan($now);
                        if($isDateFuture) {
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
                                if(in_array($ap_weekday, $hdWeekdays)) {
                                    $isTimeAvail = HairdresserAvailability::select(['hours'])
                                    ->where('hairdresser_id', $hairdresserId)
                                    ->where('weekday', $ap_weekday)
                                    ->first();
                                    $availTimes = $isTimeAvail->hours;

                                    $hdTimes = explode(', ', $availTimes);
                                    array_pop($hdTimes);
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

    public function destroy($id)
    {
        $appointment = Appointment::find($id);
        $loggedUserId = Auth::id();
        if($appointment && $appointment->user_id == $loggedUserId) {
            $appointment->delete();
        }

        return redirect()->route('home');
    }

    public function getAllUndone(Request $request) 
    {
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

                    return view('admin.appointments.undone', [
                        'appointments' => $apList,
                        'page' => $page,
                        'items' => $fullAppointments,
                    ]);
                }
            }
        }

        return redirect()->back();
    }

    public function getAllDone(Request $request) 
    {
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
    
                    return view('admin.appointments.done', [
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
