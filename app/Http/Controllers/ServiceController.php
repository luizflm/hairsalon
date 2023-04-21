<?php

namespace App\Http\Controllers;

use App\Models\Hairdresser;
use App\Models\HairdresserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function insertView() {
        $hairdressers = Hairdresser::all();

        return view('service', ['hairdressers' => $hairdressers]);
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

    public function getAll() {
        $array = ['error' => '', 'list' => []];

        $services = HairdresserService::orderBy('price', 'DESC')
        ->orderBy('id', 'ASC')
        ->get();
        foreach($services as $service) {
            $hairdresser = Hairdresser::find($service->id_hairdresser);
            $hairdresser->avatar = asset('storage/'.$hairdresser->avatar);

            $service = [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'hairdresser' => $hairdresser,
            ];

            $array['list'][] = $service;
        }

        return $array;
    }

    public function getOne($id) {
        $array = ['error' => ''];

        $service = HairdresserService::find($id);
        if($service) {
            $hairdresser = Hairdresser::find($service->id_hairdresser);
            $hairdresser->avatar = asset('storage/'.$hairdresser->avatar);
    
            $array['data'] = [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'hairdresser' => $hairdresser,
            ];
        } else {
            $array['error'] = 'Não encontrado.';
            return $array;
        }
   
        return $array;
    }

    

    public function update($id, Request $request) {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'id_hairdresser' => 'required',
            'name' => 'required|min:2',
            'price' => 'required',
        ]);
        if(!$validator->fails()) {
            $idHairdresser = $request->id_hairdresser;
            $price = $request->price;

            $name = $request->name;
            $name = trim($name," ");

            $hairdresser = Hairdresser::find($idHairdresser);
            if($hairdresser) {
                $service = HairdresserService::find($id);
                if($service) {
                    $service->update([
                        'id_hairdresser' => $idHairdresser,
                        'name' => $name,
                        'price' => $price,
                    ]);

                    $array['data'] = $service;
                } else {
                    $array['error'] = 'Serviço não encontrado.';
                    return $array;
                }
            } else {
                $array['error'] = 'Cabelereiro(a) não encontrado(a).';
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

        $service = HairdresserService::find($id);
        if($service) {
            $service->delete();
        } else {
            $array['error'] = 'Serviço não encontrado.';
            return $array;
        }

        return $array;
    }
}
