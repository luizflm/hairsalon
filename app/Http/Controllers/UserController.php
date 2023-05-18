<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function edit($id)
    {
        $loggedUser = Auth::user();
        $user = User::find($id);
        if($user && $loggedUser->id == $user->id) { 
            return view('auth.edit', ['user' => $user]);
        } else {
            return redirect()->route('home');
        }
    }

    public function update(Request $request, $id)
    {
        $loggedUser = Auth::user();
        $user = User::find($id);
        $request['cpf'] = str_replace(['.', '-'], '', $request['cpf']); 

        $validator = $request->validate([
            'email' => ['required', 'email', Rule::unique('users')->ignore($loggedUser->id)],
            'name' => ['required', 'min:2'],
            'cpf' => ['required', 'digits:11', Rule::unique('users')->ignore($loggedUser->id)],
            'password' => ['required', 'min:4'],
            'password_confirm' => ['required', 'same:password'],
        ]);
        if($validator) {
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
        }

        return redirect()->route('users.edit', ['id' => $loggedUser->id]);
    }

    public function destroy($id)
    {
        $loggedUser = Auth::user();
        $user = User::find($id);

        if($user['id'] == $loggedUser->id) {
            $user->delete();
        }
        
        return redirect()->route('home');
    }
}
