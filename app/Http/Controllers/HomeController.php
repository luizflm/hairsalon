<?php

namespace App\Http\Controllers;

use App\Models\Hairdresser;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index() 
    {    
        $user = Auth::user();

        $hairdressers = Hairdresser::all();
        foreach($hairdressers as $hairdresser) { 
            $specialties = explode(', ', $hairdresser['specialties']);
            $hairdresser['specialties'] = $specialties;

            $hairdresser['avatar'] = asset('/storage/'.$hairdresser['avatar']);
        }

        return view('pages.home', [
            'user' => $user,
            'hairdressers' => $hairdressers,
        ]);
    }
}
