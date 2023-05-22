<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreDoneServiceRequest;
use App\Models\Appointment;
use App\Models\Hairdresser;
use App\Models\HairdresserDoneService;
use App\Models\HairdresserService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DoneServiceController extends Controller
{
    public function index(Request $request) 
    {
        $page = $request->page ?? 1;
        $currentDate = date('Y-m');
        $date = $request->date ?? $currentDate;

        $servicesCount = 0;

        if($page != 0) {
            $hairdressers = Hairdresser::orderBy('id', 'ASC')->get();
            foreach($hairdressers as $hairdresser) {
                $doneServices = HairdresserDoneService::where('hairdresser_id', $hairdresser['id'])
                ->where('service_datetime', 'LIKE', '%'.$date.'%')
                ->paginate(4);
                $servicesCount += count($doneServices);  
                if($doneServices->items()) {
                    $fullMoney = 0;
                    foreach($doneServices as $doneService) {
                        $price = HairdresserService::where('id', $doneService['hairdresser_service_id'])->pluck('price');
                        $fullMoney += $price[0];
                    }

                    $comission = $fullMoney * 0.06;
                    $comission = number_format($comission, 2, '.');
                    $hairdresser['comission'] = 'R$ '.$comission; 
                    $hairdresser['done_services'] = $servicesCount;

                    $list['hairdresser'][] = $hairdresser;
                }            
            }

            $pageCount = ceil($servicesCount / 4);
            if($page <= $pageCount) { 
                return view('admin.pages.comission', [
                    'page' => $page,
                    'date' => $date,
                    'items' => $servicesCount,
                    'list' => $list
                ]);
            }
        }

        return back();
    }

    public function store(StoreDoneServiceRequest $request) 
    {
        $appointment = $request->appointment;

        $appointmentId = $appointment['id'];
        $hairdresserId = $appointment['hairdresser_id'];

        $doneAppointment = Appointment::where('id', $appointmentId)
        ->where('hairdresser_id', $hairdresserId)
        ->first();
        if($doneAppointment) {
            $wasDone = $doneAppointment['was_done']; 
            if($wasDone === 0) {
                $serviceId = $appointment['hairdresser_service_id'];

                $apDate = $appointment['ap_date'];
                $formatedApDate = explode('/', $apDate);
                $formatedApDate = $formatedApDate[2].'-'.$formatedApDate[1].'-'.$formatedApDate[0];
    
                $apTime = $appointment['ap_time'];
                $formatedApTime = $apTime.":00";
                $apDatetime = $formatedApDate.' '.$formatedApTime;

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

                    return redirect()->route('appointments.done', ['page' => 1]);
                }
            }
        }

        return back();
    }
}
