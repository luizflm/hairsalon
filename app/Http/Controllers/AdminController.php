<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index() 
    {
        $user = Auth::user();

        return view('admin.pages.home', ['user' => $user]);
    }
}
