<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\EditServiceRequest;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Models\Hairdresser;
use App\Models\HairdresserService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function getHairdresserAllAjax($id) 
    {
        $services = HairdresserService::where('hairdresser_id', $id)->get();

        return $services;
    }

    public function index(Request $request)
    {
        $page = $request->page;
        $fullServices = HairdresserService::all()->count(); 
        $pageCount = ceil($fullServices / 4);
 
        $services = HairdresserService::orderBy('price', 'DESC')
        ->orderBy('id', 'ASC')
        ->with(['hairdresser'])
        ->paginate(4);
        if($services->items()) {
            if($page != 0) {
                if($page <= $pageCount) { 
                    foreach($services as $service) {
                        $hairdresser = $service->hairdresser->name;
                        $service = [
                            'id' => $service->id,
                            'name' => $service->name,
                            'price' => $service->price,
                            'hairdresser' => $hairdresser,
                        ];
                        $servicesList[] = $service;
                    }
        
                    return view('admin.services.index', [
                        'services' => $servicesList,
                        'page' => $page,
                        'items' => $fullServices
                    ]);
                }
            }
        }

        return back();
    }

    public function create()
    {
        $hairdressers = Hairdresser::all();

        return view('admin.services.create', [
            'hairdressers' => $hairdressers 
        ]);
    }

    public function store(StoreServiceRequest $request)
    {
        $hairdresserId = $request->hairdresser_id;
        $price = $request->price;

        $name = $request->name;
        $name = trim($name," ");

        $hairdresser = Hairdresser::find($hairdresserId);
        if($hairdresser) {
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

    public function edit(HairdresserService $service)
    {
        $hairdressers = Hairdresser::all();

        return view('admin.services.edit', ['service' => $service, 'hairdressers' => $hairdressers]);
        
    }

    public function update(EditServiceRequest $request, HairdresserService $service)
    {
        $hairdresserId = $request->hairdresser_id;
        $price = $request->price;

        $name = $request->name;
        $name = trim($name," ");

        $hairdresser = Hairdresser::find($hairdresserId);
        if($hairdresser) {
            if($service) {
                if($service['hairdresser_id'] == $hairdresserId) {
                    if($service['name'] == $name) {
                        $service->update([
                            'hairdresser_id' => $hairdresserId,
                            'name' => $name,
                            'price' => $price,
                        ]);
                    } else {
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
                } else {
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

        return redirect()->back()->withInput($request->input()); 
    }

    public function destroy(HairdresserService $service)
    {
        $service->delete();
        
        return redirect()->route('services.index', ['page' => 1]);
    }
}
