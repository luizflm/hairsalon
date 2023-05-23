<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function edit(User $user)
    {
        $loggedUser = Auth::user();
        if($user && $loggedUser->id == $user->id) { 
            return view('auth.edit', ['user' => $user]);
        } else {
            return redirect()->route('home');
        }
    }

    public function update(EditUserRequest $request, User $user)
    {
        $loggedUser = Auth::user();
        $request['cpf'] = str_replace(['.', '-'], '', $request['cpf']); 

        if($user && $loggedUser->id == $user->id) {
            $name = $request->name;
            $email = $request->email;
            $password = $request->password;
            $cpf = $request->cpf;
            $hash = Hash::make($password); 

            $name = trim($name," ");
            $name = explode(' ', $name);
            if(count($name) > 1) {
                $formatedName = $name[0].' '.last($name);
            } else {
                $formatedName = $name[0];
            }

            if($formatedName == "admin" && $loggedUser->name != 'admin') {
                return redirect()->back()->withErrors([
                    'email' => 'Nome inválido.',
                ])->withInput($request->except(['password', 'password_confirm']));
            }

            $user->update([
                'name' => $formatedName,
                'email' => $email,
                'password' => $hash,
                'cpf' => $cpf,
            ]);

            return redirect()->route('home');
        }

        return redirect()->route('users.edit', ['id' => $loggedUser->id]);
    }

    public function destroy(User $user)
    {
        $loggedUser = Auth::user();

        if($user->id == $loggedUser->id) {
            $user->delete();
        }
        
        return redirect()->route('home');
    }
}
