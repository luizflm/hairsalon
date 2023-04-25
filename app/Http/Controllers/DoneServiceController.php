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

    public function getComission(Request $request) {
        $page = $request->page;
        $currentDate = date('Y-m');
        $date = $request->date ?? $currentDate;

        if($page != 0) {
            $hairdressers = Hairdresser::orderBy('id', 'ASC')->get();
            
            foreach($hairdressers as $hairdresser) {
                // serviços do hairdresser no ano/mes desejado
                $doneServices = HairdresserDoneService::where('hairdresser_id', $hairdresser['id'])
                ->where('service_datetime', 'LIKE', '%'.$date.'%')
                ->paginate(4);
                
                $pageCount = ceil(count($doneServices) / 4);
                if($page <= $pageCount) {
                    if($doneServices->items()) {
                        $fullMoney = 0;
                        foreach($doneServices as $doneService) {
                            // pra cada serviço realizado, pego o preço do serviço e incremento
                            $price = HairdresserService::where('id', $doneService['hairdresser_service_id'])->pluck('price');
                            $fullMoney += $price[0];
                        }
        
                        $comission = $fullMoney * 0.06; // fazendo a porcentagem (6%)
                        $comission = number_format($comission, 2, '.'); // arredondando a porcentagem
                        $hairdresser['comission'] = 'R$ '.$comission;
                        $hairdresser['done_services'] = count($doneServices);
        
                        $list['hairdresser'][] = $hairdresser;

                        return view('comission', [
                            'page' => $page,
                            'date' => $date,
                            'items' => 1,
                            'list' => $list
                        ]);
                    }
                }                
            }
        }
        
        return back();
    }

    public function insertAction(Request $request) {
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
