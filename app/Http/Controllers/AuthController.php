<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function insertView() {
        return view('register');
    }

    public function insertAction(Request $request) {
        $validator = $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|min:2',
            'cpf' => 'required|numeric|regex:/^[0-9]+$/|digits:11|unique:users,cpf',
            'password' => 'required|min:4',
            'password_confirm' => 'required|same:password'
        ]);
        if($validator) {
            $name = $request->name;
            $cpf = $request->cpf;
            $email = $request->email;
            $password = $request->password;
            $hash = Hash::make($password);

            $name = trim($name," ");
            $name = explode(' ', $name);
            if(count($name) > 1) {
                $formatedName = $name[0].' '.last($name);
            } else {
                $formatedName = $name[0];
            }

            $newUser = User::create([
                'name' => $formatedName,
                'cpf' => $cpf,
                'email' => $email,
                'password' => $hash,
            ]);

            Auth::login($newUser);

            return redirect()->route('home');
        } else {
            return back()->onlyInput(['email', 'name', 'cpf']);
        }
    }

    public function loginView() {
        return view('login');
    }

    public function loginAction(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if(Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('home');
        }

        return back()->withErrors([
            'email' => 'O email e/ou senha estão incorretos.',
        ])->onlyInput('email');
    }

    public function logout(Request $request) {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
