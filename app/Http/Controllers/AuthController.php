<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function registerAction(RegisterRequest $request) 
    {
        $name = $request->name;
        $cpf = $request->cpf;
        $cpf = str_replace(['.', '-'], '', $cpf); 
        $email = $request->email;
        $password = $request->password;
        $hash = Hash::make($password);

        $cpfExists = User::where('cpf', $cpf)->first();
        if($cpfExists) { 
            return redirect()->back()->withErrors([
                'email' => 'CPF já está em uso.'
            ])->withInput($request->except(['password', 'password_confirm']));
        }

        $name = trim($name," ");
        $name = explode(' ', $name);
        if(count($name) > 1) {
            $formatedName = $name[0].' '.last($name);
        } else {
            $formatedName = $name[0];
        }

        if($formatedName === "admin") {
            $admin = User::where('name', 'admin')->first();
            if($admin) { 
                return redirect()->back()->withErrors([
                    'email' => 'Nome inválido, tente colocar um diferente.',
                ])->withInput($request->except(['password', 'password_confirm']));
            } else {
                $newUser = User::create([
                    'name' => $formatedName,
                    'cpf' => $cpf,
                    'email' => $email,
                    'password' => $hash,
                    'is_admin' => '1',
                ]);
            }
        } else {
            $newUser = User::create([
                'name' => $formatedName,
                'cpf' => $cpf,
                'email' => $email,
                'password' => $hash,
            ]);
        }

        Auth::login($newUser);

        return redirect()->route('home');   
    }

    public function loginAction(LoginRequest $request) 
    {
        $credentials = $request->validated();

        if(Auth::attempt($credentials)) { 
            $request->session()->regenerate();

            return redirect()->route('home');
        } else {
            return redirect()->back()->withErrors([
                'email' => 'O email e/ou senha estão incorretos.',
            ])->onlyInput('email');
        }
    }

    public function logout(Request $request) 
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
