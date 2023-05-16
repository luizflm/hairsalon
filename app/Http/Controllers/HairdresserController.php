<?php

namespace App\Http\Controllers;

use App\Models\Hairdresser;
use App\Models\HairdresserAvailability;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HairdresserController extends Controller
{
    public function index(Request $request)
    {
        $hairdressersCount = Hairdresser::all()->count();

        $page = $request->page;
        $pageCount = ceil($hairdressersCount / 4); 

        $hairdressers = Hairdresser::orderBy('id', 'ASC')->paginate(4); 
        if($hairdressers->items()) {
            if($page != 0) {
                if($page <= $pageCount) {
                    return view('admin.hairdressers.index', [
                        'hairdressers' => $hairdressers,
                        'page' => $page,
                        'items' => $hairdressersCount
                    ]);
                }
            }
        }

        return redirect()->back();
    }

    public function create()
    {
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

        $days = [
          'Domingo',
          'Segunda',
          'Terça',
          'Quarta',
          'Quinta',
          'Sexta',
          'Sábado',  
        ];

        return view('admin.hairdressers.create', [
            'times' => $times,
            'days' => $days
        ]);
    }

    public function store(Request $request)
    {
        $validator = $request->validate([
            'name' => 'required|min:2',
            'specialties' => 'required',
            'days' => 'required',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'avatar' => 'required|file|mimes:jpg,png',
        ]);
        if($validator) {
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
            $formatedSpecialty = '';
            foreach($specialties as $spKey => $spValue) {
                $specialties[$spKey] = trim($specialties[$spKey]," "); 

                $formatedSpecialty .= $specialties[$spKey].', '; 
            }
            $formatedSpecialty = substr($formatedSpecialty, 0, strlen($formatedSpecialty) - 2); 

            $startTime = $request->start_time;
            $endTime = $request->end_time;
            $carbonStartTime = Carbon::createFromFormat('H:i', $startTime); 
            $carbonEndTime = Carbon::createFromFormat('H:i', $endTime);
            if($carbonEndTime->greaterThan($carbonStartTime)) {
                $newHairdresser = Hairdresser::create([
                    'name' => $formatedName,
                    'avatar' => $avatar,
                    'specialties' => $formatedSpecialty,
                ]);

                $interval = $carbonEndTime->diffInMinutes($carbonStartTime);
                $times = []; 
                for ($i = 0; $i <= $interval; $i += 60) {
                    $time = $carbonStartTime->copy()->addMinutes($i)->format('H:i');
                    $times[] = $time;
                }
                array_pop($times);
                $workTime = implode(', ', $times);

                $days = $request->days;
                foreach($days as $day) { 
                    HairdresserAvailability::create([
                        'weekday' => $day,
                        'hours' => $workTime,
                        'hairdresser_id' => $newHairdresser['id'],
                    ]);
                }   

                return redirect()->back();
            } else {
                return redirect()->back()->withErrors([
                    'name' => 'O horário inicial deve ser antes que o final.',
                ]);
            }
        }

        return redirect()->back()->withInput($request->all());
    }

    public function edit($id)
    {
        $hairdresser = Hairdresser::find($id);
        $hdAvailability = HairdresserAvailability::where('hairdresser_id', $id)->get();

        $workHours = $hdAvailability[0]['hours'];
        $workHours = explode(', ', $workHours);

        $workDays = [];
        foreach($hdAvailability as $availability) {
            $workDays[] = $availability['weekday']; 
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

        $days = [
            'Domingo',
            'Segunda',
            'Terça',
            'Quarta',
            'Quinta',
            'Sexta',
            'Sábado',
        ];

        return view('admin.hairdressers.edit', [
            'hairdresser' => $hairdresser,
            'workDays' => $workDays,
            'days' => $days,
            'times' => $times,
            'workHours' => $workHours,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = $request->validate([
            'name' => 'required|min:2',
            'specialties' => 'required',
            'days' => 'required',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'avatar' => 'file|mimes:jpg,png',
        ]);
        if($validator) {
            $hairdresser = Hairdresser::find($id);

            if($request->avatar) {
                $avatar = $request->file('avatar')->store('public');
                $avatar = last(explode('/', $avatar));
            }
       
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
            $formatedSpecialty = '';
            foreach($specialties as $spKey => $spValue) {
                $specialties[$spKey] = trim($specialties[$spKey]," "); 
                $formatedSpecialty .= $specialties[$spKey].', '; 
            }
            $formatedSpecialty = substr($formatedSpecialty, 0, strlen($formatedSpecialty) - 2);
            
            $startTime = $request->start_time;
            $endTime = $request->end_time;
            $carbonStartTime = Carbon::createFromFormat('H:i', $startTime);
            $carbonEndTime = Carbon::createFromFormat('H:i', $endTime);
            if($carbonEndTime->greaterThan($carbonStartTime)) {
                if(!empty($avatar)) {
                    $hairdresser->update([
                        'name' => $formatedName,
                        'specialties' => $formatedSpecialty,
                        'avatar' => $avatar,
                    ]);
                } else {
                    $hairdresser->update([
                        'name' => $formatedName,
                        'specialties' => $formatedSpecialty,
                    ]);
                }

                $hdAvailabilities = HairdresserAvailability::where('hairdresser_id', $id)->get(); 
                foreach($hdAvailabilities as $availability) {
                    $availability->delete();
                }

                $interval = $carbonEndTime->diffInMinutes($carbonStartTime);
                $times = [];
                for ($i = 0; $i <= $interval; $i += 60) {  
                    $time = $carbonStartTime->copy()->addMinutes($i)->format('H:i');
                    $times[] = $time;
                }
                $workTime = implode(', ', $times);

                $days = $request->days;
                foreach($days as $day) { 
                    HairdresserAvailability::create([
                        'weekday' => $day,
                        'hours' => $workTime,
                        'hairdresser_id' => $id,
                    ]);
                }   

                return redirect()->back();
            } else {
                return redirect()->back()->withErrors([
                    'name' => 'O horário inicial deve ser antes que o final.',
                ]);
            }
        }

        return redirect()->back();
    }

    public function destroy($id)
    {
        $hairdresser = Hairdresser::find($id);
        if($hairdresser) { 
            $hairdresser->delete();
        }

        return redirect()->back();
    }
}
