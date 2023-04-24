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
        $validator = $request->validate([
            'appointment' => 'required',
        ]);
        if($validator) {
            $appointment = $request->appointment;

            $appointmentId = $appointment['id'];
            $hairdresserId = $appointment['hairdresser_id'];
   
            $doneAppointment = Appointment::where('id', $appointmentId)
            ->where('hairdresser_id', $hairdresserId)
            ->first();
            if($doneAppointment) {
                $wasDone = $doneAppointment['was_done'];
                // verificando se já foi colocado como concluido antes
                if($wasDone === 0) {
                    $serviceId = $appointment['hairdresser_service_id'];

                    $apDate = $appointment['ap_date'];
                    $formatedApDate = explode('/', $apDate);
                    $formatedApDate = $formatedApDate[2].'-'.$formatedApDate[1].'-'.$formatedApDate[0];
        
                    $apTime = $appointment['ap_time'];
                    $formatedApTime = $apTime.":00";
                    $apDatetime = $formatedApDate.' '.$formatedApTime;

                    // verificando se o usuario esta finalizando um agendamento do futuro
                    $carbonApDatetime = Carbon::createFromFormat('Y-m-d H:i:s', $apDatetime);
                    $now = date('Y-m-d H:i:s');
                    $isDateFuture = $carbonApDatetime->greaterThan($now);
                    if($isDateFuture === false) {
                        $doneAppointment->update([
                            'was_done' => 1,
                        ]);

                        HairdresserDoneService::create([
                            'service_datetime' => $apDatetime,
                            'hairdresser_id' => $hairdresserId,
                            'hairdresser_service_id' => $serviceId,
                        ]);
                    }
                }
            }
        }
        
        return back();
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
