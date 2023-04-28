<?php

namespace App\Http\Controllers;

use App\Models\Hairdresser;
use App\Models\HairdresserService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function getHairdresserAll(Request $request) {
        $page = $request->page;
        // pegando todos os serviços existentes
        $fullServices = HairdresserService::all()->count(); 
        // pegando o numero de paginas disponiveis, se forem mostrados 4 registros por página
        $pageCount = ceil($fullServices / 4);
 
        // pegando os serviços, ordenandos de forma decrescente por preço, e de forma crescente por id
        $services = HairdresserService::orderBy('price', 'DESC')
        ->orderBy('id', 'ASC')
        ->paginate(4);
        if($services->items()) { // verificando se tem algum serviço 
            if($page != 0) { // verificando se a página desejada não é 0
                // verificando se a página enviada é menor ou igual ao número de páginas disponiveis
                if($page <= $pageCount) { 
                    foreach($services as $service) { 
                        // pra cada serviço, encontrar o hairdresser "dono"
                        $hairdresser = Hairdresser::find($service->hairdresser_id);
                        // montar o serviço com os dados necessários
                        $service = [
                            'id' => $service->id,
                            'name' => $service->name,
                            'price' => $service->price,
                            'hairdresser' => $hairdresser,
                        ];
                        // colocar o serviço em um array, que será enviado pra view
                        $servicesList[] = $service;
                    }
        
                    return view('services', [
                        'services' => $servicesList,
                        'page' => $page,
                        'items' => $fullServices
                    ]);
                }
            }
        }

        return back(); // caso algo dê errado, retorna pra página anterior
    }

    public function insertView() {
        $hairdressers = Hairdresser::all();

        return view('insert_service', ['hairdressers' => $hairdressers]);
    }

    public function insertAction(Request $request) {
        $validator = $request->validate([
            'name' => 'required|min:2',
            'price' => 'required',
            'hairdresser_id' => 'required',
        ]);
        if($validator) {
            $hairdresserId = $request->hairdresser_id;
            $price = $request->price;

            $name = $request->name;
            $name = trim($name," ");

            $hairdresser = Hairdresser::find($hairdresserId);
            // vendo se o hairdresser existe
            if($hairdresser) {
                // vendo se o hairdresser já tem aquele serviço cadastrado
                $hasService = HairdresserService::where('hairdresser_id', $hairdresserId)
                ->where('name', $name)
                ->first();
                if(!$hasService) {
                    HairdresserService::create([
                        'hairdresser_id' => $hairdresserId,
                        'name' => $name,
                        'price' => $price
                    ]);

                    return redirect()->back();
                } else {
                    return redirect()->back()->withErrors([
                        'name' => 'O(a) cabelereiro(a) já tem esse serviço cadastrado!',
                    ])->withInput($request->all());
                }
            } else {
                return redirect()->back()->withErrors([
                    'name' => 'O(a) cabelereiro(a) não foi encontrado.',
                ])->withInput($request->all());
            }
        }
    }

    public function updateView($id) {
        $service = HairdresserService::find($id);
        $hairdressers = Hairdresser::all();

        if($service) {
            return view('edit_service', ['service' => $service, 'hairdressers' => $hairdressers]);
        }

        return redirect()->back();
    }

    public function updateAction($id, Request $request) {
        $validator = $request->validate([
            'name' => 'required|min:2',
            'price' => 'required',
            'hairdresser_id' => 'required'
        ]);
        if($validator) {
            $hairdresserId = $request->hairdresser_id;
            $price = $request->price;

            $name = $request->name;
            $name = trim($name," ");

            $hairdresser = Hairdresser::find($hairdresserId);
            // vendo se o hairdresser existe
            if($hairdresser) {
                // vendo se o serviço existe
                $service = HairdresserService::find($id);
                if($service) {
                    // se o hairdresser_id enviado for o mesmo (não alterou o hairdresser)
                    if($service['hairdresser_id'] == $hairdresserId) {
                        // se o nome enviado for o mesmo (não alterou o nome do serviço)
                        if($service['name'] == $name) {
                            $service->update([
                                'hairdresser_id' => $hairdresserId,
                                'name' => $name,
                                'price' => $price,
                            ]);
                        } else { // nome trocou
                            // ver se o hairdresser ja tem um serviço com o nome enviado (nome trocado)
                            $hdServiceNameExists = HairdresserService::where('name', $name)
                            ->where('hairdresser_id', $hairdresserId)
                            ->first();
                            if(!$hdServiceNameExists) {
                                $service->update([
                                    'hairdresser_id' => $hairdresserId,
                                    'name' => $name,
                                    'price' => $price,
                                ]);
                            } else {
                                return redirect()->back()->withErrors([
                                    'name' => 'O(a) cabelereiro(a) já tem esse serviço.'
                                ])->withInput($request->input());
                            }
                        }
                    } else { // trocou o hairdresser do serviço
                        // vendo se o hairdresser novo já tem um serviço com aquele nome
                        $hdServiceExists = HairdresserService::where('name', $name)
                        ->where('hairdresser_id', $hairdresserId)
                        ->first();
                        if(!$hdServiceExists) {
                            $service->update([
                                'hairdresser_id' => $hairdresserId,
                                'name' => $name,
                                'price' => $price,
                            ]);
                        } else {
                            return redirect()->back()->withErrors([
                                'name' => 'O(a) cabelereiro(a) já tem esse serviço.'
                            ])->withInput($request->input());
                        }
                    }
                } else {
                    return redirect()->back()->withErrors([
                        'name' => 'O serviço não foi encontrado.'
                    ])->withInput($request->input());
                }
            } else {
                return redirect()->back()->withErrors([
                    'name' => 'O(a) cabelereiro(a) não foi encontrado.'
                ])->withInput($request->input());
            }
        }

        return redirect()->back()->withInput($request->input());
    }


    public function delete($id) {
        $service = HairdresserService::find($id);
        if($service) {
            $service->delete();
        }
        
        return redirect()->route('services', ['page' => 1]);
    }
}
