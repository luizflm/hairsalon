<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index() {
        $user = Auth::user(); // pegando o usuário logado

        return view('admin_home', ['user' => $user]);
    }
}
