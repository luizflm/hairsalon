<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Hairdresser;
use App\Models\HairdresserDoneService;
use App\Models\HairdresserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DoneServiceController extends Controller
{
    public function getDoneServices(Request $request) {
        $array = ['error' => '', 'list' => []];

        $date = $request->date;
        $idHairdresser = $request->id_hairdresser;

        $doneServices = HairdresserDoneService::where('id_hairdresser', $idHairdresser)
        ->where('service_datetime','LIKE', $date.'%')
        ->get();
        foreach($doneServices as $doneService) {
            $hairdresser = Hairdresser::find($idHairdresser);
            $hairdresser->avatar = asset('storage/'.$hairdresser->avatar);

            $idService = $doneService->id_service;
            $service = HairdresserService::find($idService);

            $array['list'][] = [
                'id' => $doneService->id,
                'hairdresser' => $hairdresser,
                'service' => $service,
                'service_datetime' => $doneService->service_datetime,
            ];
        }

        return $array;
    }

    public function getOne($id) {
        $array = ['error' => ''];

        $doneService = HairdresserDoneService::find($id);
        if($doneService) {
            $hairdresser = Hairdresser::find($doneService->id_hairdresser);
            $service = HairdresserService::find($doneService->id_service);

            $array['data'] = [
                'id' => $doneService->id,
                'hairdresser' => $hairdresser,
                'service' => $service
            ];
        } else {
            $array['error'] = 'Não encontrado.';
            return $array;
        }

        return $array;
    }

    public function insert(Request $request) {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'id_appointment' => 'required'
        ]);
        if(!$validator->fails()) {
            $idAppointment = $request->id_appointment;
            $idUser = Auth::user()->id;

            $appointment = Appointment::where('id', $idAppointment)
            ->where('id_user', $idUser)
            ->first();
            if($appointment) {
                $isDone = $appointment->was_done;
                if($isDone === 0) {
                    $idHairdresser = $appointment->id_hairdresser;
                    $idService = $appointment->id_service;
                    $apDateTime = $appointment->ap_datetime;
    
                    $apDate = Carbon::createFromFormat('Y-m-d H:i:s', $apDateTime);
                    $now = date('Y-m-d H:i:s');
                    $isDateFuture = $apDate->greaterThan($now);
                    if($isDateFuture === false) { 
                        $newDoneService = HairdresserDoneService::create([
                            'id_hairdresser' => $idHairdresser,
                            'id_service' => $idService,
                            'service_datetime' => $apDateTime
                        ]);
    
                        $appointment->update([
                            'was_done' => '1',
                        ]);
    
                        $array['data'] = $newDoneService;
                    } else {
                        $array['error'] = 'O serviço ainda não foi realizado.';
                        return $array;
                    }
                } else {
                    $array['error'] = 'O agendamento já foi marcado como concluído.';
                    return $array;
                }
            } else {
                $array['error'] = 'Agendamento não encontrado.';
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

        $doneService = HairdresserDoneService::find($id);
        if($doneService) {
            $doneService->delete();
        } else {
            $array['error'] = 'Não encontrado.';
            return $array;
        }

        return $array;
    }
}
