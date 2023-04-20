<?php

namespace App\Http\Controllers;

use App\Models\Hairdresser;
use App\Models\HairdresserAvailability;
use App\Models\HairdresserEvaluation;
use App\Models\HairdresserService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HairdresserController extends Controller
{
    public function insertView() {
        return view('insert_hairdresser');
    }

    public function insert(Request $request) {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2',
            'avatar' => 'required|file|mimes:jpg,png',
            'specialties' => 'required',
        ]);
        if(!$validator->fails()) {
            $avatar = $request->file('avatar')->store('public');
            $avatar = last(explode('/', $avatar));

            $name = $request->name;
            $name = trim($name," ");
            $name = explode(' ', $name);
            if(count($name) > 1) {
                $formatedName = $name[0].' '.last($name);
            } else {
                $formatedName = $name[0];
            }

            $specialties = $request->specialties;
            $specialties = explode(',', $specialties);
            $formatedSp = '';
            foreach($specialties as $spKey => $spValue) {
                $specialties[$spKey] = trim($specialties[$spKey]," ");
                $formatedSp .= $specialties[$spKey].', ';
            }
            $formatedSp = substr($formatedSp, 0, strlen($formatedSp) - 2);

            $newHairdresser = Hairdresser::create([
                'name' => $formatedName,
                'avatar' => $avatar,
                'specialties' => $formatedSp,
            ]);

            $avatar = asset('storage/'.$avatar);

            $array['data'] = [
                'id' => $newHairdresser->id,
                'name' => $formatedName,
                'avatar' => $avatar,
                'specialties' => $specialties,
            ];
        } else {
            $array['error'] = $validator->messages()->first();
            return $array;
        }

        return $array;
    }

    public function update($id, Request $request) {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2',
            'avatar' => 'required|file|mimes:jpg,png',
            'specialties' => 'required',
        ]);
        if(!$validator->fails()) {
            $hairdresser = Hairdresser::find($id);

            $avatar = $request->file('avatar')->store('public');
            $avatar = last(explode('/', $avatar));

            $name = $request->name;
            $name = trim($name," ");
            $name = explode(' ', $name);
            if(count($name) > 1) {
                $formatedName = $name[0].' '.last($name);
            } else {
                $formatedName = $name[0];
            }

            $specialties = $request->specialties;
            $specialties = explode(', ', $specialties);
            $formatedSp = '';
            foreach($specialties as $spKey => $spValue) {
                $specialties[$spKey] = trim($specialties[$spKey]," ");
                $formatedSp .= $specialties[$spKey].', ';
            }
            $formatedSp = substr($formatedSp, 0, strlen($formatedSp) - 2);

            $hairdresser->update([
                'name' => $formatedName,
                'avatar' => $avatar,
                'specialties' => $formatedSp,
            ]);

            $avatar = asset('storage/'.$avatar);

            $array['data'] = [
                'id' => $hairdresser->id,
                'name' => $formatedName,
                'avatar' => $avatar,
                'specialties' => $specialties,
            ];
        } else {
            $array['error'] = $validator->messages()->first();
            return $array;
        }

        return $array;
    }

    public function delete($id) {
        $array = ['error' => ''];

        $hairdresser = Hairdresser::find($id);
        if($hairdresser) {
            $hairdresser->delete();
        } else {
            $array['error'] = 'Cabelereiro(a) não encontrado(a).';
            return $array;
        }

        return $array;
    }

    public function getInfo($id) {
        $array = ['error' => ''];

        $hairdresser = Hairdresser::find($id);
        if($hairdresser) {
            $avatar = asset('storage/'.$hairdresser->avatar);

            $specialties = $hairdresser->specialties;
            $specialties = explode(',', $specialties);
            foreach($specialties as $spKey => $spValue) {
                $specialties[$spKey] = trim($specialties[$spKey]," ");
            }
           
            $availability = HairdresserAvailability::where('id_hairdresser', $id)->get();
            foreach($availability as $avail) {
                $weekday = $avail->weekday;
                $days = [
                    1 => 'Segunda-Feira',
                    2 => 'Terça-Feira',
                    3 => 'Quarta-Feira',
                    4 => 'Quinta-Feira',
                    5 => 'Sexta-Feira',
                    6 => 'Sábado',
                    7 => 'Domingo'
                ];

                $hours = explode(', ', $avail->hours);

                $hdAvail[] = [
                    'weekday' => $days[$weekday],
                    'hours' => $hours
                ];
            }

            // services
            $services = HairdresserService::where('id_hairdresser', $id)->get();
            $hdServices = [];
            foreach($services as $service) {
                $hdServices[] = [
                    'name' => $service->name,
                    'price' => $service->price,
                ];
            }

            // evaluations
            $evaluations = HairdresserEvaluation::where('id_hairdresser', $id)
            ->orderBy('stars', 'DESC')
            ->get();
            $hdEvaluations = [];
            foreach($evaluations as $evaluation) {
                $evaluationOwner = User::find($evaluation->id_user);

                $hdEvaluations[] = [
                    'evaluation_owner' => $evaluationOwner,
                    'stars' => $evaluation->stars,
                    'comment' => $evaluation->comment,
                ];
            }
            
            $array['data'] = [
                'id' => $id,
                'name' => $hairdresser->name,
                'avatar' => $avatar,
                'specialties' => $specialties,
                'availability' => $hdAvail,
                'services' => $hdServices,
                'hd_evaluations' => $hdEvaluations,
            ];
        } else {
            $array['error'] = 'Cabelereiro(a) não encontrado(a).';
            return $array;
        }

        return $array;
    }

    public function getAll() {
        $array = ['error' => '', 'list' => []];

        $hairdressers = Hairdresser::all();
        foreach($hairdressers as $hairdresser) {
            $avatar = asset('storage/'.$hairdresser->avatar);

            $specialties = $hairdresser->specialties;
            $specialties = explode(',', $specialties);
            foreach($specialties as $spKey => $spValue) {
                $specialties[$spKey] = trim($specialties[$spKey]," ");
            }

            $availability = HairdresserAvailability::where('id_hairdresser', $hairdresser->id)->get();
            $hdAvail = [];
            foreach($availability as $avail) {
                $weekday = $avail->weekday;
                $days = [
                    1 => 'Segunda-Feira',
                    2 => 'Terça-Feira',
                    3 => 'Quarta-Feira',
                    4 => 'Quinta-Feira',
                    5 => 'Sexta-Feira',
                    6 => 'Sábado',
                    7 => 'Domingo'
                ];

                $hours = explode(', ', $avail->hours);

                $hdAvail[] = [
                    'weekday' => $days[$weekday],
                    'hours' => $hours,
                ];
            }

            // services
            $services = HairdresserService::where('id_hairdresser', $hairdresser->id)->get();
            $hdServices = [];
            foreach($services as $service) {
                $hdServices[] = [
                    'name' => $service->name,
                    'price' => $service->price,
                ];
            }

            $array['list'][] = [
                'id' => $hairdresser->id,
                'name' => $hairdresser->name,
                'avatar' => $avatar,
                'specialties' => $specialties,
                'availability' => $hdAvail,
                'services' => $hdServices,
            ];
        }

        return $array;
    }
}
