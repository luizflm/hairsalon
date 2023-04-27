<?php

namespace App\Http\Controllers;

use App\Models\Hairdresser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index() {    
        // pegando usuário logado
        $user = Auth::user();

        // pegando os todos os hairdressers
        $hairdressers = Hairdresser::all();
        foreach($hairdressers as $hairdresser) { 
            // transformando a string de especialidades em um array
            $specialties = explode(', ', $hairdresser['specialties']);
            // substituindo a string pelo array
            $hairdresser['specialties'] = $specialties;

            // trocando o nome da imagem pelo link até a storage (onde está a imagem)
            $hairdresser['avatar'] = asset('/storage/'.$hairdresser['avatar']);
        }

        return view('home', ['user' => $user, 'hairdressers' => $hairdressers,]);
    }
}
