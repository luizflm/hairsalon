<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Hairdresser;
use App\Models\HairdresserDoneService;
use App\Models\HairdresserService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DoneServiceController extends Controller
{
    public function getComission(Request $request) {
        $page = $request->page ?? 1;
        $currentDate = date('Y-m'); // pegando o ano e mês do dia atual
        // verificando: se foi enviado alguma data pela url, usa a data da url, senão, usa a data atual
        $date = $request->date ?? $currentDate;

        $servicesCount = 0;

        if($page != 0) { // verificando se a página enviada não é 0
            // pegando os hairdressers de forma crescente pelo id
            $hairdressers = Hairdresser::orderBy('id', 'ASC')->get();
            
            foreach($hairdressers as $hairdresser) { // pra cada hairdresser
                // pega serviços do hairdresser no ano/mes desejado
                $doneServices = HairdresserDoneService::where('hairdresser_id', $hairdresser['id'])
                ->where('service_datetime', 'LIKE', '%'.$date.'%')
                ->paginate(4);
                // pega a quantidade de serviços feitos
                $servicesCount += count($doneServices);  
                if($doneServices->items()) { // verifica se algum item foi encontrado na query de $doneServices
                    // cria uma variavel com valor inicial 0 para ser incrementada a cada serviço feito do hairdresser na data desejada
                    $fullMoney = 0;
                    foreach($doneServices as $doneService) { // pra cada serviço feito
                        // pego o preço do serviço
                        $price = HairdresserService::where('id', $doneService['hairdresser_service_id'])->pluck('price');
                        // incremento o preço na váriavel
                        $fullMoney += $price[0];
                    }
    
                    $comission = $fullMoney * 0.06; // fazendo a porcentagem (6%)
                    $comission = number_format($comission, 2, '.'); // arredondando a porcentagem
                    // formatando a string de "comissão" do hairdresser
                    $hairdresser['comission'] = 'R$ '.$comission; 
                    // colocando a quantidade de serviços feitos na string de "done_services" do hairdresser
                    $hairdresser['done_services'] = $servicesCount;
    
                    // coloca o hairdresser em um array de lista
                    $list['hairdresser'][] = $hairdresser;
                }            
            }
            // pega a quantidade de páginas disponíveis, sendo 4 registros mostrados por página
            $pageCount = ceil($servicesCount / 4);
            // verifica se a página enviada é menor ou igual ao número de páginas disponíveis
            if($page <= $pageCount) { 
            // após todo o processamento, renderiza a view com os dados necessários (fora do foreach)
                return view('comission', [
                    'page' => $page,
                    'date' => $date,
                    'items' => $servicesCount,
                    'list' => $list
                ]);
            }
        }
        // caso algo dê erro, retorna para a página anterior
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
   
            // buscando appointment com o id do hairdresser e o id do appointment
            $doneAppointment = Appointment::where('id', $appointmentId)
            ->where('hairdresser_id', $hairdresserId)
            ->first();
            if($doneAppointment) { // se o appointment foi encontrado
                $wasDone = $doneAppointment['was_done']; 
                // verificando se já foi colocado como concluido antes
                if($wasDone === 0) { // se não
                    // pega o id do serviço
                    $serviceId = $appointment['hairdresser_service_id'];

                    // pega a data 
                    $apDate = $appointment['ap_date'];
                    // transforma a string em um array, separando por barras ex(20/04/2023)
                    $formatedApDate = explode('/', $apDate);
                    // formata o array criando uma string nova (2023-04-20)
                    $formatedApDate = $formatedApDate[2].'-'.$formatedApDate[1].'-'.$formatedApDate[0];
        
                    // pega o horário
                    $apTime = $appointment['ap_time'];
                    // concatena com ":00" pra ficar ex: (09:00:00)
                    $formatedApTime = $apTime.":00";

                    // cria uma nova string formatada com a data e o horário, ex: (2023-04-20 09:00:00) 
                    $apDatetime = $formatedApDate.' '.$formatedApTime;

                    // verificando se o usuario esta finalizando um agendamento do futuro
                    $carbonApDatetime = Carbon::createFromFormat('Y-m-d H:i:s', $apDatetime);
                    $now = date('Y-m-d H:i:s');
                    $isDateFuture = $carbonApDatetime->greaterThan($now);
                    if($isDateFuture === false) { // se não
                        $doneAppointment->update([ // marca o appointment como feito
                            'was_done' => 1,
                        ]);
                        // cria um registro de serviço feito
                        HairdresserDoneService::create([
                            'service_datetime' => $apDatetime,
                            'hairdresser_id' => $hairdresserId,
                            'hairdresser_service_id' => $serviceId,
                        ]);
                        // redireciona para a tela de agendamentos concluídos
                        return redirect()->route('appointments_done', ['page' => 1]);
                    }
                }
            }
        }
        // caso algo dê erro, retorna para a página anterior
        return back();
    }
}
